<?php
/**
 * member.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_member extends voa_uda_uc_base {

	public $serv_member = null;
	public $serv_memberfield = null;

	public function __construct() {
		parent::__construct();
		if ($this->serv_member === null) {
			$this->serv_member = &service::factory('voa_s_uc_member');
			$this->serv_memberfield = &service::factory('voa_s_uc_memberfield');
		}
	}

	/**
	 * 新增一个uc用户
	 * @param array $data 用户注册信息
	 * + mobilephone
	 * + email
	 * + realname
	 * + wechatunionid
	 * + qqopenid
	 * + password (md5)
	 * @param array $member <strong style="color:red">(引用结果)</strong>用户信息
	 * @return boolean
	 */
	public function new_member($data = array(), &$member = array()) {

		// 生成加密后的密码以及密码盐值
		list($password, $salt) = voa_h_func::generate_password($password, '', false, 6);

		// 用户主表信息
		$member = array(
			'm_mobilephone' => $data['mobilephone'],
			'm_email' => $data['email'],
			'm_realname' => $data['m_realname'],
			'm_wechatunionid' => isset($data['wechatunionid']) ? $data['wechatunionid'] : '',
			'm_qqopenid' => isset($data['qqopenid']) ? $data['qqopenid'] : '',
			'm_password' => $password,
			'm_salt' => $salt
		);

		$front = controller_front::get_instance();
		$client_ip = $front->get_request()->get_client_ip();

		// 新增的用户m_id
		$m_id = 0;
		try {

			$this->serv_member->begin();
			$m_id = $this->serv_member->insert($member, true);
			$member['m_id'] = $m_id;

			// 用户扩展信息
			$memberfield = array(
				'm_id' => $m_id,
				'mf_regip' => $client_ip,
				'mf_regdate' => startup_env::get('timestamp'),
				'mf_lastloginip' => $client_ip,
				'mf_lastlogin' => startup_env::get('timestamp')
			);
			$this->serv_memberfield->insert($memberfield);

			$this->serv_memberfield->commit();

		} catch (Exception $e) {
			logger::error($e);
			return $this->set_errmsg(voa_errcode_uc_member::MEMBER_INSERT_FAILED);
		}

		return true;
	}

	/**
	 * 通过指定的m_id找到用户信息
	 * @param number $m_id 用户在uc的m_id
	 * @param array $member <strong style="color:red">(引用结果)</strong>用户信息
	 * @return boolean
	 */
	public function get_by_id($m_id = 0, &$member = array()) {
		$member = $this->serv_member->fetch($m_id);
		if (empty($member)) {
			return $this->set_errmsg(voa_errcode_uc_system::LOGIN_ID_NOT_EXISTS);
		}

		return true;
	}

	/**
	 * 通过普通登录帐号获取用户信息
	 * @param string $account 手机号或Email
	 * @param array $member <strong style="color:red">(引用结果)</strong>用户信息
	 * @return boolean
	 */
	public function get_by_account($account, &$member = array()) {

		if (validator::is_mobile($account)) {
			// 使用手机号
			$member = $this->serv_member->fetch_by_mobilephone($account);
			if (empty($member)) {
				return $this->set_errmsg(voa_errcode_uc_system::LOGIN_MOBILE_MEMBER_NULL);
			}
		} elseif (validator::is_email($account)) {
			// 使用邮箱
			$member = $this->serv_member->fetch_by_email($account);
			if (empty($member)) {
				return $this->set_errmsg(voa_errcode_uc_system::LOGIN_EMAIL_MEMBER_NULL);
			}
		} else {
			// 未知类型的帐号
			return $this->set_errmsg(voa_errcode_uc_system::LOGIN_ACCOUNT_UNKNOWN);
		}

		return true;
	}

	/**
	 * 更新用户扩展表
	 * @param number $m_id uc用户id
	 * @param array $data 待更新的字段与数据数组
	 * @return boolean
	 */
	public function update_field($m_id, $data) {
		if (empty($data)) {
			return true;
		}
		$this->serv_memberfield->update($data, $m_id);
		return true;
	}

	/**
	 * 验证手机号码的可用性
	 * @param string $mobile 手机号
	 * @param number $m_id 关联的用户m_id，用于检查重复使用情况，-1=不检查
	 * @return boolean
	 */
	public function validator_mobile(&$mobile, $m_id = -1) {
		$mobile = (string)$mobile;
		$mobile = trim($mobile);
		if (!validator::is_mobile($mobile)) {
			return $this->set_errmsg(voa_errcode_uc_member::MOBILE_ERROR);
		}
		if ($m_id >= 0 && $this->serv_member->count_by_mobilephone_not_id($mobile, $m_id) > 0) {
			// 要求检查手机号码是否已被使用，且已有其他用户使用此号码
			return $this->set_errmsg(voa_errcode_uc_member::MOBILE_USED);
		}

		return true;
	}

	/**
	 * 验证邮箱地址的可用性
	 * @param string $email 邮箱地址
	 * @param number $m_id 关联的用户m_id，用于检查重复使用情况，-1=不检查
	 * @return boolean
	 */
	public function validator_email(&$email, $m_id = -1) {
		$email = (string)$email;
		$email = trim($email);
		$email = rstrtolower($email);
		if (!validator::is_email($email)) {
			return $this->set_errmsg(voa_errcode_uc_member::MOBILE_USED);
		}
		if ($m_id >= 0 && $this->serv_member->count_by_email_not_id($email, $m_id) > 0) {
			// 要求检查email，且已有其他用户使用此email
			return $this->set_errmsg(voa_errcode_uc_member::EMAIL_USED);
		}

		return true;
	}

	/**
	 * 验证真实姓名
	 * @param string $realname 真实姓名
	 * @param number $m_id $m_id 关联的用户m_id，用于检查重复使用情况，-1=不检查
	 * @return boolean
	 */
	public function validator_realname(&$realname, $m_id = -1) {
		$realname = (string)$realname;
		$realname = trim($realname);
		if (!validator::is_realname($realname)) {
			return $this->set_errmsg(voa_errcode_uc_member::REALNAME_ERROR);
		}

		return true;
	}

}
