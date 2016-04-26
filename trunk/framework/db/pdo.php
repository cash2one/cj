<?php
/**
 * 数据库操作类
 * $Author$
 * $Id$
 */

class db_pdo {
	/** db 连接 */
	private $__db;
	/** db 配置 */
	private $__cfg;
	/** 原始表名 */
	private $__table;
	/** 原始表名和真实表名对照表 */
	private $__tables = array();
	/** 分库/分表方法类 */
	private $__shard = null;

	/**
	 * 实例化
	 * @param string $table 表名
	 * @param array $shard_key 分库分表关键字
	 * @throws db_exception
	 */
	public function db_pdo($table, $shard_key) {
		/** 默认都进行了分库/分表操作 */
		list($cfg, $table) = $this->shard_table($table, $shard_key);
		/** 记录数据库配置 */
		$this->__cfg = $cfg;
		/** 取真实的表名 */
		$this->__table = $table;
		/** 连接 */
		$this->__db = pdodb::connect(
			$this->__cfg['dsn'],
			$this->__cfg['user'],
			$this->__cfg['password'],
			$this->__cfg['charset'],
			$this->__cfg['tablepre'],
			$this->__cfg['failover'],
			$this->__cfg['persistent'],
			$this->__cfg['timeout']
		);
	}

	public function shard_table($table, $shard_key = array()) {
		/** 获取应用名 */
		$app_name = startup_env::get('app_name');
		list($dbname, $tname) = explode('.', $table);
		/** 数据库配置(项目)列表 */
		$dbs = config::get($app_name.'.db.dbs');
		if (!in_array($dbname, $dbs)) {
			throw new db_exception('Can not find db config for table '.$table);
		}

		/** 数据库配置 */
		$conf = config::get($app_name.'.db.'.$dbname);
		/** 表格配置 */
		$tables = config::get($app_name.'.db.'.$dbname.'.tables');
		/** 当前表所用的数据库 */
		$host = isset($tables[$tname]['host']) ? intval($tables[$tname]['host']) : 0;

		/** 判断是否有分库/分表 */
		$shard_cfg = config::get($app_name.'.db.'.$table.'.shard');
		if (empty($shard_key) || empty($shard_cfg)) {
			return array($conf[$host], $table);
		}

		/** 分库/分表配置开始 */
		$this->__shard = &db_shard::factory($shard_cfg, $shard_key);
		return array($this->__shard->get_db_conf($conf[$host]), $table);
	}

	public function object() {
		return $this->__db;
	}

	/**
	 * 获取真实表名
	 * @param string $table
	 */
	public function table($table) {
		if ($this->__shard) {
			return $this->__db->table_name($this->__shard->get_table($table));
		}

		if (!array_key_exists($table, $this->__tables)) {
			$this->__tables[$table] = FALSE === stripos($table, '.') ? $table : substr($table, stripos($table, '.') + 1);
		}

		return $this->__db->table_name($this->__tables[$table]);
	}

	/**
	 * 获取一条记录
	 *
	 * @param string $conditions
	 * @param array $param
	 * @param array columns 列
	 * @return array || null
	 */
	public function fetch($conditions = null, $params = array(), $columns = array('*')) {

		$columns = implode(',', $columns);
		$sql = "SELECT $columns FROM ".$this->table($this->__table);

		if ($conditions) {
			$sql .= ' WHERE '.$conditions;
		}

		try {
			$data = $this->__db->fetch($sql, $params);
		} catch (PDOException $e) {
			throw new db_exception($e->getMessage());
		}

		return $data;
	}

