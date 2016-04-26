<?php

/**
 * Class voa_c_api_xdf_get_uinfo
 * 接口/新东方/ PC/H5共用的用户信息获取API【redmine:#1242】
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */
class voa_c_api_xdf_get_userinfo extends voa_c_api_xdf_base {

	public function execute() {

		//签名合法性验证
		if (!$this->_validate_sig()) {
			$this->_set_errcode('102:invalid request address');
			return false;
		}

		//获取openid
		$openid = $this->request->get('openid');

		//根据openid获取用户信息
		$s_member = new voa_s_oa_member();
		$user_info = $s_member->fetch_by_openid($openid);
		unset($user_info['m_password'], $user_info['m_salt'], $user_info['m_status'], $user_info['m_created'], $user_info['m_updated'], $user_info['m_deleted']);

		//返回用户信息
		$this->_result = $user_info;
		return true;
	}
}
