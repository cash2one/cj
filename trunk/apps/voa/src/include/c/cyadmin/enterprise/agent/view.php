<?php

/**
 * 代理加盟详情
 * Created by PhpStorm.
 * User: ChangYi
 * Date: 2015/6/29
 * Time: 18:47
 */
class voa_c_cyadmin_enterprise_agent_view extends voa_c_cyadmin_base {
	public function execute() {
		try {
			$aid             = $this->request->get( 'aid' );
			$serv            = &service::factory( 'voa_s_cyadmin_agent_index' );
			$view            = $serv->get( $aid );
			$view['created'] = rgmdate( $view['created'], 'Y-m-d H;i' );
		} catch( Exception $e ) {

		}
		$this->view->set( 'view', $view );
		$this->output( 'cyadmin/agent/view' );
	}
}