	/**
	 * 取得所有的记录
	 *
	 * @param array $conditions
	 * @param array $params
	 * @param array $columns
	 * @param integer $start
	 * @param integer $limit
	 * @param string $order
	 *  + fieldName1 => [ASC|DESC]
	 *  + fieldName2 => [ASC|DESC]
	 * @return array
	 */
	public function fetch_all($conditions = null, $params = array(), $columns = array('*'), $start = 0, $limit = 0, $order = array()) {

		$columns = implode(',', $columns);
		$sql = "SELECT $columns FROM ".$this->table($this->__table);

		if ($conditions) {
			$sql .= ' WHERE '.$conditions;
		}

		if ($order && is_array($order)) {
			$orderClause = '';
			foreach ($order as $field => $orderBy) {
				$orderClause .= $field.' '.$orderBy.',';
			}

			$sql .= ' ORDER BY '.rtrim($orderClause, ',');
		} else if ($order && is_string($order)) {
			$sql .= ' ORDER BY '.$order;
		}

		if ($limit) {
			$sql .= " LIMIT $start, $limit";
		}

		try {
			$rows = $this->__db->fetch_all($sql, $params);
		} catch (PDOException $e){
			throw new db_exception($e->getMessage());
		}

		return $rows;
	}

	/**
	 * find_by_sql
	 *
	 * @param string $sql
	 * @param array $params
	 * @return array
	 */
	public function find_by_sql($sql, $params = array()) {

		try {
			$sql = $this->_rewrite_sql($sql);
			$data = $this->__db->fetch_all($sql, $params);
		} catch (PDOException $e){
			throw new db_exception($e->getMessage());
		}

		return $data;
	}

	/**
	 * 删除表记录
	 *
	 * @param string|array $condition 删除条件
	 * @param array $params
	 * @return unknown
	 */
	public function remove($conditions, $params) {

		$sql = "DELETE FROM ".$this->table($this->__table)." WHERE $conditions";
		try {
			return $this->__db->exec($sql, $params);
		} catch (PDOException $e){
			throw new db_exception($e->getMessage());
		}
	}

	/**
	 * 插入一条记录 或更新表记录
	 *
	 * @param array $data
	 * @param string $conditions
	 * @param array $param
	 * @return bool || int
	 */
	public function save($data, $conditions = NULL, $params = NULL) {

		$temp_params = array();
		$set = array();
		foreach ($data as $k => $v) {
			array_push($set, $k.'= ?');
			array_push($temp_params, $v);
		}

		if ($conditions) {
			// 更新
			$sql = "UPDATE ".$this->table($this->__table)." SET ".join(',',$set)." WHERE $conditions";
			$params = array_merge($temp_params, $params);
		} else {
			// 插入
			$sql = "INSERT INTO ".$this->table($this->__table)." SET ". join(',', $set);
			$params = $temp_params;
		}

		// 捕获PDOException后 抛出db_exception
		try {
			return $this->__db->exec($sql, $params);
		} catch (PDOException $e){
			throw new db_exception($e->getMessage());
		}
	}

	/**
	 * replace
	 * 根据主键替换或保存
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function replace($data) {

		$temp_params = array();
		$set = array();
		foreach ($data as $k => $v) {
			array_push($set, $k.'= ?');
			array_push($temp_params, $v);
		}

		$sql = "REPLACE INTO ".$this->table($this->__table)." SET ". join(',', $set);

		try {
			return $this->__db->exec($sql, $temp_params);
		} catch (PDOException $e){
			throw new db_exception($e->getMessage());
		}
	}

	/**
	 * 设置时区
	 * @param float $timeoffset
	 */
	public function set_time_zone($timeoffset) {
		$timezone = ($timeoffset > 0 ? '+' : '-').$timeoffset;
		if (strpos($timezone,':') === false) {
			$timezone .= ':00';
		}

		$this->query("SET time_zone='{$timezone}'");
	}

