<?php
/**
 * voa_uda_frontend_inspect_attachment_abstract
 * 统一数据访问/巡店附件/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_inspect_attachment_abstract extends voa_uda_frontend_inspect_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_inspect_attachment();
		parent::__construct();
	}
}
