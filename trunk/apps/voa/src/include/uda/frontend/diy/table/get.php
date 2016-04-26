<?php
/**
 * voa_uda_frontend_diy_table_get
 * 统一数据访问/自定义数据表格/获取单个表格
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_table_get extends voa_uda_frontend_diy_table_abstract {

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
		// 获取表格 tid
		$tid = (int)$this->get('tid');
		// 读取
		$out = $this->_serv_table->get($tid);

		return true;
	}

}
