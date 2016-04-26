<?php
/**
 * voa_uda_frontend_common_plugin_abstract
 * 统一数据访问/地区信息/基类
 * $Author ppker
 * $Id$
 */

abstract class voa_uda_frontend_common_plugin_abstract extends voa_uda_frontend_common_abstract {

	public function __construct() {
		parent::__construct();
		$this->_serv = new voa_s_oa_common_plugin_display();
	}
}
