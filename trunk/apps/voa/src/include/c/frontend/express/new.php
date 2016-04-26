<?php

/**
 * 快递登记
 * $Author$
 * $Id$
 */
class voa_c_frontend_express_new extends voa_c_frontend_express_base {
	public function execute() {
		// 读快递助手配置缓存
		$p_sets = voa_h_cache::get_instance ()->get ( 'plugin.express.setting', 'oa' );
		// 判断快递代收是否设置
		if (! empty ( $p_sets ['m_uids'] ) || ! empty ( $p_sets ['cd_ids'] )) {
			
			$uid = startup_env::get('wbs_uid');//操作人用户id
			//读取用户信息
			$serv_m = &service::factory('voa_s_oa_member');
			$users = $serv_m->fetch_by_uid($uid);

			$is_check = false;
			//判断用户是否有权限扫码(用户id)
			if (!empty($p_sets['m_uids'])) {
				if ( strpos($p_sets['m_uids'],$users['m_uid']) !== false ) {
					$is_check = true;
				}
			}
			
			//根据部门id判断是否有权限
			if (!empty($p_sets['cd_ids'])) {
				if ( strpos($p_sets['cd_ids'],$users['cd_id']) !== false ) {
					$is_check = true;
				}
			}
			
			if ( $is_check ) {
				$this->view->set('setting', 'setting' );
			}else{
				$this->view->set ('e_title','您不是快递接收人！');
				$this->view->set('error', '没有权限登记！' );
			}
		}else{
			$this->view->set ('e_title','未设置快递接收人');
			$this->view->set ( 'error', '请前往畅移后台-“快递助手”-“设置”，设置您公司的快递接收人。' );
		}
		
		if ( $this->_is_post () ) {
			$this->_submit ();
			return false;
		}
		$this->view->set ( 'navtitle', '快递登记' );
		$this->_output ( 'mobile/express/post' );
	}
	
	/**
	 * 快递登记提交
	 * 
	 * @return boolean
	 */
	protected function _submit() {
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin ();
			
			$params = $this->request->postx ();
			// 读取用户信息
			$serv_m = &service::factory( 'voa_s_oa_member' );
			$users = $serv_m->fetch_by_uid($params ['uid']);
			$params ['username'] = $users['m_username'];
			
			$uda = &uda::factory ( 'voa_uda_frontend_express_add' );
			// 收件人信息
			$express_mem = array ();
			if (! $uda->execute ( $params, $express_mem )) {
				$this->_error_message ( $uda->errmsg );
			}
			
			if (! empty ( $express_mem )) {
				// 发送消息给收件人(代收人如果是自己不发消息)
				if ($express_mem ['uid'] != startup_env::get ( 'wbs_uid' )) {
					$uda->send_msg ( $express_mem, 'new', $this->session );
				}
			}
			// 提交事务
			voa_uda_frontend_transaction_abstract::s_commit ();
			
			$this->_success_message ( '操作成功', "/frontend/express/new" );
		} catch ( help_exception $e ) {
			// 事务回滚
			voa_uda_frontend_transaction_abstract::s_rollback ();
			$this->_error_message ( $e->getMessage () );
			return false;
		}
	}
}
