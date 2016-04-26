<?php
/**
 * voa_uda_frontend_diy_table_update
 * 统一数据访问/自定义数据表格/更新表格
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_table_update extends voa_uda_frontend_diy_table_abstract {

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

		$tid = (int)$tid;
		if (!$this->_serv_table->chk_tid($tid)) {
			return false;
		}

		// 提取数据
		$data = array('uid' => self::$_s_mem['m_uid']);
		if (!$this->_parse_gp($data)) {
			return false;
		}

		// 更新
		$this->_serv_table->update($tid, $data);

		// 缓存更新
		voa_h_cache::get_instance()->get('diytable', 'oa', true);

		return true;
	}

}
