<?php
/**
 * 数据库操作中间层(orm)
 * @Author
 */

class orm extends db_mo {
	// 表字段数组
	protected $_fields = array();
	// limit
	protected $_limit = 0;
	protected $_oncein = '';
	// order by
	protected $_orders = array();
	// group by
	protected $_group_by = '';
	// 查询条件
	protected $_conds = array();
	// 实例
	protected static $instance = array();
	//
	protected $_a_clean	  = array();
	// 表前缀
	protected $__tablepre = '';
	// 原始表名
	protected  $_table;
	protected  $_shard_key;
	// 原始表名和真实表名对照表
	protected  $__tables = array();
	// 分库/分表方法类
	protected $__shard = null;
	// 主键字段
	protected $_pk = '';
	// 字段对应的值
	protected $_bind_params = array();
	/** 是否使用自建查询语句进行查询 */
	protected $_complex_sql = false;

	public function __construct($cfg = null) {

		// 默认都进行了分库/分表操作
		list($cfg, $table) = $this->_shard_table($this->_table, $this->_shard_key);
		$this->__tablepre = $cfg['tablepre'];

		$this->_table = $this->_table($this->_table);
		parent::__construct($cfg);
	}

	protected function _shard_table($table, $shard_key = array()) {

		// 获取应用名
		$cfg_name = startup_env::get('cfg_name');
		list($dbname, $tname) = explode('.', $table);
		// 判断是否需要读取主配置
		if ('__'.basename(APP_PATH).'__' == $dbname) {
			list($cfg_name, $dbname, $tname) = explode('.', $table);
		}

		// 数据库配置(项目)列表
		$dbs = config::get($cfg_name.'.db.dbs');
		if (!in_array($dbname, $dbs)) {
			throw new db_exception('Can not find db config for table '.$table);
		}

		// 数据库配置
		$conf = config::get($cfg_name.'.db.'.$dbname);
		// 表格配置
		$tables = config::get($cfg_name.'.db.'.$dbname.'.tables');
		// 当前表所用的数据库
		$host = isset($tables[$tname]['host']) ? intval($tables[$tname]['host']) : 0;

		// 判断是否有分库/分表
		$shard_cfg = config::get($cfg_name.'.db.'.$table.'.shard');
		if (empty($shard_key) || empty($shard_cfg)) {
			return array($conf[$host], $table);
		}

		// 分库/分表配置开始
		$this->__shard = &db_shard::factory($shard_cfg, $shard_key);
		return array($this->__shard->get_db_conf($conf[$host]), $table);
	}

	/**
	 * 先根据表名称, 切换对应的连接, 然后返回真实的表名
	 *
	 * @param string $tablename
	 * @return unknown
	 */
	protected function _table_name($tablename) {

		return $this->__tablepre.$tablename;
	}

	/**
	 * 获取真实表名
	 * @param string $table
	 */
	protected function _table($table) {

		// 如果有分表
		if ($this->__shard) {
			return $this->_table_name($this->__shard->get_table($table));
		}

		// 表格别名和真实名称对照表
		if (!array_key_exists($table, $this->__tables)) {
			if (FALSE === stripos($table, '.')) {
				$this->__tables[$table] = $table;
			} else {
				// 取出表名的最后一位【如：__uc__.uc.tablename，中的tablename】
				$tmp = array_slice(explode('.', $table), -1, 1, false);
				$this->__tables[$table] = $tmp[0];
				unset($tmp);
			}
		}

		return $this->_table_name($this->__tables[$table]);
	}

	/**
	 * Set a value for Request object.
	 *
	 * @param   mixed   $name   Request param name
	 * @param   mixed   $value  Request param value
	 * @return  void
	 */
	protected function _set($key, $value) {

		$this->_a_clean[$key] = $value;
		return $this;
	}

	public function __set($key, $value) {

		$this->_a_clean[$key] = $value;
	}

	public function __get($key) {

		if (isset($this->_a_clean[$key])) {
			return $this->_a_clean[$key];
		} else {
			//throw new ZOL_Exception("Notice: Undefined variable '$key'");
			trigger_error("Notice: Undefined variable '$key'");
			return false;
		}
	}

