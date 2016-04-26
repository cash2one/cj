<?php
/**
 * voa_uda_uc_fastlogin_base
 * 统一数据访问/微信登陆关联登录/基类
 * Created by zhoutao.
 * Created Time: 2015/6/18  10:42
 */

class voa_uda_uc_fastlogin_base extends voa_uda_frontend_base {

	protected $_fastinformation = null;
	protected $_fastlogin = null;
	protected $_fastenterprise = null;

	public function __construct() {
		parent::__construct();
		if ($this->_fastinformation === null) {
			$this->_fastinformation = &service::factory('voa_s_uc_fastinformation');
			$this->_fastlogin = &service::factory('voa_s_uc_fastlogin');
			$this->_fastenterprise = &service::factory('voa_s_uc_fastenterprise');
		}
	}
}
