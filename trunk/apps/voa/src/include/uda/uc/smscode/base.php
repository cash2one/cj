<?php
/**
 * voa_uda_uc_smscode_base
 * 统一数据访问/smscode 短信发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_smscode_base extends voa_uda_frontend_base {

	public $serv_smscode = null;

	public function __construct() {
		parent::__construct();
		if ($this->serv_smscode === null) {
			$serv_smscode = &service::factory('voa_s_uc_smscode');
		}
	}

}
