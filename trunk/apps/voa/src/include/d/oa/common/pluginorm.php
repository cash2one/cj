<?php
/**
 * pluginorm.php
 *
 * Created by zhoutao.
 * Created Time: 2015/8/3  14:50
 */

class voa_d_oa_common_pluginorm extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.common_plugin';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		// 字段前缀
		$this->_prefield = 'cp_';
		/** 主键 */
		$this->_pk = 'cp_pluginid';
		parent::__construct(null);
	}

}
