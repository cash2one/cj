<?php
/**
 * voa_uda_frontend_thread_post_abstract
 * 统一数据访问/快递助手/设置基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_express_setting_abstract extends voa_uda_frontend_express_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_express_setting();
		parent::__construct();
	}

}
