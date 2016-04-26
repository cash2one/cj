<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/24
 * Time: 下午5:59
 */

class voa_d_cyadmin_stat_plugin_add extends  voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.stat_plugin_add';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'said';
		parent::__construct();
	}

}
