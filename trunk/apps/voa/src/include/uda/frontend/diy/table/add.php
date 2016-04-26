<?php
/**
 * voa_uda_frontend_diy_table_add
 * 统一数据访问/自定义数据表格/新增表格
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_table_add extends voa_uda_frontend_diy_table_abstract {

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
		// 提取数据
		$table = array('uid' => self::$_s_mem['m_uid']);
		if (!$this->_parse_gp($table)) {
			return false;
		}

		// 表格入库
		$out = $this->_serv_table->insert($table);

		// 缓存更新
		voa_h_cache::get_instance()->get('diytable', 'oa', true);

		return true;
	}

}
