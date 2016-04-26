<?php
/**
 * Class voa_c_api_xdf_post_qymsg
 * 接口/新东方/发送微信消息
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_api_xdf_post_qymsg extends voa_c_api_xdf_base {

	public function execute() {

		// 签名合法性验证
		if (!$this->_validate_sig()) {
			$this->_set_errcode('102:invalid request address');
			return false;
		}

		// 验证参数
		$openid = $this->request->post('openid');
		$subject = $this->request->post('subject');
		$desc = $this->request->post('desc');
		$picurl = $this->request->post('picurl');
		$msgurl = $this->request->post('msgurl');

		// 标题和内容不能为空
		if (empty($subject) || empty($desc)) {
			$this->_set_errcode('400:标题和内容不能为空');
			return false;
		}

		// 消息的连接不能为空
		if (empty($msgurl)) {
			$this->_set_errcode('400:消息连接不能为空');
			return false;
		}

		// 读取用户信息
		$serv = &service::factory('voa_s_oa_member');
		$users = $serv->fetch_all_by_conditions(array('m_openid' => array(explode(",", $openid))));

		// 设置
		if (empty($users)) {
			$this->_set_errcode('400:用户 openid 错误');
			return false;
		}

		// 获取所有的 openid
		$openids = array();
		foreach ($users as $_u) {
			$openids[] = $_u['m_openid'];
		}

		// 配置
		$p_sets = voa_h_cache::get_instance()->get('plugin.xdf.setting', 'oa');
		startup_env::set('pluginid', $p_sets['pluginid']);
		startup_env::set('agentid', $p_sets['agentid']);

		// 记录发送记录
		voa_h_qymsg::push_news_send_queue($this->session, $subject, $desc, $msgurl, implode('|', $openids), '', $picurl);

		// 发送
		$this->_send();

		return true;
	}

	protected function _send() {

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
		return true;
	}

}
