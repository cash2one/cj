<?php
/**
 * voa_uda_frontend_auth_base
 *
 * Created by zhoutao.
 * Created Time: 2015/7/3  17:45
 */

class voa_uda_frontend_auth_base extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
		$this->auth_insert = &service::factory('voa_s_oa_auth_insert');
	}

}
