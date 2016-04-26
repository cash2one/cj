<?php
/**
 * voa_d_oa_customer_data
 * 表格列详情信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_customer_data extends voa_d_abstruct {

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.customer_data';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'dataid';

		parent::__construct();
	}

	/**
	 * 根据 tid 获取表格列信息
	 * @param mixed $tid 表格行id
	 * @param mixed $page_option 分页参数
	 *  + int => limit $page_option
	 *  + array => limit $page_option[0], $page_option[1]
	 */
	public function list_by_tid($tid, $page_option = null, $orderby = array()) {

		try {
			// 查询条件
			$this->_condi('tid IN (?)', (array)$tid);
			// 只查询未删除的
			$this->_condi('status<?', self::STATUS_DELETE);
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
	 * 根据 classid 获取表格列信息
	 * @param mixed $classid 表格行id
	 * @param mixed $page_option 分页参数
	 *  + int => limit $page_option
	 *  + array => limit $page_option[0], $page_option[1]
	 */
	public function list_by_classid($classid, $page_option = null, $orderby = array()) {

		try {
			// 查询条件
			$this->_condi('classid IN (?)', (array)$classid);
			// 只查询未删除的
			$this->_condi('status<?', self::STATUS_DELETE);
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
	 * 删除字段
	 * @param mixed $vals 主键对应的值
	 * @return object
	 */
	public function delete($vals, $reset = true) {

		if (empty($vals['updated'])) {
			$vals['updated'] = startup_env::get('timestamp');
		}

		return parent::delete($vals, $reset);
	}

	/**
	 * 根据条件删除数据
	 * @param array $conds 删除条件数组
	 * @throws Exception
	 * @throws service_exception
	 */
	public function delete_by_conds($conds) {

		if (empty($vals['updated'])) {
			$vals['updated'] = startup_env::get('timestamp');
		}

		return parent::delete_by_conds($conds);
	}

	/**
	 * 根据条件计算数量
	 * @param array $conds
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_updated_conds($conds, $force = false) {

		try {
			// 条件
			$this->_parse_conds($conds);
			!$force && $this->_condi('status<?', self::STATUS_DELETE);
			return (int)$this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}

