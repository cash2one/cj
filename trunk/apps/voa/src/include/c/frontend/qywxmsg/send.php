<?php
/**
 * 企业微信消息发送
 * $Author$
 * $Id$
 */

class voa_c_frontend_qywxmsg_send extends voa_c_frontend_qywxmsg_base {

	public function execute() {
		$mq_ids = (string)$this->session->get('mq_ids');
		$ids = explode(',', $mq_ids);

		/** 读取待发送消息队列 */
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

		$this->_json_message(array());
	}

}
