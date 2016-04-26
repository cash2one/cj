<?php
/**
 * fastenterprise.php
 * 公司信息ep_id
 * Created by zhoutao.
 * Created Time: 2015/6/19  11:39
 */

class voa_d_uc_fastenterprise extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_uc.enterprise';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'ep_id';
		// 字段前缀
		$this->_prefield = 'ep_';

		parent::__construct();
	}

}
