<?php
/**
 * voa_d_oa_interface_log
 * 流程日志
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_interface_log extends voa_d_abstruct {

	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.interface_log';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';

		parent::__construct(null);
	}

	public function list_by_conds_join_count($conds) {
		// 条件
		$where = 'WHERE log.status<' . self::STATUS_DELETE;
		if (isset($conds['name like ?'])) {
			$where .= ' AND interface.cp_name like \'' . $conds['name like ?'] . '\'';
		}
		if (isset($conds['cp_pluginid'])) {
			$where .= ' AND interface.cp_pluginid=' . $conds['cp_pluginid'];
		}

		$sql = 'SELECT count(log.id) FROM ' . $this->_table .' log LEFT JOIN oa_interface interface ON log.n_id=interface.n_id ' . $where ;

		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchColumn()) {
				return false;
			}
			return $list;
		}
	}

	public function list_by_conds_join($conds, $option, $order = array()) {

		// 条件
		$where = ' WHERE log.status<' . self::STATUS_DELETE;
		if (isset($conds['name like ?'])) {
			$where .= ' AND interface.cp_name like \'' . $conds['name like ?'] . '\'';
		}
		if (isset($conds['cp_pluginid'])) {
			$where .= ' AND interface.cp_pluginid=' . $conds['cp_pluginid'];
		}
		//排序
		$order_by = '';
		if ($order) {
			$order_by = ' ORDER BY ';
			foreach($order as $_f => $_dir) {
				$order_by .=  $_f . ' ' . $_dir;
			}
		}
		//分页
		$limit = '';
		if (!empty($option)) {
			$limit = ' LIMIT ' . implode(',', $option);
		}

		$sql = 'SELECT log.id, log.code, log.msg, log.params, log.created, interface.cp_name, interface.name, interface.method, interface.url FROM ' . $this->_table .' log LEFT JOIN oa_interface interface ON log.n_id=interface.n_id ' . $where . $order_by . $limit;

		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				return false;
			}
			return $list;
		}
	}

	public function get_by_conds_join($conds) {

		// 条件
		$where = ' WHERE log.status<' . self::STATUS_DELETE;
		if (isset($conds['n_id'])) {
			$where .= ' AND log.id =' . $conds['n_id'];
		}

		$sql = 'SELECT log.id, log.code, log.msg, log.params, log.created, interface.cp_name, interface.name, interface.method, interface.url, step.s_name, flow.f_name, flow.f_desc FROM ' . $this->_table .' log LEFT JOIN oa_interface interface ON log.n_id=interface.n_id LEFT JOIN oa_interface_step step ON log.s_id=step.s_id LEFT JOIN oa_interface_flow flow ON log.f_id=flow.f_id' . $where ;

		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$result = $sth->fetch(PDO::FETCH_ASSOC)) {
				return false;
			}
			return $result;
		}
	}

}

