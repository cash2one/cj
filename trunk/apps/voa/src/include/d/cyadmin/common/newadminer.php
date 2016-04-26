<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/21
 * Time: 下午2:27
 */

class voa_d_cyadmin_common_newadminer extends voa_d_abstruct {

	/** 初始化 */
	public function __construct( $cfg = null ) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.common_adminer';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'ca_id';
		/** 前缀 */
		$this->_prefield = 'ca_';

		parent::__construct( null );
	}

}
