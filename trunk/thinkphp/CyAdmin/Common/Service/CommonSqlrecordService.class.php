<?php
/**
 * CommonSqlrecordService.class.php
 * $author$
 */

namespace Common\Service;

class CommonSqlrecordService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/CommonSqlrecord");
	}

	/**
	 * SQL 信息入库
	 * @param string $sql SQL 信息
	 * @return boolean
	 */
	public function insert($sql) {

		// 待入库的数据
		$data = array(
			'uid' => 0,
			'uniqueid' => url_uniqueid(),
			'datetime' => rgmdate(NOW_TIME, 'Y-m-d H:i:s'),
			'url' => boardurl(),
			'get' => var_export($_GET, true),
			'post' => var_export($_POST, true),
			'sql' => $sql,
			'status' => $this->_d->get_st_create(),
			'created' => NOW_TIME,
			'updated' => NOW_TIME
		);
		// 入库
		$this->_d->insert($data);
		return true;
	}

	// 获取表格名称
	public function tname() {

		return $this->_d->get_tname();
	}
}
