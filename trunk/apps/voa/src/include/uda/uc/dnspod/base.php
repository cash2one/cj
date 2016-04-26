<?php
/**
 * voa_uda_uc_dnspod_base
 * 统一数据访问/dnspod cname 操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_dnspod_base extends voa_uda_frontend_base {
	/** dnspod 配置信息 */
	protected $_ttl = 600;
	protected $_zoneid = 0;

	public function __construct() {
		parent::__construct();
		$this->_ttl = config::get(startup_env::get('app_name').'.dnspod.ttl');
		$this->_zoneid = config::get(startup_env::get('app_name').'.dnspod.zoneid');
	}

}
