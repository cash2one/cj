<?php

/**
 * view.php
 * 帐号详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_account_add extends voa_c_cyadmin_enterprise_base {

// 已弃用，选用api
	public function execute() {
		$post = $this->request->postx();
		if( ! empty( $post ) ) {
			try {

				$data = null;
				$uda  = &uda::factory( 'voa_uda_cyadmin_enterprise_add' );

				if( ! $uda->add( $post, $data, $this->session ) ) {
					if( $uda->errmsg ) {
						$this->message( 'error', $uda->errmsg );
					} else {
						$this->message( 'error', '添加失败' );
					}
				}
				$this->message( 'success', '添加成功', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
			} catch( help_exception $h ) {
				$this->message( 'error', '添加失败' );
			} catch( Exception $e ) {
				logger::error( $e );
				$this->message( 'error', '添加失败' );
			}
		}
		$this->view->set( 'list_url_base', $this->cpurl( $this->_module, $this->_operation, 'list' ) );
		$this->view->set( 'form_url', $this->cpurl( $this->_module, $this->_operation, 'add' ) );

		$this->output( 'cyadmin/enterprise/account/add' );

		return true;
	}

}
	

