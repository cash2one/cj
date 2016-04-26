<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/16
 * Time: 下午10:15
 */

class voa_d_oa_askfor_ormtemplate extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.askfor_template';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 表前缀 */
		$this->_prefield = 'aft_';
		/** 主键 */
		$this->_pk = 'aft_id';

		parent::__construct(null);
	}

}
