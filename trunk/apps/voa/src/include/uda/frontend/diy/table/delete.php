<?php
/**
 * voa_uda_frontend_diy_table_delete
 * 统一数据访问/自定义数据表格/删除表格
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_table_delete extends voa_uda_frontend_diy_table_abstract {

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
		// 删除
		$this->_serv_table->delete($tid);

		// 更新缓存
		voa_h_cache::get_instance()->get('diytable', 'oa', true);

		return true;
	}

}
