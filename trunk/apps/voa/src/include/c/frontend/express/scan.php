<?php
/**
 * 扫描二维码
 * $Author$
 * $Id$
 */
class voa_c_frontend_express_scan extends voa_c_frontend_express_base {
	public function execute() {
		$act = $this->request->get ( 'act' );
		// 确认收件
		if ($act) {
			$this->$act ();
			exit ();
		}
		
		// 读快递助手配置缓存
		$p_sets = voa_h_cache::get_instance ()->get ( 'plugin.express.setting', 'oa' );
		$uid = startup_env::get ( 'wbs_uid' ); // 操作人用户id
	    // 读取用户信息
		$serv_m = &service::factory ( 'voa_s_oa_member' );
		$users = $serv_m->fetch_by_uid ( $uid );
		
		$is_scan = false;
		// 判断用户是否有权限扫码(用户id)
		if (! empty ( $p_sets ['m_uids'] )) {
			if (strpos ( $p_sets ['m_uids'], $users ['m_uid'] ) !== false) {
				$is_scan = true;
			}
		}
		
		//根据部门id判断是否有权限
		if (! empty ( $p_sets ['cd_ids'] )) {
			if (strpos ( $p_sets ['cd_ids'], $users ['cd_id'] ) !== false) {
				$is_scan = true;
			}
		}
		
		if ($is_scan) { // 有权限扫码判断快递是否领取
			$eid = $this->request->get ( 'eid' );
			// 读取快递详情
			$uda_express = &uda::factory ( 'voa_uda_frontend_express_view' );
			$express = array ();
			if (! $uda_express->execute ( array (
					'eid' => $eid 
			), $express )) {
				$this->_error_message ( $uda_express->errmsg );
				return true;
			}
			
			//快递状态，跳转不同页面
			if ($express ['flag'] == voa_d_oa_express::GET_YES) {
				$this->view->set ( 'error', "您的快递已领取！" );
				$this->_output ( 'mobile/express/scanerror' );
			} else {
				$e_users = $serv_m->fetch_by_uid ( $express ['uid'] );
				$express ['phone'] = $e_users ['m_mobilephone'];
				$this->view->set ( 'express', $express );
				$this->_output ( 'mobile/express/scanview' );
			}
		} else {
			$this->view->set ( 'error', '没有权限哦！' );
			$this->_output ( 'mobile/express/scanerror' );
		}
	}
	
	/**
	 * 扫描二维码确认收件操作
	 */
	private function scan_ok() {
		$eid = intval ( $this->request->get ( 'eid' ) );
		$uda_sign = &uda::factory ( 'voa_uda_frontend_express_sign' );
		$express = array ();
		$uda_sign->execute ( array (
				'eid' => $eid 
		), $express );
		
		// 查询收件人信息
		$express_mem = array ();
		$uda_mem = &uda::factory ( 'voa_uda_frontend_express_mem_list' );
		$uda_mem->execute ( array (
				'eid' => $eid 
		), $express_mem );
		
		$is_send = false;
		$express_uid = array();
		// 如果是代领快递，发消息给收件人
		foreach ( $express_mem as $k => $v ) {
			if ($v ['flag'] == voa_d_oa_express_mem::COLLECTION) { // 设置可发送消息
				
				$is_send = true;
				continue;
			} elseif ($v ['flag'] == voa_d_oa_express_mem::GET) { // 收件人uid
				$express_uid ['uid'] = intval($v ['uid']);
				continue;
			}
		}
		$express_uid['eid'] = $eid;
		if ($is_send) {
			$uda_sign->send_msg ( $express_uid, 'lead_ok', $this->session );
		}
	}
}
