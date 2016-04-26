<?php
/**
 * voa_uda_frontend_common_region_abstract
 * 统一数据访问/地区信息/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_common_region_abstract extends voa_uda_frontend_common_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_common_region();
		parent::__construct();
	}
}
