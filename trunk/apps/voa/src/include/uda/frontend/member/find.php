<?php
/**
 * find.php
 * 根据帐号信息（微信、手机号、邮箱地址之一或者多个）找到匹配的人
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_member_find extends voa_uda_frontend_member_base {

	/** 请求的参数 */
	private $__request = array();
	/** 结果集合 */
	private $__result = array();
	/** 其他配置参数 */
	private $__options = array();

	/**
	 * 根据帐号信息找到匹配的人员列表
	 * @param array $request
	 * + mobile 手机号(非必填)
	 * + email 邮箱地址(非必填)
	 * + weixinid 微信号(非必填)
	 * + username 真实姓名（非必填）
	 * @param array $result
	 * array(uid, uid, ....)
	 * @param array $options
	 * @return boolean
	 */
	public function doit(array $request, array &$result, array $options = array()) {

		$this->__options = $options;
		// 定义字段
		$fields = array(
			'weixinid' => array('weixinid', parent::VAR_ARR, array(), null, true),
			'mobile' => array('mobile', parent::VAR_ARR, array(), null, true),
			'email' => array('email', parent::VAR_ARR, array(), null, true),
			'username' => array('username', parent::VAR_ARR, array(), null, true)
		);

		if (!$this->extract_field($this->__request, $fields, $request)) {
			throw new service_exception($this->errmsg, $this->errcode);
			return false;
		}

		$s_member_field = &service::factory('voa_s_oa_member_field');
		$s_member = &service::factory('voa_s_oa_member');

		$uids = array();
		// 寻找与微信号匹配的uid
		if (isset($this->__request['weixinid'])) {
			$uids = $s_member_field->fetch_all_uid_by_weixinid($this->__request['weixinid']);
		}
		// 寻找与手机号匹配的uid
		if (isset($this->__request['mobile'])) {
			// 找到手机号码
			$mobile_list = array();
			foreach ($this->__request['mobile'] as $_mobile) {
				if (validator::is_mobile($_mobile)) {
					$mobile_list[] = $_mobile;
				}
			}
			if ($mobile_list) {
				foreach ($s_member->fetch_all_uid_by_account($mobile_list, 'mobilephone') as $_uid) {
					if (!in_array($_uid, $uids)) {
						$uids[] = $_uid;
					}
				}
			}
		}
		// 寻找与邮箱匹配的uid
		if (isset($this->__request['email'])) {
			// 找到email地址格式
			$email_list = array();
			foreach ($this->__request['email'] as $_email) {
				if (validator::is_email($_email)) {
					$email_list[] = $_email;
				}
			}
			if ($email_list) {
				foreach ($s_member->fetch_all_uid_by_account($email_list, 'email') as $_uid) {
					if (!in_array($_uid, $uids)) {
						$uids[] = $_uid;
					}
				}
			}
		}

		// 寻找与名字匹配的uid
		if (isset($this->__request['username'])) {
			$_u = $s_member->fetch_by_username($this->__request['username']);
			if (!empty($_u) && !in_array($_u['m_uid'], $uids)) {
				$uids[] = $_u['m_uid'];
			}
		}

		// 返回所有匹配的uid
		$result = $uids;

		return true;
	}

}
