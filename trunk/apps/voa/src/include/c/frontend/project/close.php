<?php
/**
 * 更新任务进度
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_close extends voa_c_frontend_project_base {

	public function execute() {
		$p_id = intval($this->request->get('p_id'));
		/** 读取任务信息 */
		$serv = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$project = $serv->fetch_by_id($p_id);
		if (empty($project)) {
			$this->_error_message('该任务不存在或已删除');
		}

		if ($project['m_uid'] != startup_env::get('wbs_uid')) {
			$this->_error_message('您没有此权限');
		}

		$serv->update(array(
			'p_status' => voa_d_oa_project::STATUS_CLOSED
		), array('p_id' => $p_id));

		/** 把微信消息推入队列 */
		$this->_to_queue($project);

		$this->_success_message('任务关闭成功', '/project/view/'.$project['p_id']);
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
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => startup_env::get('pluginid')));
		$mem_list = $serv_pm->fetch_by_p_id($project['p_id']);

		/** 取所有的 uid */
		$uids = array();
		foreach ($mem_list as $m) {
			$uids[] = $m['m_uid'];
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
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

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));
	}
}

