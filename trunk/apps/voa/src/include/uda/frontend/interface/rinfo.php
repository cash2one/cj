<?php
/**
 * voa_uda_frontend_interface_list
 * 统一数据访问/测试应用/流程日志
 *
 * gaosong
 * $Id$
 */

class voa_uda_frontend_interface_rinfo extends voa_uda_frontend_interface_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('n_id', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$t = new voa_d_oa_interface_log();

		// 读取表格字段
		$result = $t->get_by_conds_join($conds);
		//var_dump($result);die;
		if (empty($result)) {
			$out = array();
			return true;
		}
		$out = $this->format($result);

		return true;
	}

	public function format($request) {

		$request['_msg'] = rjson_encode(unserialize($request['msg']));//返回
		$request['_params'] = rjson_encode(unserialize($request['params']));//请求参数

		return $request;
	}

}
