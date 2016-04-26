<?php
/**
 * 数据库操作类
 * $Author$
 * $Id$
 */

class db_table {
	/**
	 *  对象集合
	 *
	 *  @var object
	 */
	private static $_instances = array();
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
	 * &factory
	 * 实例化一个表方法
	 *
	 * @param string $class 表名
	 * @param array $shard_key 分库/分表参数
	 * @return object
	 */
	public static function &factory($table, $shard_key = array()) {
		ksort($shard_key, SORT_STRING);
		$tkey = $table.md5(http_build_query($shard_key));
		if (!array_key_exists($tkey, self::$_instances)) {
			self::$_instances[$tkey] = new self($table, $shard_key);
		}

		return self::$_instances[$tkey];
	}

	/**
	 * 实例化
	 * @param string $table 表名
	 * @param array $shard_key 分库分表关键字
	 * @throws db_exception
	 */
	public function db_table($table, $shard_key) {
		/** 默认都进行了分库/分表操作 */
		list($cfg, $table) = $this->shard_table($table, $shard_key);
		/** 记录数据库配置 */
		$this->__cfg = $cfg;
		/** 取真实的表名 */
		$this->__table = $table;
		/** 连接 */
		$this->__db = db::init($this->__cfg);
	}

	public function shard_table($table, $shard_key = array()) {
		/** 获取应用名 */
		$app_name = startup_env::get('app_name');
		list($dbname, $tname) = explode('.', $table);
		/** 数据库配置(项目)列表 */
		$dbs = config::get($app_name.'.db.dbs');
		if (!in_array($dbname, $dbs)) {
			throw new db_exception('Can not find db config for table ' . $table);
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
	 * 删除表记录
	 *
	 * @param string|array $condition 删除条件
	 * @param int $limit
	 * @param boolean $unbuffered
	 * @return unknown
	 */
	public function delete($condition, $limit = 0, $unbuffered = true) {
		$where = $this->parse_condition($condition);
		if (!$where) {
			return false;
		}

		$limit = rintval($limit);
		$sql = "DELETE FROM ".$this->table($this->__table)." WHERE $where ".($limit > 0 ? "LIMIT $limit" : '');
		return $this->query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}

	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		$sql = db_help::implode($data);
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$table = $this->table($this->__table);
		$silent = $silent ? 'SILENT' : '';
		return $this->query("$cmd $table SET $sql", null, $silent, !$return_insert_id);
	}

	public function insert_multi($data) {
		if (empty($data)) {
			return false;
		}

		$table = $this->table($this->__table);
		$fields = array();
		$values = array();
		foreach ($data AS $v) {
			if (empty($fields)) {
				foreach ($v AS $_k => $_v) {
					$fields[$_k] = db_help::quote_field($_k);
				}
			}

			$value = array();
			foreach ($fields AS $_k => $__tmp) {
				if (isset($v[$_k])) {
					$value[] = $v[$_k];
				}
			}

			if (!$value) {
				continue;
			}

			$values[] = '('.implode(',',db_help::quote($value)).')';
		}

		if (!$values) {
			return false;
		}

		$fields = implode(',',$fields);
		$values = implode(',',$values);
		return $this->query("INSERT IGNORE INTO {$table} ({$fields}) VALUES {$values}");
	}

	public function set_time_zone($timeoffset) {
		$timezone = ($timeoffset > 0 ? '+' : '-').$timeoffset;
		if (strpos($timezone,':') === false) {
			$timezone .= ':00';
		}

		$this->query("SET time_zone='{$timezone}'");
	}

	public function update($data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = db_help::implode($data);
		if (empty($sql)) {
			return false;
		}

		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$table = $this->table($this->__table);
		$where = $this->parse_condition($condition);
		if (!$where) {
			return false;
		}

		$res = $this->query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}

	public function insert_id() {
		return $this->__db->insert_id();
	}

	public function fetch($resourceid, $type = MYSQL_ASSOC) {
		return $this->__db->fetch_array($resourceid, $type);
	}

	public function fetch_first($sql, $arg = array(), $silent = false) {
		$res = $this->query($sql, $arg, $silent, false);
		$ret = $this->__db->fetch_array($res);
		$this->__db->free_result($res);
		return $ret ? $ret : array();
	}

	public function fetch_all($sql, $arg = array(), $keyfield = '', $silent=false) {
		$data = array();
		$query = $this->query($sql, $arg, $silent, false);
		while ($row = $this->__db->fetch_array($query)) {
			if ($keyfield && isset($row[$keyfield])) {
				$data[$row[$keyfield]] = $row;
			} else {
				$data[] = $row;
			}
		}

		$this->__db->free_result($query);
		return $data;
	}

	public function result($resourceid, $row = 0) {
		return $this->__db->result($resourceid, $row);
	}

	public function result_first($sql, $arg = array(), $silent = false) {
		$res = $this->query($sql, $arg, $silent, false);
		$ret = $this->__db->result($res, 0);
		$this->__db->free_result($res);
		return $ret;
	}

	public function query($sql, $arg = array(), $silent = false, $unbuffered = false) {

		// SQL 日志
		sql_record($sql, $arg);

		if (!empty($arg)) {
			if (is_array($arg)) {
				$sql = $this->format($sql, $arg);
			} elseif ($arg === 'SILENT') {
				$silent = true;
			} elseif ($arg === 'UNBUFFERED') {
				$unbuffered = true;
			}
		}

		$ret = $this->__db->query($sql, $silent, $unbuffered);
		if (!$unbuffered && $ret) {
			$cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
			if ($cmd === 'SELECT') {
			} elseif ($cmd === 'UPDATE' || $cmd === 'DELETE') {
				$ret = $this->__db->affected_rows();
			} elseif ($cmd === 'INSERT') {
				$ret = $this->__db->insert_id();
			}
		}

		return $ret;
	}

	/**
	 * 格式化字串, 类似 sprintf
	 *
	 * @param string $sql 待格式化字串
	 * @param array $arg 所需数据
	 * @return unknown
	 */
	public function format($sql, $arg) {
		$count = substr_count($sql, '%');
		if (!$count) {
			return $sql;
		} elseif ($count > count($arg)) {
			throw new db_exception('SQL string format error! This SQL need "'.$count.'" vars to replace into.', 0, $sql);
		}

		$len = strlen($sql);
		$i = $find = 0;
		$ret = '';
		while ($i <= $len && $find < $count) {
			if ($sql{$i} == '%') {
				$next = $sql{$i + 1};
				if ($next == 't') {
					$ret .= $this->table($arg[$find]);
				} elseif ($next == 's') {
					$ret .= db_help::quote(is_array($arg[$find]) ? serialize($arg[$find]) : (string) $arg[$find]);
				} elseif ($next == 'f') {
					$ret .= sprintf('%F', $arg[$find]);
				} elseif ($next == 'd') {
					$ret .= rintval($arg[$find]);
				} elseif ($next == 'i') {
					$ret .= $arg[$find];
				} elseif ($next == 'n') {
					if (!empty($arg[$find])) {
						$ret .= is_array($arg[$find]) ? implode(',', db_help::quote($arg[$find])) : db_help::quote($arg[$find]);
					} else {
						$ret .= '0';
					}
				} else {
					$ret .= db_help::quote($arg[$find]);
				}

				$i++;
				$find++;
			} else {
				$ret .= $sql{$i};
			}

			$i++;
		}

		if ($i < $len) {
			$ret .= substr($sql, $i);
		}

		return $ret;
	}

	public function parse_condition($condition) {
		if (empty($condition)) {
			return false;
		} elseif (!is_array($condition)) {
			return $condition;
		}

		if (count($condition) == 2 && isset($condition['where']) && isset($condition['arg'])) {
			$where = $this->format($condition['where'], $condition['arg']);
		} else {
			$arr = array();
			foreach ($condition as $k => $v) {
				$arr[] = db_help::field($k, $v);
			}

			$where = implode(' AND ', $arr);
		}
		return $where;
	}

	public function num_rows($resourceid) {
		return $this->__db->num_rows($resourceid);
	}

	public function affected_rows() {
		return $this->__db->affected_rows();
	}

	public function free_result($query) {
		return $this->__db->free_result($query);
	}

	public function error() {
		return $this->__db->error();
	}

	public function errno() {
		return $this->__db->errno();
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
