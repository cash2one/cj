<?php
/**
 * voa_d_cyadmin_company_trial
 *
 * Created by zhoutao.
 * Created Time: 2015/8/17  17:53
 */

class voa_d_cyadmin_company_trial extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.company_trial';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tr_id';

		parent::__construct(null);
	}

}
