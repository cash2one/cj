<?php
/**
 * insert.php
 * 后台管理员 - 插入
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_adminer_insert extends voa_uda_frontend_adminer_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 将指定前台用户设置为管理员
	 * @param number $m_uid member表m_uid
	 * @param array $adminer 管理员信息
	 * @return boolean
	 */
	public function adminer_insert($m_uid = 0, $adminer = array()) {
		if (validator::is_md5($adminer['password'])) {
			list($password, $salt) = voa_h_func::generate_password($adminer['password'], null, false, 6);
		} else {
			list($password, $salt) = voa_h_func::generate_password($adminer['password'], null, true, 6);
		}

		$this->serv_common_adminer->inser(array(
			'm_uid' => $m_uid,
			'ca_username' => $adminer['ca_username'],
			'ca_password' => $password,
			'cag_id' => $adminer['cag_id'],
			'ca_locked' => $adminer['ca_locked'],
			'ca_salt' => $salt
		));
		return true;
	}

}
