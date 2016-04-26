<?php
/**
 * voa_uda_uc_login2conn_base
 * 统一数据访问/微信登陆关联登录/基类
 * $Author$
 * $Id$
 */

class voa_uda_uc_login2conn_base extends voa_uda_frontend_base {


	/** login_conn 表*/
	public $serv_login2conn = null;

	public function __construct() {
		parent::__construct();
		if ($this->login2conn === null) {
			$this->login2conn = &service::factory('voa_s_uc_login2conn');
		}
	}
}
