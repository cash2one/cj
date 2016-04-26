<?php
/**
 * 微信应用访问信息
 * User: luckwang
 * Date: 2/7/15
 * Time: 20:22
 */

class voa_d_cyadmin_stat_log extends  voa_d_abstruct
{
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.stat_log';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'stat_log_id';
		parent::__construct();
	}

}
