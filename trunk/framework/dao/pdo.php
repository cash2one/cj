<?php
/**
 * 数据表格(pdo)操作基类
 * $Author$
 * $Id$
 */

class dao_pdo extends dao {

	/**
	 * 要操作的表名
	 *
	 * @var string
	 */
	protected $_table = '';

	/**
	 * 允许的字段
	 *
	 * @var array
	 */
	protected $_allowed = array();

	/**
	 * 必须的字段
	 *
	 * @var array
	 */
	protected $_required = array();

	/**
	 * 主键
	 *
	 * @var string
	 */
	protected $_pk = '';

	/** 初始化 */
	public function __construct() {

		if (!$this->_allowed || !is_array($this->_allowed)) {
			throw new InvalidArgumentException('param $this->_allowed cannt be empty!');
		}

		if (!is_array($this->_required)) {
			throw new InvalidArgumentException('praram $this->_required cannt be empty!');
		}
	}

	public static function &factory($tname, $shard_key) {

		if (!array_key_exists($tname, self::$_instances)) {
			list($dbname, $tn) = explode('.', $tname);
			if (isset($_SERVER['RUN_MODE']) && 'development' == $_SERVER['RUN_MODE']) {
				self::create_d($dbname, $tn);
			}

			self::$_instances[$tname] = new $tname($shard_key);
		}

		return self::$_instances[$tname];
	}

	public static function create_d($dbname, $tname) {

		return;
		$items = explode('_', $tname);
		$file = APP_PATH.'/src/include/d/'.join('/', $items).'.php';
		if (is_file($file)) {
			return true;
		}

		$count = count($items);
		while (0 < $count) {
			$count --;
			array_pop($items);
			$file = APP_PATH.'/src/include/d/'.join('/', $items).'/abstruct.php';
			if (is_file($file)) {
				break;
			}
		}

		$base_class = 'dao_pdo';
		if (0 < $count) {
			$base_class = startup_env::get('cfg_name').'_d_'.$tname.'_abstruct';
		}

		$db = new db_pdo($dbname.'.'.$tname);
		$db->exec('SHOW CREATE TABLE '.$tname);
	}

	/**
	 * add
	 *
	 * @param array $data 字段、值的关联数组
	 * @throws InvalidArgumentException
	 * @throws dao_exception
	 * @return mixed 成功返回新记录的id数
	 */
	public function insert($data, $shard_key = array()) {

		$fields = array_keys($data);
		$result = array_diff($fields, $this->_allowed);
		if ($result) {
			throw new InvalidArgumentException("invalid field(" . join(',', $result) . ")");
		}

		$result = array_diff($this->_required, $fields);
		if ($result) {
			throw new InvalidArgumentException(join(',', $result) . " is required.");
		}

		try {
			$db = new db_pdo($this->_table, $shard_key);
			$db->save($data);

			return $db->insert_id();
		} catch (PDOException $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * update 根据主键更新记录
	 *
	 * @param array $data 字段、值的关联数组
	 * @param string $pk 主键
	 * @param integer $pk_value 主键的值
	 * @param array $allowed_fs 允许更新的字段
	 * @throws InvalidArgumentException
	 * @throws dao_exception
	 * @return mixed 成功返回true
	 */
	public function update($data, $pk, $pk_value, $shard_key = array()) {

		$fields = array_keys($data);
		$result = array_diff($fields, $this->_allowed);
		if ($result) {
			throw new InvalidArgumentException("invalid field(" . join(',', $result) . ")");
		}

		$conditions = "$pk = ?";
		$params = array($pk_value);

		return $this->update_by_condition($data, $conditions, $params, $shard_key);
	}

	/**
	 * update_by_condition
	 * 根据某个字段更新一条或多条记录
	 *
	 * @param  array $data
	 * @param  array $conditions
	 * @param  array $params
	 * @param  array $shard_key
	 * @return void
	 */
	public function update_by_condition($data, $conditions, $params, $shard_key = array()) {

		$fields = array_keys($data);
		$result = array_diff($fields, $this->_allowed);
		if ($result) {
			throw new InvalidArgumentException("invalid field(" . join(',', $result) . ")");
		}

		try {

			$db = new db_pdo($this->_table, $shard_key);
			return $db->save($data, $conditions, $params);
		} catch (PDOException $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * remove
	 * 根据主键删除单条记录
	 *
	 * @param string $pk 主键
	 * @param mixed $pk_value 值
	 * @throws dao_exception
	 * @return boolean
	 */
	public function delete($pk, $pk_value, $shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);

			$conditions = "$pk = ?";
			$params = array($pk_value);

			return $db->remove($conditions, $params);
		} catch (PDOException $e) {
			throw new dao_exception($e);
		}

		return false;
	}

	/**
	 * exec_by_sql
	 * @param string $sql SQL语句
	 * @throws dao_exception
	 * @return boolean
	 */
	public function exec_by_sql($sql, $params = array(), $shard_key = array()) {

		if (!$sql || !is_array($params)) {
			throw new InvalidArgumentException('param $sql cannt by empty!');
		}

		try {
			$db = new db_pdo($this->_table, $shard_key);
			$result = $db->exec($sql, $params);
		} catch (PDOException $e) {
			throw new dao_exception($e);
		}

		return $result;
	}

	/**
	 * select
	 * 根据主键获取一条记录
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param array $columns
	 * @param array $shard_key
	 * @return void
	 */
	public function select($key, $value, $columns = array('*'), $shard_key = array()) {

		$conditions = "$key = ?";
		$params = array($value);

		return $this->get($conditions, $params, $columns, $shard_key);
	}

	/**
	 * fetch_by_ids
	 * 根据id获取多条记录
	 *
	 * @param mixed $key
	 * @param mixed $ids
	 * @param string $columns
	 * @param array $shard_key
	 * @return void
	 */
	public function list_by_ids($key, $ids, $page_options = array(), $columns = array('*'), $orderField = null, $orderType = null, $shard_key = array()) {

		$params = array();
		$conditions = $key . ' IN (' . $this->build_in_sql_clause($ids, $params) . ')';

		return $this->list_all($conditions, $params, $page_options, $columns, $orderField, $orderType, $shard_key);
	}

	/**
	 * fetch_all
	 * 取得多条记录
	 *
	 * @param array $conditions
	 * @param array $params
	 * @param array $page_options
	 * @param array $columns
	 * @param string $order
	 *   fieldName1 => [ASC|DESC]
	 *   fieldName2 => [ASC|DESC]
	 * @param array $shard_key
	 * @return array
	 */
	public function list_all($conditions = null, $params = array(), $page_options = array(), $columns = array('*'), $order = array(), $shard_key = array()) {

		if ($params && !is_array($params)) {
			throw new InvalidArgumentException('$params must be an array');
		}

		if ($columns && !is_array($columns)) {
			throw new InvalidArgumentException('$columns must be an array');
		}

		if ($page_options && !is_array($page_options)) {
			throw new InvalidArgumentException('$page_options must be an array');
		}

		if ($order && !is_array($order)) {
			throw new InvalidArgumentException('$order must be an array');
		}

		if ($shard_key && !is_array($shard_key)) {
			throw new InvalidArgumentException('$shard_key must be an array');
		}

		try {
			$db = new db_pdo($this->_table, $shard_key);

			if (!isset($page_options['perpage']) || 0 >= $page_options['perpage']) {
				$start = $limit = 0;
			} else {
				$start = $page_options['start'];
				$limit = $page_options['perpage'];
			}

			$data = $db->fetch_all($conditions, $params, $columns, $start, $limit, $order);
		} catch (Exception $e) {
			throw new dao_exception($e);
		}

		return $data;
	}

	/**
	 * get
	 * 获取一条记录
	 *
	 * @param mixed $conditions
	 * @param mixed $params
	 * @param string $columns
	 * @param array $shard_key
	 * @return void
	 */
	public function get($conditions = null, $params = array(), $columns = array('*'), $shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);
			$data = $db->fetch($conditions, $params, $columns);
		} catch (Exception $e) {
			throw new dao_exception($e);
		}

		return $data;
	}

