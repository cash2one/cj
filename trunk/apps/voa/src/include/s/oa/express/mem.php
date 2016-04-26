<?php
/**
 * 快递信息扩展表
 * $Author$
 * $Id$
 */

class voa_s_oa_express_mem extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 格式化信息
	 * @param array $express 快递信息信息
	 * @return boolean
	 */
	public function format(&$express) {

		$express['_created'] = rgmdate($express['created'], 'u');
		$express['_updated'] = rgmdate($express['updated'], 'u');

		return true;
	}

}
