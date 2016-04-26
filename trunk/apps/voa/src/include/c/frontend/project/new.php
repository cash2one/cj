<?php
/**
 * 任务列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_new extends voa_c_frontend_project_base {

	public function execute() {
		if ($this->_is_post()) {
			$subject = trim($this->request->get('subject'));
			$message = trim($this->request->get('message'));
			if (empty($subject)) {
				$this->_error_message('任务名称不能为空');
			}

			$begintime = trim($this->request->get('begintime'));
			$endtime = trim($this->request->get('endtime'));
			if (empty($begintime) || empty($endtime)) {
				$this->_error_message('任务开始/结束时间不能为空');
			}

			$stime = rstrtotime($begintime);
			$etime = rstrtotime($endtime);
			if ($stime >= $etime) {
				$this->_error_message('结束时间必须大于开始时间');
			}

			if (rgmdate($stime, 'Ymd') < rgmdate(startup_env::get('timestamp'), 'Ymd')
					|| $etime < startup_env::get('timestamp')) {
				$this->_error_message('任务开始/结束时间都必须大于当前时间'.rgmdate($stime, 'Y-m-d H:i:s'));
			}

			// 上传的附件id
			$upload_attach_ids = (string)$this->request->post('at_ids');
			$upload_attach_ids = trim($upload_attach_ids);
			// 检查附件id
			$attach_ids = array();
			// 判断是否上传了附件 且 系统是否允许上传图片
			if (!empty($upload_attach_ids) && !empty($this->_p_sets['upload_image'])) {

				// 整理附件id
				foreach (explode(',', $upload_attach_ids) as $_id) {
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
					$this->_error_message('至少要求上传 '.$this->_p_sets['upload_image_min_count'].' 张图片，您上传了 '.$count.' 张');
				}
				// 不能超出系统要求的上传数
				if ($count > $this->_p_sets['upload_image_max_count']) {
					$this->_error_message('最多只允许上传 '.$this->_p_sets['upload_image_max_count'].' 张图片，您已上传了 '.$count.' 张');
				}
			}

			// 获取附件信息
			$attachs = array();
			if (!empty($attach_ids)) {
				$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
				$attachs = array();
				$attachs = $serv_at->fetch_by_conditions(array(
					'at_id' => array($attach_ids, '='),
					'm_uid' => startup_env::get('wbs_uid')
				));
			}

			$join = trim($this->request->get('join'));
			$project_uids = trim($this->request->get('project_uids'));
			$cc_uids = trim($this->request->get('cc_uids'));
			if (empty($project_uids)) {
				$this->_error_message('参加任务人员不能为空');
			}

			/** 读取参会人员/抄送人员信息 */
			$p_uids = explode(',', $project_uids);
			$p_uids[] = startup_env::get('wbs_uid');
			$c_uids = explode(',', $cc_uids);
			$alluids = array_merge($p_uids, $c_uids);
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
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

			$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
			$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
			$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => startup_env::get('pluginid')));
			/** 数据入库 */
			try {
				$serv_m->begin();

				/** 申请信息入库 */
				$proj = array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'p_subject' => $subject,
					'p_message' => $message,
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

				/** 任务人员信息入库 */
				foreach ($puidlist as $v) {
					if ($v['m_uid'] == startup_env::get('wbs_uid') && !$join) {
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

				/** 抄送人信息入库 */
				foreach ($cculist as $v) {
					/** 如果是自己 */
					if ($v['m_uid'] == startup_env::get('wbs_uid')) {
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
						'm_uid' => startup_env::get('wbs_uid'),
						'm_username' => startup_env::get('wbs_username'),
						'pat_status' => voa_d_oa_project_attachment::STATUS_NORMAL
					));
				}

				$serv_m->commit();
			} catch (Exception $e) {
				$serv_m->rollback();
				/** 如果 $id 值为空, 则说明入库操作失败 */
				$this->_error_message('任务新增失败');
			}

			/** 给任务人发送微信消息 */
			$this->_to_queue($proj, $puidlist, $cculist);

			$this->_success_message('任务发布成功', "/project/view/{$p_id}");
		}

		/** 取草稿信息 */
		$data = array();
		$this->_get_draft($data);

		/** 起始年月日 */
		$range_start = rgmdate(startup_env::get('timestamp'), 'Y-m-d\TH:i:s\Z', 0);
		$range_end = rgmdate(startup_env::get('timestamp') + 86400, 'Y-m-d\TH:i:s\Z', 0);

		$this->view->set('cculist', $data['ccusers']);
		$this->view->set('accepters', $data['accepters']);
		$this->view->set('form_action', "/project/new?handlekey=post");
		$this->view->set('range_start', $range_start);
		$this->view->set('start_selected', $range_start);
		$this->view->set('end_selected', $range_end);
		$this->view->set('ac', $this->action_name);
		$this->view->set('refer', get_referer());
		$this->view->set('project', array('p_message' => $data['message']));

		// 赋值jsapi接口需要的ticket
		$this->_get_jsapi("['chooseImage', 'previewImage', 'uploadImage']");

		$this->_output('project/post');
	}

	/**
	 * 把任务消息推入队列
	 * @param array $project 任务详情
	 * @param array $join_list 参与人详情列表
	 * @param array $ccu_list 抄送人详情列表
	 */
	protected function _to_queue($project, $join_list, $ccu_list) {
		/** 更新草稿信息 */
		$this->_update_draft(array_keys($join_list), array_keys($ccu_list));

		/** 过滤任务信息 */
		$fmt = uda::factory('voa_uda_frontend_project_format');
		$fmt->project($project);

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $project['p_id']);
		$content = "收到新任务\n"
				 . "任务名：".$project['p_subject']."\n"
				 . "来自：".$project['m_username']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
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

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
	}

	/**
	 * 获取用户信息中的 openid
	 * @param array $openids openid 数组
	 * @param array $users 用户信息数组
	 */
	private function __get_openids(&$openids, $users) {
		foreach ($users as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$openids[$u['m_uid']] = $u['m_openid'];
		}

		return true;
	}
}

