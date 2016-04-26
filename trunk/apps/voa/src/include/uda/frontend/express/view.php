<?php
/**
 * voa_uda_frontend_express_view
 * 统一数据访问/快递助手/查看快递详情
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_view extends voa_uda_frontend_express_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$thread 输出参数
	 * @return boolean
	 */
	public function execute($in, &$express) {

		$this->_params = $in;
		// 查询表格的条件
		$fields = array(
			array('eid', self::VAR_INT, null, null, false)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 如果快递信息不存在
		if (!$express = $this->_serv->get($data['eid'])) {
			voa_h_func::throw_errmsg(voa_errcode_oa_thread::THREAD_IS_NOT_EXISTS);
			return false;
		}

		// 格式化
		$this->_fmt && $this->_format($express);

		return true;
	}

}
