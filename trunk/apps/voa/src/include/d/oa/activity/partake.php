<?php
/**
 * voa_d_oa_activity
 * 活动报名
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_activity_partake extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.activity_partake';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'apid';

		parent::__construct(null);
	}

	/**
	 * 计算指定活动id的 内部人员参与数
	 * @param array $acids
	 * @return array
	 */
	public function list_count_by_conds($acids) {

		if (is_array($acids)) {
			$acids = implode(',', $acids);
		}

		return $this->_getAll("SELECT `acid`, COUNT(`apid`) AS `_count`	FROM {$this->_table}
 				WHERE `acid` IN ({$acids}) AND `type` = 1 AND `status`<".parent::STATUS_DELETE." GROUP BY `acid`");

	}

}

