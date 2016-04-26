<?php
/**
 * voa_d_oa_redpack_total
 * 红包数据统计表
 * $Author$
 * $Id$
 */

class voa_d_oa_redpack_total extends voa_d_abstruct {

	// 初始化
	public function __construct() {

		// 表名
		$this->_table = 'orm_oa.redpack_total';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'id';
		// 字段前缀
		$this->_prefield = '';

		parent::__construct();
	}

	public function list_by_uid_year($uid, $years = array(), $page_option = null, $orderby = array()) {

		try {
			$this->_condi('m_uid=?', $uid);
			$this->_condi('year IN (?)', $years);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			!empty($page_option) && $this->_limit($page_option);

			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			return $this->_find_all();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据用户uid和年份读取统计数据
	 * @param int $uid 用户uid
	 * @param number $year 年份
	 */
	public function get_by_uid_year($uid, $year = 0) {

		try {
			// 条件
			$this->_condi('m_uid=?', $uid);
			$this->_condi('year=?', $year);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_find_row();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}
}
