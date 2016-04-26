<?php
/**
 * GuestbookService.class.php
 * $author$
 */

namespace Guestbook\Service;

class GuestbookService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Guestbook/Guestbook");
	}

	/**
	 * 新增留言操作
	 * @param array &$guestbook 留言信息
	 * @param array $params 传入的参数
	 * @param mixed $extend 扩展参数
	 */
	public function add(&$guestbook, $params, $extend = array()) {

		// 获取入库参数
		$uid = (int)$extend['uid'];
		$username = (string)$extend['username'];
		$message = (string)$params['message'];

		// 用户信息和留言信息不能为空
		if (empty($uid) || empty($username) || empty($message)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 留言信息
		$guestbook = array(
			'uid' => $uid,
			'username' => $username,
			'message' => $message,
			'status' => $this->_d->get_st_create(),
			'created' => NOW_TIME,
			'updated' => NOW_TIME
		);
		// 执行入库操作
		if (!$id = $this->_d->insert($guestbook)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		$guestbook['id'] = $id;
		return true;
	}

}
