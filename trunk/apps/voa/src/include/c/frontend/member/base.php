<?php
/**
 * voa_c_frontend_member_base
 * 企业用户相关基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_frontend_member_base extends voa_c_frontend_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	protected function _after_action($action) {
		if (!parent::_after_action($action)) {
			return false;
		}

		return true;
	}

	/**
	 * 解析外部传递的数据
	 * @param string $data
	 * @return Ambigous <multitype:, string, mixed>
	 */
	protected function _parse_data($data = '') {
		$crypt_xxtea = new crypt_xxtea(config::get('voa.auth_key'));
		$data = rbase64_decode($data);
		$data = $crypt_xxtea->decrypt($data);
		$data = json_decode($data, true);
		if (!is_array($data) || !isset($data['errcode'])) {
			$data['errcode'] = '-9999';
		}
		if (!isset($data['errmsg'])) {
			$data['errmsg'] = 'parse data error';
		}
		if (!isset($data['result'])) {
			$data['result'] = array();
		}
		return $data;
	}

	/**
	 * 处理普通帐号方式登录
	 * @param string $account 登录账号
	 * @param string $password 密码
	 * @param string $pwd_original 密码是否为原始字符串。false=是md5加密字符串，true=密码原始字符串
	 * @return array
	 */
	protected function _normal_login($account, $password, $pwd_original = false) {

		$serv_member = &service::factory('voa_s_oa_member');
		$serv_addressbook = &service::factory('voa_s_oa_common_addressbook');

		$member = array();

		// 判断账号类型
		if (validator::is_mobile($account)) { // 手机号码
			$member = $serv_member->fetch_by_mobilephone($account);
		} elseif (validator::is_email($account)) { // 邮箱地址
			$addressbook = $serv_addressbook->fetch_by_email($account);
			if (!empty($addressbook)) {
				$member = $serv_member->fetch_by_mobilephone($addressbook['cab_mobilephone']);
			}
		} else {
			$this->_error_message('未知的登录帐号类型');
			return false;
		}

		// 检查帐号基本状态
		if (empty($member)) { // 用户不存在
			$this->_error_message('登录帐号或密码错误');
			return false;
		} elseif ($member['m_status'] == voa_d_oa_member::STATUS_REMOVE) { // 用户被标记为删除状态
			$this->_error_message('登录帐号或密码错误');
			return false;
		}

		if ($pwd_original) {
			// 传进来的是密码原文
			$password = md5($password);
		}

		// 转换密码的md5值字符串为小写
		$password = rstrtolower($password);
		// 根据用户储存的散列值来计算给定的密码储存值
		list($submit_password) = voa_h_func::generate_password($password, $member['m_salt'], false);
		// 密码不正确
		if ($submit_password != $member['m_password']) {
			$this->_error_message('登录帐号或密码错误');
			return false;
		}
		$uid = $member['m_uid'];

		// 设置用户相关环境变量
		$skey = $this->_generate_skey($member['m_username'], $member['m_password']);
		$this->_set_user_env($member, $skey);

		return $member;
	}

	/**
	 * 获取用户配置信息
	 */
	public static function fetch_cache_member_setting() {

		$serv = &service::factory('voa_s_oa_member_setting');
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['m_type']) {
				$arr[$v['m_key']] = unserialize($v['m_value']);
			} else {
				$arr[$v['m_key']] = $v['m_value'];
			}
		}

		return $arr;
	}

    /**
     * 获取用户职务配置信息
     */
    public static function fetch_cache_member_positions() {
        $serv = &service::factory('voa_s_oa_member_position');
        return $serv->list_all(null, array('mp_parent_id' => 'ASC'));
    }

}
