<?php
/**
 * voa_c_api_project_post_close
 * 关闭任务接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_post_close extends voa_c_api_project_base {

	public function execute() {

		// 请求的参数
		$fields = array(
			'id' => array('type' => 'int', 'required' => true)
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		// 读取任务信息
		$serv = &service::factory('voa_s_oa_project', array('pluginid' => $this->_pluginid));
		$project = $serv->fetch_by_id($this->_params['id']);
		if (empty($project)) {
			return $this->_set_errcode(voa_errcode_api_project::CLOSED_NOT_EXISTS);
		}

		if ($project['m_uid'] != $this->_member['m_uid']) {
			return $this->_set_errcode(voa_errcode_api_project::CLOSED_NO, $project['m_uid'], $this->_member['m_uid']);
		}

		if ($project['p_status'] == voa_d_oa_project::STATUS_CLOSED) {
			return $this->_set_errcode(voa_errcode_api_project::CLOSED_OVER);
		}

		// 执行更新，关闭任务
		$serv->update(array(
			'p_status' => voa_d_oa_project::STATUS_CLOSED
		), array('p_id' => $this->_params['id']));

		// 把微信消息推入队列
		if (!empty($this->_setting['ep_wxqy'])) {
			$this->_to_queue($project);
		}

		return true;
	}

	/**
	 * 把微信消息推入队列
	 * @param array $project 任务信息
	 */
	protected function _to_queue($project) {
		/** 过滤任务信息 */
		$fmt = uda::factory('voa_uda_frontend_project_format');
		$fmt->project($project);

		/** 取项目成员 */
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => $this->_pluginid));
		$mem_list = $serv_pm->fetch_by_p_id($project['p_id']);

		/** 取所有的 uid */
		$uids = array();
		foreach ($mem_list as $m) {
			$uids[] = $m['m_uid'];
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($uids);

		/** 取openid */
		$openids = array();
		foreach ($users as $u) {
			$openids[] = $u['m_openid'];
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $project['p_id']);
		$content = "任务已关闭\n"
				. "任务名：".$project['_subject']."\n"
					 . " <a href='".$viewurl."'>点击查看详情</a>";

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
