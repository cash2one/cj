<?php
/**
 * CommonSqlrecordService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Common\Login;

class CommonSqlrecordService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/CommonSqlrecord");
	}

	/**
	 * SQL 信息入库
	 * @param string $sql SQL 信息
	 * @param array $options 表达式
	 * @param boolean $replace 是否使用 REPLACE INTO
	 * @return boolean
	 */
	public function insert($sql = '', $options = array(), $replace = false) {

		// 获取已登录的用户信息
		$login = &Login::instance();
		// 待入库的数据
		$data = array(
			'uid' => empty($login->user) ? 0 : (int)$login->user['m_uid'],
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