	public function __isset($name) {

		return isset($this->_a_clean[$name]);
	}

	public function __unset($name) {

		unset($this->_a_clean[$name]);
	}

	public function __call($name, $arguments) {

		$this->$name = $arguments[0];
		return $this;
	}

	/**
	 * 把参数加入对象
	 * @param array $aParams
	 * @return orm
	 */
	protected function _add(array $params) {

		$this->_a_clean = array_merge($this->_a_clean, $params);
		return $this;
	}

	// 重置查询条件
	public function reset_condi() {

		$this->_conds = array();
		return $this;
	}

	/**
	 * 重设 orm 对像
	 *
	 * @param void
	 * @reutn orm
	 */
	public function reset() {

		$this->_limit = 0;
		$this->_conds = array();
		$this->_orders = array();
		$this->_group_by = '';
		$this->_fields = array();
		$this->_a_clean = array();

		return $this;
	}

	/**
	 * 获取主键名 just for mysql
	 * @param  void
	 * @return string
	 */
	protected function _get_primary_field() {

		// 如果主键值为空
		if (empty($this->_pk)) {
			$sql = "describe {$this->_table}";
			foreach ($this->_query($sql) as $row) {
				if ($row['Key'] == 'PRI') {
					$this->_pk = $row['Field'];
				}
			}
		}

		return $this->_pk;
	}

	/**
	 * 查询的字段
	 * @param string $fields   需要查询的字段  * = 所有字段,   "field1, field2, etc...."多个字段
	 * @return orm
	 */
	protected function _select($fields = "") {

	   $this->_fields = $fields;
		return $this;
	}

	/**
	 * group by
	 * @param string $name   需要查询的字段  * = 所有字段,   "field1, field2, etc...."多个字段
	 * @return orm
	 */
	protected function _group_by($name) {

		$this->_group_by = " GROUP BY $name ";
		return $this;
	}

	/**
	 * in条件
	 * 场景
	 * $atoal = $t->in($item, 'id')->in(...)->total();
	 * @param mixed $item   查询的条件
	 * @param string $field 查询的字段名，默认为主健
	 * @return orm
	 */
	protected function _in($item, $field = '') {

		if ($item instanceof orm) {
			return false;
		}

		// 设置值
		$this->_condi(($field ? $field : $this->_get_primary_field()).' IN (?)', $item);
		return $this;
	}

	/**
	 * order by
	 * 场景
	 * $atoal = $t->in($table, 'id')->order_by('id', 'DESC')->find_all();
	 * @param string $sort
	 * @param string $dir   desc | asc 默认是desc
	 * @return orm
	 */
	protected function _order_by($sort, $dir = 'DESC') {

		$dir = rstrtoupper($dir);
		$this->_orders[$sort] = $sort.' '.('DESC' == $dir ? $dir : 'ASC');
		return $this;
	}

	/**
	 * limit
	 * 场景
	 * $list = $t->in($table, 'id')->limit(1)->find_all();
	 * $list = $t->in($table, 'id')->limit('1, 2')->find_all();
	 * $list = $t->in($table, 'id')->limit(array('1', '2'))->find_all();
	 * @param mixed $n  "1", "1, 2", "array('1', '2')"
	 * @return orm
	 */
	protected function _limit($n = 1) {

		if (is_array($n)) {
			if (count($n) <= 2) {
				$this->_limit = implode(',', $n);
			} else {
				trigger_error('sql limit array Less than 2 col!', E_USER_WARNING);
			}
		} else {
			$this->_limit = $n;
		}

		return $this;
	}

	/**
	 * 获取需要更新的字段信息
	 * @param array $fields 字段信息
	 * @param array $vals 数据数组
	 * @return boolean
	 */
	protected function _get_update_fields_values(&$fields, &$vals) {

		if (empty($this->_a_clean)) {
			return true;
		}

		// 获取字段/数据数组
		foreach ($this->_a_clean as $key => $val) {
			if (is_array($val)) {
				continue;
			}

			if (FALSE === stripos($key, '?')) { // 如果条件键值是字段名
				$fields[] = "`$key` = ?";
			} else {
				$fields[] = $key;
			}

			$vals[] = $val;
		}

		return true;
	}

