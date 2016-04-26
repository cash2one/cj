<?php
/**
 * dynamic.php
 * 微社群用户动态表
 * Create By YMX
 * $Author$
 * $Id$
 */

class voa_d_oa_common_dynamic extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.common_dynamic';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}

	public function list_real_by_conds($conds, $page_option = null, $orderby = array()) {
		try {
			// 条件
			$this->_parse_conds($conds);

			!empty($page_option) && $this->_limit($page_option);
			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}


			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件计算数量
	 * @param array $conds
	 * @throws service_exception
	 * @return number
	 */
	public function count_real_by_conds($conds) {
		try {
			// 条件
			$this->_parse_conds($conds);
			return (int)$this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}