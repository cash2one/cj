<?php
/**
 * 用户表
 * $Author$
 * $Id$
 */

class db {
	/** 表前缀 */
	public $tablepre;
	/** 版本 */
	public $version = '';
	/** 查询次数 */
	public $querynum = 0;
	/** 连接 */
	public $link;
	/** 数据库配置 */
	public $config = array();
	public $sqldebug = array();
	private $__trans_times = 0;
	private static $s__dbs;

	function db() {
		/** do nothing. */
	}

	public static function init($cfg) {
		/** 取 hash值 */
		$values = array_values($cfg);
		sort($values, SORT_STRING);
		$md5 = md5(implode('', $values));
		/** 连接不存在, 则 */
		if (empty(self::$s__dbs[$md5])) {
			$db = new self();
			$db->set_config($cfg);
			$db->connect();
			self::$s__dbs[$md5] = $db;
		}

		return self::$s__dbs[$md5];
	}

	/** 设置数据库配置 */
	function set_config($config) {
		$this->config = &$config;
		$this->tablepre = $config['tablepre'];
	}

	/** 连接数据库 */
	function connect() {
		if(empty($this->config)) {
			$this->halt('config_db_not_found');
		}

		/** 开始连接 */
		$this->link = $this->_dbconnect(
			$this->config['host'],
			$this->config['user'],
			$this->config['pw'],
			$this->config['charset'],
			$this->config['dbname'],
			$this->config['pconnect']
		);
	}

	/**
	 * 连接数据库
	 * @param string $dbhost 数据库服务器地址
	 * @param string $dbuser 数据库用户名
	 * @param string $dbpw 数据库密码
	 * @param string $dbcharset 数据库字符集
	 * @param string $dbname 数据库名称
	 * @param boolean $pconnect 是否启用长连接
	 * @param boolean $halt
	 * @return unknown
	 */
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect, $halt = true) {
		if($pconnect) {
			$link = @mysql_pconnect($dbhost, $dbuser, $dbpw, MYSQL_CLIENT_COMPRESS);
		} else {
			$link = @mysql_connect($dbhost, $dbuser, $dbpw, 1, MYSQL_CLIENT_COMPRESS);
		}

		if(!$link) {
			$halt && $this->halt('notconnect', $this->errno());
		} else {
			$this->link = $link;
			if($this->version() > '4.1') {
				$dbcharset = $dbcharset ? $dbcharset : $this->config['charset'];
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}

			$dbname && @mysql_select_db($dbname, $link);
		}

		return $link;
	}

	/**
	 * 先根据表名称, 切换对应的连接, 然后返回真实的表名
	 *
	 * @param string $tablename
	 * @return unknown
	 */
	function table_name($tablename) {
		return $this->tablepre.$tablename;
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	public function checkquery($sql) {
		return db_safecheck::checkquery($sql);
	}

	public function query($sql, $silent = false, $unbuffered = false) {
		$this->checkquery($sql);
		if(defined('BD_DEBUG') && BD_DEBUG) {
			$starttime = microtime(true);
		}

		if('UNBUFFERED' === $silent) {
			$silent = false;
			$unbuffered = true;
		} elseif('SILENT' === $silent) {
			$silent = true;
			$unbuffered = false;
		}

		$func = $unbuffered ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->query($sql, 'RETRY'.$silent);
			}
			if(!$silent) {
				$this->halt($this->error()." ".$sql, $this->errno(), $sql);
			}
		}

		if(defined('BD_DEBUG') && BD_DEBUG) {
			$this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->link);
		}

		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->link);
		}

		return $this->version;
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt($message = '', $code = 0, $sql = '') {
		throw new dao_exception($message, $code, $sql);
	}

	/** 启动事务 */
	function begin() {
		if(!$this->link) {
			return false;
		}

		if($this->__trans_times == 0) {
			mysql_query('START TRANSACTION', $this->link);
		}

		$this->__trans_times++;
		return true;
	}

	/** 提交事务 */
	function commit() {
		if($this->__trans_times > 0) {
			$result = mysql_query('COMMIT', $this->link);
			$this->__trans_times = 0;
			if(!$result) {
				$this->halt('MySQL COMMIT Error.');
			}
		}

		return true;
	}

	/** 回滚事务 */
	function rollback() {
		if($this->__trans_times > 0) {
			$result = mysql_query('ROLLBACK', $this->link);
			$this->__trans_times = 0;
			if(!$result) {
				$this->halt('MySQL ROLLBACK Error.');
			}
		}

		return true;
	}
}
