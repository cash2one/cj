<?php
/**
 * 数据库操作类
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_abstruct extends orm {
	// 允许的表字段
	protected $_allowed_fields = array();
	// 必须的表字段
	protected $_required_fields = array();
	// 字段前缀
	protected $_prefield = '';
	// 数据状态:初始化
	const STATUS_NORMAL = 1;
	// 数据状态:已更新
	const STATUS_UPDATE = 2;
	// 数据状态:已删除
	const STATUS_DELETE = 3;

	public function __construct($cfg = null) {

		// 调用父类构造方法, 连接数据库
		try {
			parent::__construct(null);
		}  catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据主键值获取单条数据
	 * @param string $val 值
	 */
	public function get($val) {

		try {
			// 设置条件
			$this->_set($this->_pk, (string)$val);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_find_row();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取数据列表
	 * @param int|array $page_option 分页参数
	 *  + int => limit $page_option
	 *  + array => limit $page_option[0], $page_option[1]
	 * @param array $orderby 排序信息
	 */
	public function list_all($page_option = null, $orderby = array()) {

		try {
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
	 * 删除字段
	 * @param mixed $vals 主键对应的值
	 * @return object
	 */
	public function delete($vals, $reset = true) {

		try {
			// 如果参数为空
			if (empty($vals)) {
				throw new Exception('$vals is empty.');
			}

			// 重置
			$reset && $this->reset();

			// 删除时间
			if (!isset($vals[$this->_prefield.'deleted'])) {
				$this->_set($this->_prefield.'deleted', startup_env::get('timestamp'));
			}

			$this->_set($this->_prefield.'status', self::STATUS_DELETE);
			// 设置为删除状态
			$this->_condi($this->_pk.' IN (?)', (array)$vals);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);

			return $this->_update();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 信息入库
	 * @param array $data 数据数组
	 * @return boolean
	 */
	public function insert($data, $reset = true) {

		try {
			// 重置
			$reset && $this->reset();

			// 默认创建时间
			if (!isset($data[$this->_prefield.'created'])) {
				$data[$this->_prefield.'created'] = startup_env::get('timestamp');
			}

			if (!isset($data[$this->_prefield.'updated'])) {
				$data[$this->_prefield.'updated'] = startup_env::get('timestamp');
			}

			// 默认状态
			if (!isset($data[$this->_prefield.'status'])) {
				$data[$this->_prefield.'status'] = self::STATUS_NORMAL;
			}

			// 执行插入操作
			$this->_insert($data);
			return $data;
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 插入多条记录
	 * @param array $data 数据数组
	 * @return Ambigous <Ambigous, boolean>
	 */
	public function insert_multi($data) {

		$fields = array();
		$values = array();
		$values_prepare = array();
		// 是否第一次
		$first = true;
		// 分析数据
		foreach ($data as $single) {
			$vps = array();

			// 默认创建时间
			if (!isset($single[$this->_prefield.'created'])) {
				$single[$this->_prefield.'created'] = startup_env::get('timestamp');
			}

			if (!isset($single[$this->_prefield.'updated'])) {
				$single[$this->_prefield.'updated'] = startup_env::get('timestamp');
			}

			// 默认状态
			if (!isset($single[$this->_prefield.'status'])) {
				$single[$this->_prefield.'status'] = self::STATUS_NORMAL;
			}

			// 遍历单条记录
			foreach ($single as $key => $val) {
				if (is_array($val)) {
					continue;
				}

				$first && $fields[] = '`'.$key.'`';
				$values[] = $val;
				$vps[] = "?";
			}

			$first = false;
			$values_prepare[] = '('.implode(',', $vps).')';
		}

		if (empty($fields) || empty($values_prepare)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_common::INSERT_DATA_IS_EMPTY);
			return false;
		}

		// 拼凑 sql
		$sql = "INSERT INTO {$this->_table} (". implode(',', $fields).") VALUES ".implode(',', $values_prepare);
		return $this->_execute($sql, $values);
	}

	/**
	 * 更新数据
	 * @param int $val 主键键值
	 * @param array $data 待更新数据
	 */
	public function update($val = null, $data = array(), $reset = true) {

		try {
			// 重置
			$reset && $this->reset();
			// 更新时间
			if (!isset($data[$this->_prefield.'updated'])) {
				$data[$this->_prefield.'updated'] = startup_env::get('timestamp');
			}

			// 更新状态值
			if (!isset($data[$this->_prefield.'status'])) {
				$data[$this->_prefield.'status'] = self::STATUS_UPDATE;
			}

			// 如果条件为真
			if (null !== $val) {
				$this->_condi($this->_pk.(is_array($val) ? ' IN (?)' : '=?'), $val);
			}

			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_update($data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计总数
	 * @param number $limit
	 * @return Ambigous
	 */
	public function count($limit = 0, $reset = true) {

		try {
			// 重置
			$reset && $this->reset();
			// limit 设置
			if (0 < $limit) {
				$this->_limit($limit);
			}

			// 设置为删除状态
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);

			return $this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据主键值读取数据
	 * @param int|string|array $vals 查询条件
	 * @param array $orderby 排序
	 */
	public function list_by_pks($vals, $orderby = array(), $reset = true) {

		try {
			// 重置
			$reset && $this->reset();

			// 设置条件
			if (!empty($vals)) {
				$this->_condi($this->_pk.' IN (?)', (array)$vals);
			}

			// 数据状态
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);

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
	 * 根据条件读取数据
	 * @param array $conds 条件数组
	 * @throws service_exception
	 */
	public function get_by_conds($conds) {

		try {
			// 条件
			$this->_parse_conds($conds);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_find_row();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件读取数据数组
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $orderby 排序
	 * @throws service_exception
	 */
	public function list_by_conds($conds, $page_option = null, $orderby = array()) {
		try {
			// 条件
			$this->_parse_conds($conds);

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
	 * 根据条件更新数据
	 * @param array $conds 条件数组
	 * @param array $data 数据数组
	 * @throws service_exception
	 */
	public function update_by_conds($conds, $data, $force = false) {

		try {
			// 条件
			$this->_parse_conds($conds);
			if (!$force) {
				$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			}

			// 更新时间
			if (!isset($data[$this->_prefield.'updated'])) {
				$data[$this->_prefield.'updated'] = startup_env::get('timestamp');
			}

			// 更新状态值
			if (!isset($data[$this->_prefield.'status'])) {
				$data[$this->_prefield.'status'] = self::STATUS_UPDATE;
			}

			return $this->_update($data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件删除数据
	 * @param array $conds 删除条件数组
	 * @throws Exception
	 * @throws service_exception
	 */
	public function delete_by_conds($conds) {

		try {
			// 条件
			$this->_parse_conds($conds);
			// 删除时间
			if (!isset($conds[$this->_prefield.'deleted'])) {
				$this->_set($this->_prefield.'deleted', startup_env::get('timestamp'));
			}

			$this->_set($this->_prefield.'status', self::STATUS_DELETE);

			// 设置为删除状态
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return $this->_update();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 根据条件计算数量
	 * @param array $conds
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conds($conds) {
		try {
			// 条件
			$this->_parse_conds($conds);
			$this->_condi($this->_prefield.'status<?', self::STATUS_DELETE);
			return (int)$this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取单条数据 —— 通过高级复杂的查询方法
	 * 【注意】查询条件必须引入 status<? 以确保输出未删除的数据<br>
	 * 【警告】禁止滥用此方法，使用此方法仅限于复杂的使用常规方式无法获取的情况。
	 * 该方法不可直接用在外部，只能用于数据层继承子类内
	 * @param string $sql 查询条件语句 —— where 之后的SQL语句，查询值使用?替代
	 * @param array $data 查询值数组，以$sql的?为顺序
	 * @param array $orderby 排序 array('field1'=>'DESC', 'field2'=>'ASC')
	 * @param string $fields 需要提取的字段，默认为：*
	 * @param boolean $reset 是否重置orm成员值，默认为:true
	 * @uses：
	 * _get_by_complex("(field1=? OR field2>?) AND field3=?", array(data1, data2, data3), 'field1')
	 * @throws service_exception
	 * @return array
	 */
	protected function _get_by_complex($sql, $data, $orderby = array(), $fields = '', $reset = true) {
		try {
			// 重置全局成员值
			$reset && $this->reset();
			// 排序
			if (!empty($orderby)) {
				foreach ($orderby as $_f => $_dir) {
					$this->_order_by($_f, $_dir);
				}
			}
			// 传入 where 查询条件
			$this->_complex_sql = $sql;
			// 传入查询的值
			$this->_bind_params = $data;
			// 传入limit
			$this->_limit(1);
			// 返回数据
			return $this->_find_row($fields);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取一组数据 —— 通过高级复杂的查询方法
	 * 【注意】查询条件必须引入 status<? 以确保输出未删除的数据<br>
	 * 【警告】禁止滥用此方法，使用此方法仅限于复杂的使用常规方式无法获取的情况。
	 * 该方法不可直接用在外部，只能用于数据层继承子类内
	 * @param string $sql 查询条件语句 —— where 之后的SQL语句，查询值使用?替代
	 * @param array $data 查询值数组，以$sql的?顺序
	 * @param array $limit limit条件：array(min, max) or null 不做限制
	 * @param array $orderby 排序 =null 不进行排序
	 * @param string $fields 需要提取数据的字段，默认：*
	 * @param boolean $reset 是否重置orm成员值，默认为：true
	 * @throws service_exception
	 * @return array
	 */
	protected function _list_by_complex($sql, $data, $limit = array(0, 1), $orderby = array(), $fields = '', $reset = true) {
		try {
			// 重置全局成员值
			$reset && $this->reset();
			// 排序
			if (!empty($orderby)) {
				foreach ($orderby as $_f => $_dir) {
					$this->_order_by($_f, $_dir);
				}
			}
			// 传入 where 查询条件
			$this->_complex_sql = $sql;
			// 传入查询的值
			$this->_bind_params = $data;
			// 传入limit
			if (!empty($limit)) {
				$this->_limit($limit);
			}
			// 返回数据
			return $this->_find_all($fields);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 统计指定条件的总数 - 通过高级复杂的查询方式
	 * 【注意】查询条件必须引入 status<? 以确保输出未删除的数据<br>
	 * 【警告】禁止滥用此方法，使用此方法仅限于复杂的使用常规方式无法获取的情况。
	 * 该方法不可直接用在外部，只能用于数据层继承子类内
	 * @param string $sql 查询条件语句 —— where 之后的SQL语句，查询值使用?替代
	 * @param array $data 查询值数组，以$sql的?顺序
	 * @param string $fields count()的字段，默认：*
	 * @param boolean $reset 是否重置orm成员值，默认为：true
	 * @throws service_exception
	 * @return number
	 */
	protected function _count_by_complex($sql, $data, $fields, $reset = true) {

		try {
			// 重置全局成员值
			$reset && $this->reset();
			// 传入 where 查询条件
			$this->_complex_sql = $sql;
			// 传入查询值
			$this->_bind_params = $data;
			// 以某个字段
//			$this->_find_sql($fields);
			// 返回数据
			return $this->_total();
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 物理删除数据 —— 以主键值查询
	 * 【注意】此方法为物理删除数据，除非业务逻辑需要否则禁止使用本方法！
	 * 【警告】禁止滥用此方法，仅限于特殊业务需要使用
	 * @param mixed $pks 主键值或数组
	 * @throws service_exception
	 */
	protected function _delete_real($pks, $reset = true) {
		try {
			// 如果参数为空
			if (empty($pks)) {
				throw new Exception('primary field value is empty.');
			}
			// 重置全局成员值
			$reset && $this->reset();
			return $this->_delete($pks);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 使用指定条件物理删除指定的数据
	 * 【注意】此方法为物理删除数据，除非业务逻辑需要否则禁止使用本方法！
	 * 【警告】禁止滥用此方法，仅限于特殊业务需要使用
	 * @param array $conds 查询条件
	 * @param string $reset 是否重置orm成员值，默认为：true
	 * @throws Exception
	 * @throws service_exception
	 * @return boolean
	 */
	protected function _delete_real_by_conds($conds, $reset = true) {
		try {
			// 重置全局成员值
			$reset && $this->reset();
			// 条件
			$this->_parse_conds($conds);
			// 返回数据
			$sql = 'DELETE '.$this->_from();
			return $this->_execute($sql, $this->_bind_params);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
	}

}
