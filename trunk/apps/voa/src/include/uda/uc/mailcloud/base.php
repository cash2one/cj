<?php
/**
 * voa_uda_uc_mailcloud_base
 * 统一数据访问/邮件发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_mailcloud_base extends voa_uda_frontend_base {
	/** mailcloud 模板配置信息 */
	protected $_tpls = array();

	public function __construct() {
		parent::__construct();
		$this->_tpls = config::get(startup_env::get('app_name').'.mailcloud.tpls');
	}

}
