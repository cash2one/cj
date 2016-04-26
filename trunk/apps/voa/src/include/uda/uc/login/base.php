<?php
/**
 * base.php
 * 统一数据访问/微信登陆关联登录/基类
 * Created by zhoutao.
 * Created Time: 2015/6/26  18:15
 */

class voa_uda_uc_login_base extends voa_uda_frontend_base {

	protected $_enterprise_adminer = null;
	protected $_enterprise = null;

	public function __construct() {
		parent::__construct();
		if ($this->_enterprise_adminer === null) {
			$this->_enterprise_adminer = &service::factory('voa_s_uc_enterpriseadminer');
			// 用了快速登录那里的表uc_enterprise的s层调用，没区别
			$this->_enterprise = &service::factory('voa_s_uc_fastenterprise');
		}
	}
}
