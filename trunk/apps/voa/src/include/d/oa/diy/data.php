<?php
/**
 * voa_d_oa_diy_data
 * 表格列详情信息
 *
 * $Author$
 * $Id$
 */

class voa_d_oa_diy_data extends voa_d_abstruct {
	// 富文本
	const FTYPE_TXT = 2;
	// 多行文本
	const FTYPE_CH = 1;

	/** 初始化 */
	public function __construct() {

		/** 表名 */
		$this->_table = 'orm_oa.diy_data';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'dataid';

		parent::__construct();
	}

	/**
	 * 信息入库
	 * @param array $data 数据
	 * @param array $columns 列信息
	 * @return boolean
	 */
	public function insert_column_data($columns, $data) {

		// 列数据
		$coldata = array();
		// 遍历数据
		foreach ($data as $_k => $_v) {
			// 如果是以下划线开头
			if ('_' != $_k{0}) {
				continue;
			}

			// 取 tc_id
			$tc_id = substr($_k, 1);
			// 属性不存在
			if (!array_key_exists($tc_id, $columns)) {
				continue;
			}

			// 列属性
			$col = $columns[$tc_id];

			// 数据
			$coldata[] = array(
				'uid' => $data['uid'],
				'tid' => $data['tid'],
				'dr_id' => $data['dr_id'],
				'tc_id' => substr($_k, 1),
				'data_ch' => voa_s_oa_diy_data::TYPE_TEXT == $col['ct_type'] ? '' : $_v,
				'data_txt' => voa_s_oa_diy_data::TYPE_TEXT == $col['ct_type'] ? $_v : ''
			);
		}

		// 多条记录入库
		$this->insert_multi($coldata);

		return $data;
	}

	/**
	 * 根据条件读取单条数据
	 * @param array $columns 列属性数组
	 * @param array $conds 条件数组
	 * @throws service_exception
	 */
	public function get_by_column_conds($columns, $conds) {

		try {
			// 传入limit
			$this->_limit(1);

			// 生成 sql
			$sql = $this->_create_sql($columns, $conds);

			// 执行
			$sth = null;
			if ($this->_execute($sql, $this->_bind_params, $sth)) {
				return $sth->fetch(PDO::FETCH_ASSOC);
			}

			return false;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取数据列表
	 * @param array $columns 列属性数组
	 * @param array $conds 条件数组
	 * @throws service_exception
	 */
	public function list_by_column_conds($columns, $conds, $page_option = null, $orderby = array()) {

		try {
			// 分页参数
			!empty($page_option) && $this->_limit($page_option);
			// 排序
			foreach ($orderby as $_f => $_dir) {
				$this->_order_by($_f, $_dir);
			}

			// 生成 sql
			$sql = $this->_create_sql($columns, $conds);

			// 执行
			$sth = null;
			if ($this->_execute($sql, $this->_bind_params, $sth)) {
				// 读取数据
				if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
					return false;
				}

				// 转换主键键值
				$rets = array();
				foreach ($list as $_v) {
					$rets[$_v['dr_id']] = $_v;
				}

				return $rets;
			}

			return false;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件统计记录数
	 * @param array $columns 列属性数值
	 * @param array $conds 条件数组
	 * @param string $page_option 分页参数
	 * @throws service_exception
	 * @return boolean
	 */
	public function count_by_column_conds($columns, $conds, $page_option = null) {

		try {
			// 分页参数
			!empty($page_option) && $this->_limit($page_option);

			// 生成 sql
			$sql = $this->_create_sql($columns, $conds);
			$sql = "SELECT COUNT(*) FROM ({$sql}) AS `ta`";

			// 执行
			$sth = null;
			if ($this->_execute($sql, $this->_bind_params, $sth)) {
				// 读取数据
				return $sth->fetchColumn();
			}

			return 0;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件更新数据
	 * @param array $columns 列属性数组
	 * @param array $conds 条件数组
	 * @throws service_exception
	 */
	public function update_by_column_conds($columns, $conds, $data) {

		try {
			// 传入limit
			$this->_limit(1);

			// 遍历所有字段
			foreach ($columns as $_col) {
				$f_alias = '_'.$_col['tc_id'];
				// 如果字段值没有改变
				if (!array_key_exists($f_alias, $data)) {
					continue;
				}

				// 存储字段
				$field = self::FTYPE_TXT == $_col['ftype'] ? 'data_txt' : 'data_ch';
				// 更新
				$conds['tc_id'] = $_col['tc_id'];
				$this->update_by_conds($conds, array($field => $data[$f_alias]));
			}

			return true;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 创建 sql
	 * @param array $columns 列信息
	 * @param array $conds 条件
	 * @return string
	 */
	protected function _create_sql($columns, $conds) {

		// 条件
		$fcols = array('tid', 'uid', 'dr_id', 'created', 'updated', 'status');
		// 遍历列属性
		foreach ($columns as $_tc_id => $_col) {
			// 字段名称
			$field = self::FTYPE_TXT == $_col['ftype'] ? 'data_txt' : 'data_ch';
			// sql 字段
			$fcols[] = "MAX(CASE tc_id WHEN '{$_tc_id}' THEN {$field} ELSE '' END) AS _{$_tc_id}";
		}

		// 状态参数
		$conds['status<?'] = self::STATUS_DELETE;
		// 拼凑 sql 查询
		$sql = "SELECT ".implode(", ", $fcols)." FROM {$this->_table} GROUP BY dr_id ".$this->_having($conds)." ".$this->_o_l();

		return $sql;
	}

	// 拼凑 having 语句
	protected function _having($conds) {

		$this->_merge2conds($conds);
		// 初始化参数
		$this->_bind_params = array();

		$wheres = array();
		// 遍历查询条件
		foreach ($this->_conds as $condi => $val) {
			$this->_field_sign_condi($wheres, $condi, $val);
		}

		// 查询条件字串
		$condition = "";
		if (!empty($wheres)) {
			$condition = "HAVING ".implode(' AND ', $wheres);
		}

		return $condition;
	}

	// 拼凑 sql 语句的 order/limit
	protected function _o_l() {

		// limit
		$limit = '';
		if (!empty($this->_limit)) {
			$limit = 'LIMIT '.$this->_limit;
		}

		// order by
		$order_by = '';
		if (!empty($this->_orders)) {
			$order_by = 'ORDER BY '.implode(',', $this->_orders);
		}

		return "{$order_by} {$limit}";
	}

}

