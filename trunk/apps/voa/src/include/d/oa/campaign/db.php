<?php
/**
 * 数据库操作类,有些情景下sql语句较为复杂,很难用封装的方式进行操作数据库,稳定性也不行,学习成本也高,不如直接用sql
 * Create By linshiling
 * 使用方式
 * $db = new voa_d_oa_campaign_db();
 * $sql = "SELECT * FROM {campaign} WHERE (is_all = 1 OR id in(7)) AND status < 3 AND overtime > 1427082524 LIMIT 0, 10";
 * $list = $db->getAll($sql);
 *
 * 其中表名用花括号,不需带前辍
 *
 * !!!要注意的是用这种方式操作数据库,status,created,updated,deleted这些字段需要自己处理
 */
class voa_d_oa_campaign_db extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct() {

		/**
		 * 表名
		 */
		$this->_table = 'orm_oa.campaign'; // 不写会报错,随便写个
		parent::__construct();
	}

	// 替换为实际表名
	private function get_table($sql) {

		return preg_replace('#\{(\w+)\}#', $this->__tablepre . "$1", $sql);
	}

	/**
	 * 根据 sql 读取二维数据
	 *
	 * @param string $sql sql 语句
	 */
	public function getAll($sql) {

		$query = $this->_query($this->get_table($sql));
		$result = $query->fetchAll();
		return $result;
	}

	/**
	 * 根据 sql 读取一条数据
	 *
	 * @param string $sql sql 语句
	 */
	public function getRow($sql) {

		$query = $this->_query($this->get_table($sql));
		$result = $query->fetch();
		return $result;
	}

	/**
	 * 根据 sql 读取一条数据的一个字段值
	 *
	 * @param string $sql sql 语句
	 */
	public function getOne($sql) {

		$query = $this->_query($this->get_table($sql));
		$result = $query->fetchColumn();
		return $result;
	}

	/**
	 * 根据 sql 读取多数数据的一个字段值,并串为一维索引数组
	 * @param string $sql sql 语句
	 * @example SELECT id FROM {campaign} WHERE uid = 3
	 * 			会返回array(1,34,54,76)这样的数据
	 */
	public function getCol($sql) {
		$query = $this->_query($this->get_table($sql));
		$result = $query->fetchAll();
		$temp = array();
		foreach ($result as $r)
		{
			$temp[] = current($r);
		}
		return $temp;
	}

	/**
	 * 获取映射数组
	 * @param string $sql sql 语句
	 * @example SELECT id,name FROM {member}
	 * 			会返回array(1=>'名称',2=>'名称2')这样的数据
	 */
	public function getMap($sql) {
		$query = $this->_query($this->get_table($sql));
		$result = $query->fetchAll();
		$temp = array();
		foreach ($result as $r)
		{
			$keys = array_keys($r);
			$k = current($r);
			$temp[$k] = $r[$keys[1]];
		}
		return $temp;
	}

}

