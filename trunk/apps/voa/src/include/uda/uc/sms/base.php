<?php
/**
 * voa_uda_uc_sms_base
 * 统一数据访问/sms 短信发送操作/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_sms_base extends voa_uda_frontend_base {
	/** sms 配置信息 */
	protected $_signame = array();

	public function __construct() {
		parent::__construct();
		$this->_signame = config::get(startup_env::get('app_name').'.sms.signame');
	}

}
