<?php

/**
 * outsider.php
 * 活动外部参与人员
 * Created by zhoutao.
 * Created Time: 2015/5/8  9:52
 */
class voa_d_oa_activity_outsider extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.activity_outsider';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'oapid';

		parent::__construct(null);
	}

	/**
	 * 计算指定活动id的 外部人员参与数
	 * @param array $acids
	 * @return array
	 */
	public function list_count_by_conds($acids) {

		if (is_array($acids)) {
			$acids = implode(',', $acids);
		}

		return $this->_getAll("SELECT `acid`, COUNT(`oapid`) AS `_count` FROM {$this->_table}
				WHERE `acid` IN ({$acids}) GROUP BY `acid`");

	}

}