	/**
	 * 多条件
	 * 场景
	 * $list = $t->condi('a=?', 1)->condi('b>?', 1)->find_all();
	 * @param string $str
	 * @return orm
	 */
	protected function _condi($condition, $values) {

		$this->_conds[$condition] = $values;
		return $this;
	}

	/**
	 * 把参数合并到查询条件
	 * @param array $arr 参数数组
	 * @return boolean
	 */
	protected function _merge2conds($arr) {

		foreach ($arr as $field => $value) {
			if (FALSE === stripos($field, '?')) { // 如果条件键值是字段名
				$this->_conds["{$field}".(is_array($value) ? ' IN (?)' : ' = ?')] = $value;
			} else {
				$this->_conds["{$field}"] = $value;
			}
		}

		return true;
	}

	/**
	 * 拼查询条件
	 * @param array &$wheres where 条件数组
	 * @param string $condi 字段条件
	 * @param mixed $value 值
	 * @return string
	 */
	protected function _field_sign_condi(&$wheres, $condi, $value) {

		if (is_array($value)) { // IN/NOT IN 条件
			// 如果条件参数错误
			/**if (FALSE === stripos($condi, '(?)')) {
				trigger_error('condition params error.', E_USER_ERROR);
				return false;
			}*/

			// 如果数组为空
			if (empty($value)) {
				return true;
			}

			// 如果包含了多个条件
			$condis = explode('?', $condi);
			if (2 < count($condis)) {
				$last = array_pop($condis);
				$tmp_wheres = array();
				foreach ($condis as $_k => $_v) {
					$_v .= '(' == substr($_v, -1) ? '?)' : '?';

					$this->_field_sign_condi($tmp_wheres, $_v, $value[$_k]);
				}

				$tmp_wheres[] = $last;
				$whstr = implode('', $tmp_wheres);
				$wheres[] = str_replace('))', ')', $whstr);
				return true;
			}

			$vars = array();
			foreach ($value as $v) {
				$this->_bind_params[] = $v;
				$vars[] = '?';
			}

			$wheres[] = str_replace("(?)", "(".implode(',', $vars).")", $condi);
			return true;
		}

		$this->_bind_params[] = $value;
		$wheres[] = $condi;
		return true;
	}

	// 拼凑 where 语句
	protected function _where($is_update = false) {

		// 判断是否使用复杂的自建查询语句
		if ($this->_complex_sql) {
			// 构造临时生成数据变量
			$where = " WHERE ".$this->_complex_sql;
			// 置空查询语句，避免后面加载错误
			$this->_complex_sql = null;
			// 返回
			return $where;
		}

		// 初始化参数
		$this->_bind_params = array();
		// 把属性合并到查询条件
		$is_update || $this->_merge2conds($this->_a_clean);

		$wheres = array();
		// 遍历查询条件
		foreach ($this->_conds as $condi => $val) {
			$this->_field_sign_condi($wheres, $condi, $val);
		}

		// 查询条件字串
		$condition = "";
		if (!empty($wheres)) {
			$condition = " WHERE ".implode(' AND ', $wheres);
		}

		// 为字段增加 ` 符号
		$condition = preg_replace_callback("/(([a-z0-9_]+)(\s*)(=|>|<|!))/i", array($this, '_cb'), $condition);
		$condition = preg_replace_callback("/(([a-z0-9_]+)(\s+)(like|not like|in|not in|between|not between))(\s+)/i", array($this, '_cb'), $condition);

		return $condition;
	}

	/**
	 * 处理回调
	 * @param array $match 正则匹配的数据
	 * @return string
	 */
	protected function _cb($match) {

		$field = strtolower($match[2]);
		return '`'.$field.'`'.$match[3].$match[4];
	}

	// 拼凑 sql 语句的 from 以及之后部分
	protected function _from() {

		return " FROM {$this->_table} ".$this->_where()." ".$this->_g_o_l();
	}

