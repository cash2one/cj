<?php
/**
 * voa_uda_frontend_diy_data_abstract
 * 统一数据访问/自定义数据表格数据/基类
 *
 * $Author$
 * $Id$
 */

abstract class voa_uda_frontend_diy_data_abstract extends voa_uda_frontend_diy_abstract {
	// service data
	protected $_serv_data = null;
	// 特殊条件
	protected $_special_conds = array();

	public function __construct() {

		parent::__construct();
		$this->_serv_data = new voa_s_oa_diy_data();
	}

	/**
	 * 设置特殊条件
	 * @param array $conds 特殊条件, 用在和用户相关的赋值
	 * @return boolean
	 */
	public function set_special_conds($conds) {

		$this->_special_conds = $conds;
		return true;
	}

	/**
	 * 根据属性获取对应的值
	 * @param array $col 属性信息
	 * @return multitype:|NULL
	 */
	private function __get_by_column($col) {

		// 如果属性id有值
		if (isset($this->_params['_'.$col['tc_id']])) {
			return $this->_params['_'.$col['tc_id']];
		}

		// 如果属性名称有值
		if (isset($this->_params[$col['field']])) {
			return $this->_params[$col['field']];
		}

		return null;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $data 数据结果
	 * @param boolean $ignore_null 忽略 null 值
	 * @return boolean
	 */
	protected function _parse_gp(&$data, $columns, $ignore_null = false) {

		// 遍历自定义字段
		foreach ($columns as $_col) {
			$_falias = '_'.$_col['tc_id'];
			// 取值
			$data[$_falias] = $this->__get_by_column($_col);
			// 如果自定义字段信息不存在
			if (null === $data[$_falias]) {
				// 如果该字段必填, 则
				if ($_col['required'] && !$ignore_null) {
					voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELD_REQUIRED, $_col['fieldname']);
					return false;
				}

				unset($data[$_falias]);
				continue;
			}

			// 取选项值
			$opts = array();
			if (isset(self::$_s_tablecolopts['p2c'])) {
				if (array_key_exists($_col['tc_id'], self::$_s_tablecolopts['p2c'])) {
					$opts = self::$_s_tablecolopts['p2c'][$_col['tc_id']];
				}
			}


			// 检查 diy 内容
			if (!$this->_serv_data->chk_diy($data[$_falias], $_col, $opts)) {
				return false;
			}
		}

		return true;
	}

}
