<?php
/**
 * paysetting_special.php
 *
 * Created by zhoutao.
 * Created Time: 2015/8/19  14:07
 */

class voa_d_cyadmin_company_paysetting_special extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.company_paysetting_special';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'sp_id';

		parent::__construct(null);
	}

}
