<?php

/**
 * @Author: ppker
 * @Date:   2015-07-27 09:49:16
 * @Last Modified by:   ppker
 * @Last Modified time: 2015-08-06 21:57:35
 * @Description 已读消息
 */
class voa_c_admincp_system_message_old extends voa_c_admincp_system_base {

	public function execute() {

		// 获取已读记录 根据浏览者id logid来做就好了
		$uid = $this->_user['ca_id']; // 当前登录用户的uid  `ca_id`
		$ep_id = $this->_setting['ep_id'];
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}

		$re_info = $this->_onrequest_rpc($page, $uid, $ep_id);

		$this->view->set('re_info', $re_info);
		$this->output( 'system/message/old' );
	}


	/**
	 * 通过RPC获取列表进行分页
	 * @param $conditions
	 * @param $page_option
	 */
	protected function _onrequest_rpc($page, $uid, $ep_id) {

		$domain = config::get('voa.cyadmin_domain.domain_url'); // 总后台domain

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/OaRpc/Rpc/MessageList');

		$rre = $rpc->get_old_message_list($page, $uid, $ep_id);
		if (!empty($rre)) {
			// 对获取的数据进行分页 老框架
			$pagerOptions = array(
				'total_items'      => $rre['total'],
				'per_page'         => 12,
				'current_page'     => $rre['page'],
				'show_total_items' => true,
			);
			$multi = pager::make_links( $pagerOptions );
			$end_message_list = isset($rre['data_list']) ? $rre['data_list'] :array();

			return array($multi, $end_message_list);
		}
	}


}
