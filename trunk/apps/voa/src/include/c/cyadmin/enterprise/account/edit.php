<?php

/**
 *
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_account_edit extends voa_c_cyadmin_base {

	public function execute() {

		$acid = $this->request->get( 'acid' );

		$edit = $this->request->post( 'edit' );
		$post = $this->request->postx();
		if( ! empty( $edit ) ) {
			$uda = &uda::factory( 'voa_uda_cyadmin_enterprise_add' );
			$uda->edit( $post, $data );
			if( $data ) {
				$this->message( 'success', '修改成功', get_referer( $this->cpurl( $this->_module, $this->_operation, 'list' ) ), false );
			} else {
				$this->message( 'error', '修改失败' );
			}
		}
		$serv    = &service::factory( 'voa_s_cyadmin_enterprise_account' );
		$account = $serv->get( $acid );

		// 注入模板变量
		$this->view->set( 'data', $account );
		$this->view->set( 'form_url', $this->cpurl( $this->_module, $this->_operation, 'edit' ) );
		$this->view->set( 'list_url_base', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
		$this->view->set( 'add_url_base', $this->cpurl( $this->_module, $this->_operation, 'add' ) );
		// 输出模板
		$this->output( 'cyadmin/enterprise/account/edit' );

	}

	public function up( $ep_id ) {
		$epid = 20;
		$serv = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		var_dump( $serv );
		exit;
	}
}
