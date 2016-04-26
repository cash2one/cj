<?php
/**
 * operationrecord.php
 *
 * Created by zhoutao.
 * Created Time: 2015/7/31  10:55
 */

class voa_d_cyadmin_company_operationrecord extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_cyadmin.company_operationrecord';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'op_id';

		parent::__construct(null);
	}

	public function count_by_complex($sql, $data, $filed) {
		
		// 真正的总数 等于 要减去的已读的条数
		$total = $this->_count_by_complex($sql, $data, $filed);
		return $total;
	}

	public function list_by_complex($sql, $data, $limit, $order) {
		
		// 真正的总数 等于 要减去的已读的条数
		return $this->_list_by_complex($sql, $data, $limit, $order);
	}



}