	/**
	 * find_by_sql
	 *
	 * @param mixed $sql
	 * @param array $params
	 * @param array $shard_key
	 * @return array
	 */
	public function find_by_sql($sql, $params = array(), $shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);
			$data = $db->find_by_sql($sql, $params);
		} catch (Exception $e) {
			throw new dao_exception($e);
		}

		return $data;
	}

	/**
	 * count
	 * 计算总数
	 *
	 * @param mixed $conditions
	 * @param array $params
	 * @param array $shard_key
	 * @return void
	 */
	public function count($conditions = null, $params = array(), $shard_key = array()) {

		if ($params && !is_array($params)) {
			throw new InvalidArgumentException('$params must is an array');
		}

		try {
			$db = new db_pdo($this->_table, $shard_key);
			$num = $db->count($conditions, $params);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return intval($num);
	}

	/**
	 * build_in_sql_clause
	 * 根据数据($data)返回, SQL IN 子句
	 *
	 * @param mixed $data
	 * @param mixed $sql_params
	 * @throws InvalidArgumentException
	 * @return string
	 */
	public function build_in_sql_clause($data, &$sql_params) {

		if (!is_array($data)) {
			throw new InvalidArgumentException('参数必须是查询数组');
		}

		$params = array();
		foreach ($data as $v) {
			$params[] = '?';
			$sql_params[] = $v;
		}

		return join(',', $params);
	}

	/**
	 * incr
	 * 给一个字段增加一个单位值
	 *
	 * @param string $field 字段名
	 * @param string $conditions 条件
	 * @param array $params 参数
	 * @param integer $unit
	 * @return boolean
	 */
	public function incr($field, $conditions = null, $params = array(),  $unit = 1, $shard_key = array()) {

		if ($params && !is_array($params)) {
			throw new InvalidArgumentException('$params must is an array');
		}

		try {
			$db = new db_pdo($this->_table, $shard_key);
			return $db->incr($field, $conditions, $params, $unit);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * decr
	 * 给一个字段减少一个单位值
	 *
	 * @param string $field 字段名
	 * @param string $conditions 条件
	 * @param array $params 参数
	 * @param integer $unit
	 * @return boolean
	 */
	public function decr($field, $conditions = null, $params = array(),  $unit = 1, $shard_key = array()) {

		if ($params && !is_array($params)) {
			throw new InvalidArgumentException('$params must is an array');
		}

		try {
			$db = new db_pdo($this->_table, $shard_key);
			return $db->decr($field, $conditions, $params, $unit);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * 获取刚刚写入记录的ID
	 * @return int
	 */
	public function insert_id($shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);
			return $db->insert_id();
		} catch (PDOException $e){
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}

	public function begin($shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);
			return $db->begin();
		} catch (PDOException $e){
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}

	public function commit($shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);
			return $db->commit();
		} catch (PDOException $e){
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}

	public function rollback($shard_key = array()) {

		try {
			$db = new db_pdo($this->_table, $shard_key);
			return $db->rollback();
		} catch (PDOException $e){
			throw new dao_exception($e->getMessage(), $e->getCode());
		}
	}
}
