<?php
/**
 * voa_d_oa_banner
 * 活动报名
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_banner extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.banner';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'bid';

		parent::__construct(null);
	}

	public function get_last() {

		$sql = 'SELECT bid,b_order FROM`'.$this->_table .'` WHERE status < 3 ORDER BY `b_order` ASC limit 1';
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$result = $sth->fetch(PDO::FETCH_ASSOC)) {
				return false;
			}
			return $result;
		}
	}

	public function update_order_all() {

		$sql = 'UPDATE`'.$this->_table .'`SET `b_order` = `b_order` + 1';
		$sth = null;
		return $this->_execute($sql, array());
	}


}