	/**
	 * 一次插入多条记录
	 *
	 * @param array $data
	 */
	public function multi_insert($data) {

		$count = count($data);
		$get_keys = array_keys($data[0]);
		$count_keys = count($get_keys);
		$columns = implode(',', $get_keys);

		// 构造问号表达式
		$tmp_arr = array();
		for($i = 0; $i< $count_keys; $i ++) {
			$tmp_arr[] = '?';
		}

		$tmp_str = '('.implode(',', $tmp_arr).')';
		$tmp_arr2 = array();
		$merge_arr = array();
		for ($i = 0; $i < $count; $i ++) {
			$tmp_arr2[] = $tmp_str;
			$merge_arr = array_merge($merge_arr, array_values($data[$i]));
		}

		$tmp_str2 = implode(',', $tmp_arr2);
		$conditions = "INSERT INTO ".$this->table($this->__table)." ($columns) VALUES $tmp_str2";
		try {
			return $this->__db->exec($conditions,$merge_arr);
		} catch (PDOException $e) {
			throw new db_exception($e->getMessage());
		}
	}

	/**
	 * count
	 * 计算行数
	 *
	 * @param string $conditions
	 * @param array $params
	 * @return integer
	 */
	public function count($conditions = NULL, $params = array()) {

		$sql = 'SELECT COUNT(*) FROM '.$this->table($this->__table);
		try {
			if ($conditions) {
				$sql .= ' WHERE '.$conditions;
			}

			return $this->__db->fetch_one($sql, $params);
		} catch (PDOException $e) {
			throw new db_exception($e->getMessage());
		}
	}

	/**
	 * exec
	 * 执行sql语句
	 *
	 * @param string $sql
	 * @param array $params
	 * @return void
	 */
	public function exec($sql, $params = array()) {

		try {
			$sql = $this->_rewrite_sql($sql);
			$result = $this->__db->exec($sql, $params);
		} catch (PDOException $e) {
			throw new db_exception($e);
		}

		return $result;
	}

	/**
	 * + num
	 * @param string $field 字段名
	 * @param string $conditions
	 * @param array $params
	 * @param number $unit
	 * @throws db_exception
	 * @return number
	 */
	public function incr($field, $conditions = null, $params = array(), $unit = 1) {

		$sql = 'UPDATE '.$this->table($this->__table)." SET `$field` = `$field` + $unit";
		if ($conditions) {
			$sql .= ' WHERE '.$conditions;
		}

		try {
			$result = $this->__db->exec($sql, $params);
		} catch (PDOException $e) {
			throw new db_exception($e->getMessage, $e->getCode());
		}

		return $result;
	}

	/**
	 * - num
	 * @param string $field
	 * @param string $conditions
	 * @param array $params
	 * @param number $unit
	 * @throws db_exception
	 * @return number
	 */
	public function decr($field , $conditions = null, $params = array(), $unit = 1) {

		$sql = 'UPDATE '.$this->table($this->__table)." SET $field = IF($field > $unit, $field - $unit, 0)";
		if ($conditions) {
			$sql .= ' WHERE '.$conditions;
		}

		try {
			$result = $this->__db->exec($sql, $params);
		} catch (PDOException $e) {
			throw new db_exception($e->getMessage);
		}

		return $result;
	}

	/** 清空表 */
	public function truncate() {

		$sql = "TRUNCATE ".$this->table($this->__table);
		$params = array();
		try {
			return $this->__db->exec($sql, $params);
		} catch (PDOException $e) {
			throw new db_exception($e->getMessage());
		}
	}

	/**
	 * 重写sql
	 * @param string $sql
	 * @return mixed
	 */
	protected function _rewrite_sql($sql) {

		$pattern = '/((?:select.*?from|insert into|delete from|update|replace into|truncate table|describe|alter table)\s+)`?(\w+)`?/i';
		return preg_replace($pattern, '\1'.$this->table($this->__table), $sql);
	}

	/**
	 * 获取刚刚写入记录的ID
	 * @return int
	 */
	public function insert_id() {

		try {
			return $this->__db->last_insert_id();
		} catch (PDOException $e){
			throw new db_exception($e->getMessage());
		}
	}

	public function begin() {
		return $this->__db->begin();
	}

	public function commit() {
		return $this->__db->commit();
	}

	public function rollback() {
		return $this->__db->rollback();
	}
}
