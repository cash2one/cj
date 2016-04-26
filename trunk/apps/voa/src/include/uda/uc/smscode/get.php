<?php
/**
 * voa_uda_uc_smscode_get
 * 统一数据访问/smscode
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_smscode_get extends voa_uda_uc_smscode_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 校验用户提交的手机短信验证码是否有效
	 * @param string $mobilephone 手机号
	 * @param string $submit_smscode 用户提交的验证码文字
	 * @param number $set_expire_second 短信验证码有效期，注意与 voa_uda_uc_smscode_insert->send() 方法内的$set_expire_second值保持一致
	 * 如果不设置，则使用系统全局的设置  config::get('voa.smscode_send_expire')
	 * @return boolean
	 */
	public function validator($mobilephone, $submit_smscode, $set_expire_second = 0) {

		// 检查手机号码合法性
		if (!validator::is_mobile($mobilephone)) {
			return $this->set_errmsg(voa_errcode_uc_register::REGISTER_MOBILE_ERROR);
		}

		// 尝试将全角中文数字转换为半角，增加用户体验
		$submit_smscode = str_replace(array('０', '１', '２', '３', '４', '５', '６', '７', '８', '９'),
				array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9), $submit_smscode);
		if (strlen($submit_smscode) != 6 || !preg_match('/^\d+$/', $submit_smscode)) {
			return $this->set_errmsg(voa_errcode_uc_register::REGISTER_SMSCODE_FORMAT_ERROR);
		}

		// 找到该手机号的最后一次验证码
		$serv_smscode = &service::factory('voa_s_uc_smscode');
		$log = $serv_smscode->fetch_last_by_mobile($mobilephone);
		if (empty($log)) {
			return $this->set_errmsg(voa_errcode_uc_register::REGISTER_SMSCODE_NONE);
		}

		// 验证验证码有效期
		if (empty($set_expire_second)) {
			$set_expire_second = config::get('voa.smscode_send_expire');
		}
		if (startup_env::get('timestamp') - $log['smscode_created'] > $set_expire_second) {
			return $this->set_errmsg(voa_errcode_uc_register::REGISTER_SMSCODE_TIMEOUT);
		}

		// 检查手机验证码是否有效
		if ($log['smscode_code'] != $submit_smscode) {
			// 验证无效
			return $this->set_errmsg(voa_errcode_uc_register::REGISTER_SMSCODE_ERROR);
		}

		// 标记该验证码已被使用
		$serv_smscode->set_used_by_smscode_id($log['smscode_id']);

		return true;
	}

}
