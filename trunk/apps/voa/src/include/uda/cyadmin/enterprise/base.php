<?php

/**
 * voa_uda_cyadmin_enterprise_base
 * uda/畅移后台/企业/
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_cyadmin_enterprise_base extends voa_uda_cyadmin_base {

	public $serv_enterprise_app = null;
	public $serv_enterprise_profile = null;
	public $serv_enterprise_appsetting = null;
	protected $serv_enterprise_message_log = null;

	public function __construct() {
		parent::__construct();

		if( $this->serv_enterprise_profile === null ) {
			$this->serv_enterprise_profile = &service::factory( 'voa_s_cyadmin_enterprise_profile' );
		}
		if( $this->serv_enterprise_app == null ) {
			$this->serv_enterprise_app = &service::factory( 'voa_s_cyadmin_enterprise_app' );
		}
		if( $this->serv_enterprise_appsetting == null ) {
			$this->serv_enterprise_appsetting = &service::factory( 'voa_s_cyadmin_enterprise_appsetting' );
		}

		if( $this->serv_enterprise_message_log == null ) {
			$this->serv_enterprise_message_log = &service::factory( 'voa_s_cyadmin_enterprise_message_log' );
		}
	}

}
