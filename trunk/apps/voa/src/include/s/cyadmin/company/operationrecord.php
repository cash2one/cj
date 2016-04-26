<?php
/**
 * @Author: ppker
 * @Date:   2015-10-21 17:39:17
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-21 22:16:42
 */

class voa_s_cyadmin_company_operationrecord extends voa_s_abstract {

	protected $_d = null;

	public function __construct() {
		parent::__construct();
		if ($this->_d == null) {
			$this->_d = new voa_d_cyadmin_company_operationrecord();
		}
	}
	/**
	 * [list_by_complex组合查询]
	 * @param  [type] $sql   [sql语句]
	 * @param  [type] $data  [sql对应的数据]
	 * @param  [type] $limit [limit]
	 * @param  [type] $order [order]
	 * @return [type]        [返回的数据]
	 */
	public function list_by_complex($sql, $data, $limit, $order) {

		return $this->_d->list_by_complex($sql, $data, $limit, $order);
	}

	/**
	 * [count_by_complex count查询]
	 * @param  [type] $sql   [description]
	 * @param  [type] $data  [description]
	 * @param  [type] $filed [description]
	 * @return [type]        [description]
	 */
	public function count_by_complex($sql, $data, $filed) {

		$re = $this->_d->count_by_complex($sql, $data, $filed);
		return $re;
	}

}
