<?php
/**
 * voa_c_api_project_post_new
 * 新建任务
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_post_new extends voa_c_api_project_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			// 任务名称
			'subject' => array('type' => 'string_trim', 'required' => true),
			// 任务说明
			'message' => array('type' => 'string_trim', 'required' => false),
			// 开始时间
			'begintime' => array('type' => 'string_trim', 'required' => true),
			// 结束时间
			'endtime' => array('type' => 'string_trim', 'required' => true),
			// 发起者是否参与任务
			'join' => array('type' => 'string_trim', 'required' => false),
			// 参加任务人员uid
			'project_uids' => array('type' => 'string_trim', 'required' => true),
			// 抄送人员uid
			'cc_uids' => array('type' => 'string_trim', 'required' => false),
			// 附件id
			'attach_ids' => array('type' => 'string_trim', 'required' => false)
		);

		// 基本验证检查
		if (!$this->_check_params($fields)) {
			return false;
		}

		if (empty($this->_params['subject'])) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_SUBJECT_NULL);
		}

		if (empty($this->_params['begintime'])) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_BEGINTIME_NULL);
		}

		if (empty($this->_params['endtime'])) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_ENDTIME_NULL);
		}

		if (is_numeric($this->_params['begintime'])) {
			$stime = (int)$this->_params['begintime'];
		} else {
			$stime = rstrtotime($this->_params['begintime']);
		}
		if (is_numeric($this->_params['endtime'])) {
			$etime = (int)$this->_params['endtime'];
		} else {
			$etime = rstrtotime($this->_params['endtime']) + 86400;
		}
		if ($stime >= $etime) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_TIME_ERROR);
		}

		if (rgmdate($stime, 'Ymd') < rgmdate(startup_env::get('timestamp'), 'Ymd')) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_BEGINTIME_SET_ERROR, rgmdate($stime, 'Y-m-d H:i:s'));
		}
		if ($etime < startup_env::get('timestamp')) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_ENDTIME_SET_ERROR, rgmdate($stime, 'Y-m-d H:i:s'));
		}

		if (empty($this->_params['project_uids'])) {
			return $this->_set_errcode(voa_errcode_api_project::NEW_PROJECT_UIDS_NULL);
		}

		// 检查附件id
		$attach_ids = array();
		// 判断是否上传了附件 且 系统是否允许上传图片
		if (!empty($this->_params['attach_ids']) && !empty($this->_p_sets['upload_image'])) {

			// 整理附件id
			foreach (explode(',', $this->_params['attach_ids']) as $_id) {
				if (!is_numeric($_id)) {
					continue;
				}
				$_id = (int)$_id;
				if ($_id > 0 && !isset($attach_ids[$_id])) {
					$attach_ids[$_id] = $_id;
				}
			}
			// 上传的图片数
			$count = count($attach_ids);
			// 设置了最少上传图片数 且上传的图片数量小于要求的数
			if (!empty($this->_p_sets['upload_image_min_count']) && $count < $this->_p_sets['upload_image_min_count']) {
				return $this->_set_errcode(voa_errcode_api_project::UPLOAD_COUNT_TOO_SHORT, $count, $this->_p_sets['upload_image_min_count']);
			}
			// 不能超出系统要求的上传数
			if ($count > $this->_p_sets['upload_image_max_count']) {
				return $this->_set_errcode(voa_errcode_api_project::UPLOAD_COUNT_TOO_MUCH, $count, $this->_p_sets['upload_image_max_count']);
			}
		}

		// 当前用户
		$uid = $this->_member['m_uid'];

		// 读取参会人员
		$p_uids = explode(',', $this->_params['project_uids']);
		$p_uids[] = $uid;
		// 抄送人员信息
		$c_uids = explode(',', $this->_params['cc_uids']);
		$alluids = array_merge($p_uids, $c_uids);
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => $this->_pluginid));
		$alllist = $serv_m->fetch_all_by_ids($alluids);
		$puidlist = array();
		$cculist = array();
		foreach ($alllist as $v) {
			if (in_array($v['m_uid'], $p_uids)) {
				$puidlist[$v['m_uid']] = $v;
			} elseif (in_array($v['m_uid'], $c_uids)) {
				$cculist[$v['m_uid']] = $v;
			}
		}

		// 获取附件信息
		$attachs = array();
		if (!empty($attach_ids)) {
			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$attachs = array();
			$attachs = $serv_at->fetch_by_conditions(array(
				'at_id' => array($attach_ids, '='),
				'm_uid' => $this->_member['m_uid']
			));
		}


		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => $this->_pluginid));
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => $this->_pluginid));
		$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => $this->_pluginid));

		// 数据入库
		try {
			$serv_m->begin();

			// 申请信息入库
			$proj = array(
				'm_uid' => $uid,
				'm_username' => $this->_member['m_username'],
				'p_subject' => $this->_params['subject'],
				'p_message' => $this->_params['message'],
				'p_begintime' => $stime,
				'p_endtime' => $etime,
				'p_updated' => startup_env::get('timestamp'),
				'p_status' => voa_d_oa_project::STATUS_NORMAL
			);
			$p_id = $serv_p->insert($proj, true);
			$proj['p_id'] = $p_id;
			if (empty($p_id)) {
				throw new Exception('新增任务失败');
			}

			// 任务人员信息入库
			foreach ($puidlist as $v) {
				if ($v['m_uid'] == $uid && !$this->_params['join']) {
					$status = voa_d_oa_project_mem::STATUS_OUTOF;
				} else {
					$status = voa_d_oa_project_mem::STATUS_NORMAL;
				}

				$serv_pm->insert(array(
					'p_id' => $p_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'pm_status' => $status
				));
			}

			// 抄送人信息入库
			foreach ($cculist as $v) {
				// 如果是自己
				if ($v['m_uid'] == $uid) {
					continue;
				}

				$serv_pm->insert(array(
					'p_id' => $p_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'pm_status' => voa_d_oa_project_mem::STATUS_CC
				));
			}

			// 附件入库
			foreach ($attachs as $v) {
				$serv_pat->insert(array(
					'p_id' => $p_id,
					'pp_id' => 0,// 标记为任务的图片
					'at_id' => $v['at_id'],
					'm_uid' => $this->_member['m_uid'],
					'm_username' => $this->_member['m_username'],
					'pat_status' => voa_d_oa_project_attachment::STATUS_NORMAL
				));
			}

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			// 如果 $id 值为空, 则说明入库操作失败
			return $this->_set_errcode(voa_errcode_api_project::NEW_FAILED_DB);
		}

		// 给任务人发送微信消息
		if (!empty($this->_setting['ep_wxqy'])) {
			$this->_to_queue($proj, $puidlist, $cculist);
		}

		$this->_result = array(
			'id' => $p_id
		);
		return true;
	}

	/**
	 * 把任务消息推入队列
	 * @param array $project 任务详情
	 * @param array $join_list 参与人详情列表
	 * @param array $ccu_list 抄送人详情列表
	 */
	protected function _to_queue($project, $join_list, $ccu_list) {
		// 更新草稿信息
		//$this->_update_draft(array_keys($join_list), array_keys($ccu_list));

		// 过滤任务信息
		$fmt = uda::factory('voa_uda_frontend_project_format');
		$fmt->project($project);

		// 发送微信消息
		$viewurl = '';
		$this->get_view_url($viewurl, $project['p_id']);
		$content = "收到新任务\n"
				. "任务名：".$project['p_subject']."\n"
					 . "来自：".$project['m_username']."\n"
					 		. " <a href='".$viewurl."'>点击查看详情</a>";

		// 整理需要接收消息的用户
		$users = array();
		$this->__get_openids($users, $join_list);
		$this->__get_openids($users, $ccu_list);

		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);
		// 写入 cookie, 刷新页面时发送
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $this->session);
	}

	/**
	 * 获取用户信息中的 openid
	 * @param array $openids openid 数组
	 * @param array $users 用户信息数组
	 */
	private function __get_openids(&$openids, $users) {
		foreach ($users as $u) {
			if ($this->_member['m_uid'] == $u['m_uid']) {
				continue;
			}

			$openids[$u['m_uid']] = $u['m_openid'];
		}

		return true;
	}

}
