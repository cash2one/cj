<?php
/**
 * 预授权信息表
 * $Author$
 * $Id$
 */

class voa_d_uc_preauth extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_uc.preauth';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'corpid';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

	public function get_by_corpid_suiteid($corpid, $suiteid) {

		try {
			// 设置条件
			$this->_set('corpid', (string)$corpid);
			$this->_set('suiteid', (string)$suiteid);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_find_row();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