	// 拼凑 sql 语句的 group/order/limit
	protected function _g_o_l() {

		// limit
		$limit = '';
		if (!empty($this->_limit)) {
			$limit = ' LIMIT '.$this->_limit;
		}

		// order by
		$order_by = '';
		if (!empty($this->_orders)) {
			$order_by = 'ORDER BY '.implode(',', $this->_orders);
		}

		return "{$this->_group_by} {$order_by} {$limit}";
	}

	/**
	 * 获取查询的sql
	 * @param string $fields
	 * @return string
	 */
	protected function _find_sql($fields = '', $func_sql = null) {

		// 如果有 sql
		if (method_exists($this, $func_sql)) {
			return $this->$func_sql($fields);
		} else if (0 < strlen($func_sql)) {
			return $func_sql;
		}

		$fields = empty($fields) ? $this->_fields : $fields;
		if (empty($fields)) {
			$fields = "*";
		} else {
			// need fixed
			$fields = (is_string($fields) ? $fields : implode(',', $fields));
		}

		return "SELECT $fields ".$this->_from();
	}

	/**
	 * 插入数据
	 * @param array $data  字段映射，参数可选 array(field => value, etc..)
	 * @return boolean
	 */
	protected function _insert(array &$data = array()) {

		// 合并数据
		$this->_add($data);
		if (empty($this->_a_clean)) {
			return false;
		}

		// 分析数据
		foreach ($this->_a_clean as $key => $val) {
			if (!is_array($val)) {
				$fields[] = '`'.$key.'`';
				$values[] = $val;
				$values_prepare[] = "?";
			}
		}

		// 拼凑 sql
		$sql = "INSERT INTO {$this->_table} (". implode(',', $fields).") VALUES (".implode(',', $values_prepare).")";
		if ($this->_execute($sql, $values)) {
			$lastid = $this->_lastInsertId();
			$pri = $this->_get_primary_field();
			$data[$pri] = $lastid;
			return true;
		}

		return false;
	}

	/**
	 * 查找   returns a PDOStatement object, or FALSE on failure.
	 * 使用场景
	 * $data = array();
	 * foreach ($t->find('uid, name') as $item) {
	 *	 $data[] = $item;
	 * }
	 * @param mix $fields
	 * @return Ambigous <object, PDOStatement>
	 */
	protected function _find($fields = array(), $func_sql = null) {

		// 获取 sql 语句
		$sql = $this->_find_sql($fields, $func_sql);
		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			return $sth->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	}

	/**
	 * 查找所有
	 * 使用场景
	 * $t->find_all();
	 * @param mix $fields
	 * @param mix $pk 做为返回列表键名的字段名，默认：pk=null 使用表主键
	 * @return Ambigous <NULL, multitype:>
	 */
	protected function _find_all($fields = array(), $pk = null, $func_sql = null) {

		// 获取 sql 语句
		$sql = $this->_find_sql($fields, $func_sql);
		// 执行
		$sth = null;
		// 确定返回的键名
		if (null === $pk && !empty($this->_pk)) {
			$pk = $this->_pk;
		}

		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			// 读取数据
			if (!$list = $sth->fetchAll(PDO::FETCH_ASSOC)) {
				return false;
			}

			// 如果主键为空, 则直接返回
			if (empty($pk) || empty($list) || empty($list[0][$pk])) {
				return $list;
			}

			// 转换主键键值
			$rets = array();
			foreach ($list as $_v) {
				if (empty($_v[$pk])) {
					$rets[] = $_v;
				} else {
					$rets[$_v[$pk]] = $_v;
				}
			}

			return $rets;
		}

