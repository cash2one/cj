<?php
/**
 * voa_d_oa_travel_styles
 * 产品规格
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_travel_styles extends voa_d_abstruct {
	// 使用中
	const STATE_USING = 1;
	// 未使用
	const STATE_UNUSE = 2;

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.travel_styles';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'styleid';

		parent::__construct(null);
	}

	/**
	 * 根据 styleid 删除数据
	 * @param array $styleid styleid 数组
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete_by_styleid($styleid) {

		try {
			// 删除
			$this->_condi('styleid IN (?)', (array)$styleid);
			return $this->_delete();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}

