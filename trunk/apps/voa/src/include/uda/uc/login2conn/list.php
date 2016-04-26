<?php
/**
 * voa_uda_uc_login2conn_list
 * 统一数据访问/微信登陆关联登录/关联表列表
 * $Author$
 * $Id$
 */

class voa_uda_uc_login2conn_list extends voa_uda_uc_login2conn_base {

	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 登录关联列表
	 */
	public function fetch_by_conditions(&$list, $conditions, $start, $limit) {
		
		/** 数据操作方法 */
		$serv_dp = &service::factory('voa_s_uc_login2conn', array('pluginid' => 0));
		$list = $serv_dp->fetch_by_conditions($conditions, $start, $limit);
		
		return true;
	}
}

?>
