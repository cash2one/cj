<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/22
 * Time: 下午3:42
 */

class voa_d_cyadmin_common_plugin_group extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.common_plugin_group';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'cpg_id';
		/** 字段前缀 */
		$this->_prefield = 'cpg_';

		parent::__construct(null);
	}

}
