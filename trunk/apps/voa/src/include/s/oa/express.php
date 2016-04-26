<?php
/**
 * 快递基本信息
 * $Author$
 * $Id$
 */
class voa_s_oa_express extends voa_s_abstract {
	
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * 格式化信息
	 * 
	 * @param array $express
	 *        	帖子信息
	 * @return boolean
	 */
	public function format(&$express) {
		if ($express ['get_time'] > 0) {
			$express ['_get_time'] = rgmdate($express['get_time'], 'u');
		}
		$express['_created'] = rgmdate($express['created'], 'u');
		return true;
	}
}
