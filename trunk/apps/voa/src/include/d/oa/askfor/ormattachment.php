<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/16
 * Time: 下午10:14
 */

class voa_d_oa_askfor_ormattachment extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.askfor_attachment';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 表前缀 */
		$this->_prefield = 'afat_';
		/** 主键 */
		$this->_pk = 'afat_id';

		parent::__construct(null);
	}
}

