<?php
/**
 * 数据库连接
 * @Author
 */

class db_mo {
	// pdo 链接
	protected $dbh;
	protected $master;
	protected $slave;
	protected $forceReadMaster = false;
	protected $username = 'root';
	protected $password;
	protected $engine = 'mysql';
	protected $sqlComment = '';
	protected $database = '';
	private static $__instance = array();
	/** db 配置 */
	private $__cfg;

	public function __construct($cfg) {

		/** 记录数据库配置 */
		$this->__cfg = $cfg;
		// 先排序
		asort($cfg, SORT_STRING);
		// 取数组的 md5
		$md5 = md5(serialize($cfg));
		if (array_key_exists($md5, self::$__instance)) {
			$this->dbh = self::$__instance[$md5];
			return true;
		}

		// 建立连接
		$attr = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_PERSISTENT => $this->__cfg['persistent']
		);
		$this->dbh = new PDO($this->__cfg['dsn'], $this->__cfg['user'], $this->__cfg['password'], $attr);

		// 在每条sql后尾增加一条注释，好让DBA方便查出问题的sql出自什么应用。
		if (!empty($_SERVER['HTTP_HOST'])) {
			$this->sqlComment = empty($_SERVER['REQUEST_METHOD'])
				? ( '/* '.addslashes(strtr($_SERVER['HOSTNAME'].$_SERVER['SCRIPT_FILENAME'], '*', '#')).' */')
				: ( '/* '.addslashes(strtr($_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['REQUEST_URI'], '*', '#')) .' */');
		}

		$this->_query("SET NAMES '".$this->__cfg['charset']."'");
		self::$__instance[$md5] = $this->dbh;

		return true;
	}

	/**
	 * 强制选择Master连接
	 * 暂时弃用
	 * @param  void
	 * @return db_mo
	 */
	protected function _forceReadMaster() {

		$this->forceReadMaster = true;

		return $this;
	}

	/**
	 * 根据sql的 select 前缀判断选择读或写的连接
	 * 暂时弃用
	 * @param  string $sql
	 * @return void
	 */
	protected function _chooseDbConn($sql) {

		/**
		if (empty($sql)) {
			return false;
		}
		if ($this->dbh !== $this->master) {
			$this->dbh =& $this->master;
		}
		if ('SELECT' == strtoupper(strtok($sql, ' ')) && false == $this->forceReadMaster) {
			if (!empty($this->slave)) {
				$this->dbh =& $this->slave;
			} else {
				//trigger_error('Server is using master server to operate, because of that the slave server does not exist.'
				//   , E_USER_WARNING);
			}
		} elseif (empty($this->master)) {
			throw new ZOL_Exception('Dose not exist instance of DB of master server!');
		}

		return true;
		*/
	}

	/**
	 * 执行数据库查询
	 *
	 * @param  string $sql
	 * @return object  $query   pdo query 对像
	 */
	protected function _query($sql = '') {

		$this->_chooseDbConn($sql);
		$sql .= $this->sqlComment;
		$query = $this->dbh->query($sql, PDO::FETCH_ASSOC);
		if (empty($query)) {
			$error = $this->_errorInfo();
			trigger_error($error[2].' sql:'.$sql, E_USER_WARNING);
		}

		if (!empty($this->forceReadMaster)) {
			$this->forceReadMaster = false;
		}

		return $query;
	}

	/**
	 * 根据 sql 读取单条记录
	 * @param string $sql sql 语句
	 * @return Ambigous <NULL, string>
	 */
	protected function _getOne($sql) {

		$query = $this->_query($sql);

		return ($query instanceof PDOStatement) ? $query->fetchColumn() : null;
	}

	/**
	 * 根据 sql 读取数据
	 * @param string $sql sql 语句
	 * @param int $fetchStyle
	 * @return Ambigous <NULL, mixed>
	 */
	protected function _getRow($sql, $fetchStyle = PDO::FETCH_ASSOC) {

		$query = $this->_query($sql);
		$row = ($query instanceof PDOStatement) ? $query->fetch($fetchStyle) : null;

		return $row;
	}

	/**
	 * 根据 sql 读取数据
	 * @param string $sql sql 语句
	 * @param int $fetchStyle
	 * @return Ambigous <NULL, multitype:>
	 */
	protected function _getAll($sql, $fetchStyle = PDO::FETCH_ASSOC) {

		$query = $this->_query($sql);
		$result = ($query instanceof PDOStatement) ? $query->fetchAll($fetchStyle) : null;

		return $result;
	}

	// 开始事务
	public function beginTransaction() {

		return ($this->dbh instanceof PDO) ? $this->dbh->beginTransaction() : false;
	}

	// 提交
	public function commit() {

		return ($this->dbh instanceof PDO) ? $this->dbh->commit() : false;
	}

	// 错误号
	protected function _errorCode() {

		return ($this->dbh instanceof PDO) ? $this->dbh->errorCode() : false;
	}

	// 错误详情
	protected function _errorInfo() {

		return ($this->dbh instanceof PDO) ? $this->dbh->errorInfo() : false;
	}

	// 执行
	protected function _exec($statement = '') {

		$this->_chooseDbConn($statement);
		$ret = ($this->dbh instanceof PDO) ? $this->dbh->exec($statement) : false;
		$this->forceReadMaster = false;

		return $ret;
	}

	// 最后入库id
	protected function _lastInsertId() {

		return ($this->dbh instanceof PDO) ? $this->dbh->lastInsertId() : false;
	}

	/**
	 * 预处理
	 * @param string $statement
	 * @param array $options
	 * @return Ambigous <boolean, PDOStatement>
	 */
	protected function _prepare($statement = '', array $options = array()) {

		$this->_chooseDbConn($statement);
		$ret = ($this->dbh instanceof PDO) ? $this->dbh->prepare($statement, $options) : false;
		if (true == $this->forceReadMaster) {
			$this->forceReadMaster = false;
		}

		return $ret;
	}

	// 字串转义
	protected function _quote($string, $parameterType = PDO::PARAM_STR) {

		return ($this->dbh instanceof PDO) ? $this->dbh->quote($string, $parameterType) : false;
	}

	// 回滚
	public function rollBack() {

		return ($this->dbh instanceof PDO) ? $this->dbh->rollBack() : false;
	}

	/**
	 * 设置连接属性
	 * @param string $attribute
	 * @param mixed $value
	 * @return boolean
	 */
	protected function _setAttribute($attribute, $value) {

		return ($this->dbh instanceof PDO) ? $this->dbh->setAttribute($attribute, $value) : false;
	}

	// 获取有效的 pdo 引擎
	protected function _getAvailableDrivers() {

		return ($this->dbh instanceof PDO) ? $this->dbh->getAvailableDrivers() : false;
	}

	/**
	 * 获取连接属性
	 * @param string $attribute
	 * @return boolean
	 */
	protected function _getAttribute($attribute) {

		return ($this->dbh instanceof PDO) ? $this->dbh->getAttribute($attribute) : false;
	}
}
