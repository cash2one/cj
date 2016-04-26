<?php
/**
 * voa_c_api_common_post_sendmsg
 * 异步发送微信消息（自COOKIE读取待发送队列ID）
 * 无任何参数请求
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_api_common_post_sendmsg extends voa_c_api_common_abstract {


	protected function _before_action($action) {
		$this->_require_login = false;
		if (! parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		// 无具体参数请求
		$mq_ids = (string)$this->session->get('mq_ids');
		$ids = explode(',', $mq_ids);

		// 读取待发送消息队列
		$serv = &service::factory('voa_s_oa_msg_queue', array('pluginid' => 0));
		$list = $serv->fetch_unsend_by_ids($ids);

		$serv_qy = voa_wxqy_service::instance();
		foreach ($list as $q) {
			startup_env::set('pluginid', $q['cp_pluginid']);
			switch ($q['mq_msgtype']) {
				case voa_h_qymsg::MSGTYPE_TEXT:
					$serv_qy->post_text($q['mq_message'], $q['mq_agentid'], $q['mq_touser'], $q['mq_toparty']);
					break;
				case voa_h_qymsg::MSGTYPE_NEWS:
					$serv_qy->post_news(unserialize($q['mq_message']), $q['mq_agentid'], $q['mq_touser'], $q['mq_toparty']);
					break;
			}
		}

		$serv->delete_by_ids($ids);
		$this->session->remove('mq_ids');

		$this->_result = array('ids' => $ids);
	}

}
