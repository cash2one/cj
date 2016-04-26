<?php
/**
 * password.php
 * 密码修改
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_addressbook_put_password extends voa_c_api_addressbook_base {

	public function execute() {

		// 接受的参数
		$fields = array(
			'pw' => array('type' => 'string', 'required' => true),
			'newpw' => array('type' => 'string', 'required' => true)
		);
		// 基本变量检查
		$this->_check_params($fields);

		if (empty($this->_member) || empty($this->_member['m_uid'])) {
			$this->_set_errcode(voa_errcode_api_addressbook::USER_NOT_LOGIN);
			return false;
		}

		if (!validator::is_md5($this->_params['pw'])) {
			$this->_set_errcode(voa_errcode_api_addressbook::PW_NOT_MD5);
			return false;
		}

		if (!validator::is_md5($this->_params['newpw'])) {
			$this->_set_errcode(voa_errcode_api_addressbook::NEWPW_NOT_MD5);
			return false;
		}

		if (rstrtolower($this->_params['pw']) == rstrtolower($this->_params['newpw'])) {
			$this->_set_errcode(voa_errcode_api_addressbook::PW_IS_SAME);
			return false;
		}

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
		if (!$uda_member_update->pwd_modify($this->_member['m_uid'], $this->_params['newpw'], false)) {
			$this->_errcode = $uda_member_update->errno;
			$this->_errmsg = $uda_member_update->error;
			return false;
		}

		return true;
	}

}
