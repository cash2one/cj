<?php
/**
 * voa_uda_frontend_diy_option_abstract
 * 统一数据访问/商品应用/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_diy_option_abstract extends voa_uda_frontend_diy_abstract {
	// service table column option
	protected $_serv_tablecolopt = null;

	public function __construct() {

		parent::__construct();
		$this->_serv_tablecolopt = new voa_s_oa_diy_tablecolopt();
	}

	/**
	 * 从输入参数中提取数据
	 * @param array $table 数据结果
	 * @param array $isupdate 是否更新
	 * @return boolean
	 */
	protected function _parse_gp(&$tablecolopt, $isupdate = false) {

		$fields = array(
			array('tc_id', self::VAR_INT, null, null, $isupdate),
			array('attachid', self::VAR_INT, null, null, $isupdate),
			array('value', self::VAR_STR, array($this->_serv_tablecolopt, 'chk_value'), null, $isupdate)
		);
		// 提取数据
		if (!$this->extract_field($tablecolopt, $fields)) {
			return false;
		}

		return true;
	}

}
