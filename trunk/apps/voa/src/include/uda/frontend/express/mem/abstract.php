<?php
/**
 * voa_uda_frontend_express_mem_abstract
 * 统一数据访问/社区应用/评论基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_express_mem_abstract extends voa_uda_frontend_express_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_express_mem();
		parent::__construct();
	}

}
