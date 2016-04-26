<?php
/**
 * voa_uda_frontend_inspect_score_abstract
 * 统一数据访问/巡店打分项/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_inspect_score_abstract extends voa_uda_frontend_inspect_abstract {

	public function __construct() {

		$this->_serv = new voa_s_oa_inspect_score();
		parent::__construct();
	}
}