		return false;
	}

	/**
	 * 查找一行
	 * 使用场景
	 * $t->find_row('uid, name');
	 * @param mix $fields
	 * @return Ambigous <NULL, mixed>
	 */
	protected function _find_row($fields = array(), $func_sql = null) {

		// 获取 sql 语句
		$sql = $this->_find_sql($fields, $func_sql);
		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			return $sth->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	}

	/**
	 * 查找一个字段值
	 * 使用场景
	 * $name = $t->id(1)->find_one('name');
	 * @param unknown $fields
	 * @return Ambigous <NULL, string>
	 */
	protected function _find_one($fields = '', $func_sql = null) {

		// 获取 sql
		$sql = $this->_find_sql($fields, $func_sql);
		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			return $sth->fetchColumn();
		}

		return false;
	}

	/**
	 * 统计
	 * 使用场景
	 * $total = $t->total():
	 * @param void
	 * @return Ambigous <NULL, string>
	 */
	protected function _total($func_sql = null) {

		// 临时保存 limit
		$limit = $this->_limit;
		$this->_limit = '';
		// 获取 sql
		$sql = $this->_find_sql(' COUNT(*) ', $func_sql);

		// 恢复 limit
		$this->_limit = $limit;
		// 执行
		$sth = null;
		if ($this->_execute($sql, $this->_bind_params, $sth)) {
			return $sth->fetchColumn();
		}

		return false;
	}

	/**
	 * 删除
	 * $t->delete(1); //删除主健为1的数据
	 * $t->delete(array(1, 2));//删除主健为1 和2的数据
	 * @param mixed $items
	 * @return boolean
	 */
	protected function _delete($items = null) {

		// 删除条件
		if (null !== $items) {
			$pri = $this->_get_primary_field();
			$this->_condi($pri.(is_array($items) ? ' IN (?)' : '=?'), $items);
		}

		$sql = 'DELETE '.$this->_from();
		return $this->_execute($sql, $this->_bind_params);
	}

	/**
	 * 更新数据
	 * @param mixed $fields 需要更新的字段信息
	 * @return boolean
	 */
	protected function _update($fields = null) {

		// 获取主键
		$pri = $this->_get_primary_field();
		// 如果为数组, 则为待更新字段信息
		if (is_array($fields)) {
			$this->_a_clean = array_merge($this->_a_clean, $fields);
		} elseif (null !== $fields) { // 非数组则为更新条件
			$this->_condi($pri.'=?', $fields);
		}

		// limit
		$limit = '';
		if (!empty($this->_limit)) {
			$limit = ' LIMIT '.$this->_limit;
		}

		// 获取更新信息
		$fields = array();
		$values = array();
		$this->_get_update_fields_values($fields, $values);
		// 如果字段或数据数组为空, 则说明参数有错
		if (empty($fields) || empty($values)) {
			trigger_error('update params error.', E_USER_ERROR);
			return false;
		}

		// sql
		$sql = sprintf("UPDATE {$this->_table} SET %s ".$this->_where(true)." $limit", implode(',', $fields));
		$params = array_merge($values, $this->_bind_params);
		return $this->_execute($sql, $params);
	}

	/**
	 * 执行 sql 语句
	 * @param string $sql sql语句
	 * @param array $data 数据数组
	 * @param
	 * @return boolean
	 */
	protected function _execute($sql, $data = array(), &$sth = null) {

		// SQL 日志
		sql_record($sql, $data);

		$this->reset();
		$sth = $this->_prepare($sql);
		$ret = $sth->execute($data);
		if (!$ret) {
			$error = $sth->errorInfo();
			trigger_error($error[2].$sql.var_export($data, true), E_USER_WARNING);
		}

		return $ret;
	}

	/**
	 * 解析条件
	 * @param array $conds 条件
	 */
	protected function _parse_conds($conds) {

		if (is_array($conds)) {
			foreach ($conds as $field => $val) {
				if (FALSE === stripos($field, '?')) { // 如果条件键值是字段名
					$this->_condi($field.(is_array($val) ? ' IN (?)' : '=?'), $val);
				} else {
					$this->_condi($field, $val);
				}
			}
		} elseif(!empty($conds)) {
			$this->_condi($this->_pk.'=?', $conds);
		}

		return true;
	}

	/**
	 * 获取表字段默认值
	 * @return boolean
	 */
	public function get_default_value() {

		$list = array();

		$sql = "SHOW FIELDS FROM {$this->_table}";
		foreach ($this->_query($sql) as $r) {
			$list[$r['Field']] = isset($r['Default']) ? $r['Default'] : '';
		}

		return (array)$list;
	}



}
