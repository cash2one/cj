<?php
/**
 * 代理加盟
 * Created by PhpStorm.
 * User: ChangYi(xubinshan)
 * Date: 2015/6/29
 * Time: 10:16
 */

class voa_d_cyadmin_agent_index extends  voa_d_abstruct
{
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.agent';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'aid';
		parent::__construct();
	}

}
