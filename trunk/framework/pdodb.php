<?php
/**
 * pdo - 封装对数据库的操作
 * $Author$
 * $Id$
 */

class pdodb {
	/** 数据库连接数组 */
	private static $__conns = array();
	/** 连接 */
	private $__dbh;
	/** 表格前缀 */
	private $__tablepre;

	/**
	 * pdo 构造函数
	 *
	 * @param string $dsn
	 * @param string $user
	 * @param string $password
	 * @param string $charset
	 * @param string $failover
	 * @param boolean $persistent
	 * @param integer $timeout
	 */
	private function __construct($dsn, $user, $password, $charset, $tablepre = '', $failover = '', $persistent = false, $timeout = 0) {

		$attr = array(
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_PERSISTENT => $persistent
		);

		if (0 < $timeout) {
			$attr[PDO::ATTR_TIMEOUT] = $timeout;
		}

		$this->__tablepre = $tablepre;

		try {
			$this->__dbh = new PDO($dsn, $user, $password, $attr);
			$this->__dbh->exec("SET NAMES '" . $charset . "'");
		} catch (PDOException $e){

			if ($failover) {
				try {
					$this->__dbh = new PDO($failover, $user, $password, $attr);
					$this->__dbh->exec("SET NAMES '" . $charset . "'");
				} catch (PDOException $e){
					throw new db_exception("can't connect to the server because:" . $e->getMessage());
				}
			} else {
				throw new db_exception("can't connect to the server because:" . $e->getMessage());
			}
		}
	}

	/**
	 * 获取数据库连接类
	 *
	 * @param string $dsn
	 * @param string $user
	 * @param string $password
	 * @param string $charset
	 * @param string $failover
	 * @param boolean $persistent
	 * @param integer $timeout
	 * @return pdo 实例
	 */
	public static function connect($dsn, $user, $password, $charset, $tablepre = '', $failover = '', $persistent = false, $timeout = 0) {

		if (!array_key_exists($dsn, self::$__conns)){
			self::$__conns[$dsn] = new pdodb($dsn, $user, $password, $charset, $tablepre, $failover, $persistent, $timeout);
		}

		return self::$__conns[$dsn];
	}

	/**
	 * 开启事物
	 */
	public function begin() {

		$this->__dbh->beginTransaction();
	}

	/**
	 * 提交事务
	 */
	public function commit() {

		$this->__dbh->commit();
	}

	/**
	 * 回滚事务
	 */
	public function rollback() {

		$this->__dbh->rollBack();
	}

	/**
	 * 取得记录的第一行
	 *
	 * @param string $query
	 * @param array $params
	 */
	public function fetch($query, $params = array()) {

		$stmt = $this->__dbh->prepare($query);
		$stmt->execute($params);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
	}

	/**
	 * 取得所有的记录
	 *
	 * @param string $query
	 * @param array $params
	 * @return array
	 */
	public function fetch_all($query, $params = array()) {

		$stmt = $this->__dbh->prepare($query);
		$stmt->execute($params);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $rows;
	}

	/**
	 * 获取记录的第一行第一列
	 *
	 * @param string $query
	 * @param array $params
	 */
	public function fetch_one($query, $params = array()) {

		$stmt = $this->__dbh->prepare($query);
		$result = $stmt->execute($params);
		if ($result) {
			$row = $stmt->fetchColumn();
		}

		return $row;
	}

	/**
	 * 执行sql 语句
	 *
	 * @param string $query
	 * @param array $params
	 * @return 更新的记录的条数
	 */

	public function exec($query, $params = array()) {

		$stmt = $this->__dbh->prepare($query);
		return $stmt->execute($params);
	}

	/**
	 * 获取最后一条记录的id
	 *
	 * @return string
	 */
	public function last_insert_id() {

		return $this->__dbh->lastInsertId();
	}

	/**
	 * 关闭数据库连接
	 * @param string $dsn
	 */
	public function close($dsn = null) {

		if ($dsn) {
			self::$__conns[$dsn] = NULL;
		} else {
			$this->__dbh = NULL;
		}
	}

	/**
	 * 先根据表名称, 切换对应的连接, 然后返回真实的表名
	 *
	 * @param string $tablename
	 * @return unknown
	 */
	function table_name($tablename) {
		return $this->__tablepre.$tablename;
	}
}
