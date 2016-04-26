<?php
/**
 * voa_uda_uc_dnspod_list
 * 统一数据访问/读取 dnspod 列表/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_uda_uc_dnspod_list extends voa_uda_uc_dnspod_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 通过 A 记录读取列表
	 * @param array $list 数组
	 * @param string $a a记录
	 */
	public function fetch_cnames_by_a(&$list, $a) {

		$serv = &service::factory('voa_s_uc_dnspod', array('pluginid' => 0));
		$data = $serv->fetch_by_a($a);
		foreach ($data as $_v) {
			$list[] = $_v['dp_cname'];
		}

		return true;
	}

}
