<?php
/**
 * voa_uda_frontend_common_shop_abstract
 * 统一数据访问/门店信息/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_common_shop_abstract extends voa_uda_frontend_common_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_common_shop();
		parent::__construct();
	}
}
