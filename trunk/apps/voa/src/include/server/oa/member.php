<?php
/**
 * voa_server_oa_member
 * 企业OA 用户相关内部接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_oa_member {

	private $_uda_member_update = null;

	public function __construct() {
		if (!voa_h_conf::init_db()) {
			throw new rpc_exception('config file is missing.', -1);
			return false;
		}
		$this->_uda_member_update = &uda::factory('voa_uda_frontend_member_update');
	}

	/**
	 * 修改密码方法
	 * @return boolean
	 */
	public function pwdmodify($params) {
		if (empty($params)) {
			return $this->_error(voa_errcode_rpc_member::OARPC_MEMBER_PWDRESET_PARAM_NULL);
		}
		foreach (array('password','account') as $k) {
			if (!isset($params[$k])) {
				return $this->_error(voa_errcode_rpc_member::OARPC_MEMBER_PWDRESET_PARAM_LOSE);
			}
		}

		$password = $params['password'];
		$account = $params['account'];

		$serv_member = &service::factory('voa_s_oa_member');

		if (validator::is_email($account)) {
			// 使用 email 进行修改密码
			$member = $serv_member->fetch_by_email($account);
		} elseif (validator::is_mobile($account)) {
			// 使用手机号进行修改密码
			$member = $serv_member->fetch_by_mobilephone($account);
		} else {
			// 未知的帐号类型
			return $this->_error(voa_errcode_rpc_member::OARPC_MEMBER_PWDRESET_ACCOUNT_UNKNOW);
		}

		if (empty($member)) {
			return $this->_error(voa_errcode_rpc_member::OARPC_MEMBER_PWDRESET_ACCOUNT_NOT_EXISTS);
		}

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
		if (!$uda_member_update->pwd_modify($member['m_uid'], $password, false)) {
			throw new rpc_exception($uda_member_update->error, $uda_member_update->errno);
			return false;
		}

		return true;
	}

	private function _error($error_const_string) {
		if (preg_match('/^\s*(\d+)\s*\:\s*(.+)$/', $error_const_string, $match)) {
			// 分离 错误代码 和 错误消息
			$errcode = (int)$match[1];
			$errmsg = (string)$match[2];
		} else {
			$errcode = 405;
			$errmsg = 'error';
		}
		throw new rpc_exception($errmsg, $errcode);
	}

}
