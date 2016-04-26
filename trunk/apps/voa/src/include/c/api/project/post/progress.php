<?php
/**
 * voa_c_api_project_post_progress
 * 任务进度更新接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_post_progress extends voa_c_api_project_base {

	public function execute() {

		// 请求参数
		$fields = array(
			// 任务ID
			'id' => array('type' => 'int', 'required' => true),
			// 进度描述
			'message' => array('type' => 'string_trim', 'required' => true),
			// 进度值
			'progress' => array('type' => 'int', 'required' => false),
			// 图片附件id
			'attach_ids' => array('type' => 'string_trim', 'required' => false)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 可选进度
		$procvs = explode(',', $this->_p_sets['procvs']);

		// 读取任务信息
		$serv_p = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$project = $serv_p->fetch_by_id($this->_params['id']);
		if (empty($project)) {
			return $this->_set_errcode(voa_errcode_api_project::PROGRESS_NOT_EXISTS);
		}

		// 读取当前进度信息
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$proc = $serv_pm->fetch_by_p_id_uid($this->_params['id'], $this->_member['m_uid']);

		// 判断权限
		if (voa_d_oa_project_mem::STATUS_NORMAL != $proc['pm_status'] && voa_d_oa_project_mem::STATUS_UPDATE != $proc['pm_status']) {
			return $this->_set_errcode(voa_errcode_api_project::PROGRESS_NO);
		}

		// 检查进度值
		if (!in_array($proc['pm_progress'], $procvs) && 100 > $proc['pm_progress']) {
			$proc['pm_progress'] = 0;
		}

		$message = $this->_params['message'];
		$progress = $this->_params['progress'];

		// 检查进展描述信息
		if (empty($message)) {
			return $this->_set_errcode(voa_errcode_api_project::PROGRESS_MESSAGE_NULL);
		}

		// 检查进度值
		if ($progress < 0 || $progress > 100 || !in_array($progress, $procvs)) {
			return $this->_set_errcode(voa_errcode_api_project::PROGRESS_VALUE_ERROR, $progress);
		}
		if ($progress < $proc['pm_progress']) {
			$progress = $proc['pm_progress'];
		}

		// 检查附件id
		$attach_ids = array();
		if (!empty($this->_params['attach_ids'])) {
			foreach (explode(',', $this->_params['attach_ids']) as $_id) {
				if (!is_numeric($_id)) {
					continue;
				}
				$_id = (int)$_id;
				if ($_id > 0 && !isset($attach_ids[$_id])) {
					$attach_ids[$_id] = $_id;
				}
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

		list($p_num, $u_num) = $this->_get_progress_ct();

		$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => startup_env::get('pluginid')));
		$serv_pat = &service::factory('voa_s_oa_project_attachment', array('pluginid' => $this->_pluginid));

		// 进度信息入库
		try {
			$serv_p->begin();

			// 写入进度表
			$pp_id = $serv_pp->insert(array(
				'p_id' => $this->_params['id'],
				'm_uid' => $this->_member['m_uid'],
				'm_username' => $this->_member['m_username'],
				'pp_progress' => $progress,
				'pp_message' => $message
			), true);

			// 更新用户进度
			$serv_pm->update(array(
				'pm_progress' => $progress
			), array('pm_id' => $proc['pm_id']));

			// 更新总进度
			$p_progress = ($p_num + $progress) / ($u_num + 1);
			// 需要更新的数据
			$p_update = array(
				'p_progress' => $p_progress,
				'p_updated' => startup_env::get('timestamp')
			);
			// 标记已完成  by Deepseath@20141222
			if ($p_progress == 100) {
				$p_update['p_status'] = voa_d_oa_project::STATUS_COMPLETE;
			}
			$serv_p->update($p_update, array('p_id' => $this->_params['id']));

			// 附件入库
			foreach ($attachs as $v) {
				$serv_pat->insert(array(
					'p_id' => $this->_params['id'],
					'pp_id' => $pp_id,// 对应的进度id
					'at_id' => $v['at_id'],
					'm_uid' => $this->_member['m_uid'],
					'm_username' => $this->_member['m_username'],
					'pat_status' => voa_d_oa_project_attachment::STATUS_NORMAL
				));
			}

			$serv_p->commit();
		} catch (Exception $e) {
			$serv_p->rollback();
			return $this->_set_errcode(voa_errcode_api_project::PROGRESS_ERROR_DB);
		}

		// 把微信消息推入队列
		if (!empty($this->_setting['ep_wxqy'])) {
			$this->_to_queue($project, $p_progress);
		}

		$this->_result = array();

		return true;
	}

	/** 读取任务人员 */
	protected function _get_progress_ct() {

		// 进度总值
		$pnum = 0;
		// 人数
		$unum = 0;
		// 读取任务人员/抄送人信息
		$serv = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$allmems = $serv->fetch_by_p_id($this->_params['id']);
		foreach ($allmems as $m) {

			if (voa_d_oa_project_mem::STATUS_QUIT != $m['pm_status']) {
				// 如果已退出
				$this->_p_uids[$m['m_uid']] = $m['m_uid'];
			}

			// 发起者，但不参与，则不计入总的参与任务人数内
			if (voa_d_oa_project_mem::STATUS_OUTOF == $m['pm_status']) {
				continue;
			}

			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status']
					|| voa_d_oa_project_mem::STATUS_QUIT == $m['pm_status'] || $m['m_uid'] == startup_env::get('wbs_uid')) {
				continue;
			}

			$unum ++;
			$pnum += $m['pm_progress'];
		}

		return array($pnum, $unum);
	}

	/**
	 * 把微信消息推入队列
	 * @param array $project 任务详情信息
	 * @param int $progress 总进度
	 */
	protected function _to_queue($project, $progress) {
		/** 过滤任务详情信息 */
		$fmt = uda::factory('voa_uda_frontend_project_format');
		$fmt->project($project);

		/** 查询用户openid */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($this->_p_uids);

		$openids = array();
		foreach ($users as $u) {
			$openids[] = $u['m_openid'];
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $project['p_id']);
		if (100 <= $progress) {
			$content = "任务完成\n"
					. "任务名：".$project['_subject']."\n"
						 . "完成时间：".$project['_updated']."\n"
						 		. " <a href='".$viewurl."'>点击查看详情</a>";
		} else {
			$content = $this->_member['m_username']." 进度更新\n"
					. "任务名：".$project['_subject']."\n"
						 . "更新时间：".$project['_updated']."\n"
						 		. " <a href='".$viewurl."'>点击查看详情</a>";
		}

		$data = array(
			'mq_touser' => implode('|', $openids),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		// 写入 cookie, 刷新页面时发送
		voa_h_qymsg::set_queue_session(array($data['mq_id']), $this->session);
	}

}
