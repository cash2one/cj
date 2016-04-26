<?php
/**
 * Class voa_c_api_xdf_get_uinfo
 * 接口/新东方/ PC/H5共用的用户信息获取API【redmine:#1242】
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_api_xdf_get_uinfo extends voa_c_api_xdf_base {

	public function execute() {

		//签名合法性验证
		if (!$this->_validate_sig()) {
			$this->_set_errcode('102:invalid request address');
			return false;
		}

		//获取code验证码
		$scode = $this->request->get('scode');

		//获取登录记录
		$ser_sig = &service::factory('voa_s_oa_common_signature');
		$info = $ser_sig->fetch_by_code($scode);

		//查无登录记录
		if (empty($info)) {
			//返回错误提示
			return $this->_set_errcode('101:您还没有登录.');
		}

		//登录状态
		$sig_login_status = $info['sig_login_status'];

		//登录时间
		$sig_login_time = $info['sig_login_time'];

		//登录超过两分钟
		if (!($sig_login_status && startup_env::get('timestamp') - $sig_login_time < 120)) {
			return $this->_set_errcode('104:login fail');
		}

		//登录成功,删除记录
		$ser_sig->update(array('sig_status' => 3, 'sig_deleted' => startup_env::get('timestamp')), array('sig_id' => $info['sig_id']));

		//用户id
		$m_uid = $info['sig_m_uid'];

		//根据用户id获取用户信息
		$s_member = new voa_s_oa_member();
		$user_info = $s_member->fetch_by_uid($m_uid);
		unset($user_info['m_password'], $user_info['m_salt'], $user_info['m_status'], $user_info['m_created'], $user_info['m_updated'], $user_info['m_deleted']);

		//返回用户信息
		$this->_result = $user_info;
		return true;
	}
}
