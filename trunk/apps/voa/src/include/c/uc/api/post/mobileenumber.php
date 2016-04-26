<?php
/**
 * mobileenumber.php
 * 官网普通登录手机号码获取公司信息控制层
 * Created by zhoutao.
 * Created Time: 2015/6/26  17:16
 */

class voa_c_uc_api_post_mobileenumber extends voa_c_uc_api_base {

	public function execute() {
		$data = array(
			'mobilephone' => (string)$this->_params['account'],
		);

		$uda = &uda::factory('voa_uda_uc_login_enterpriseadminer');

		$list = null;
		// 根据手机号码，获取域名列表
		$uda->mobile($data, $list);

		if (!empty($list)) {
			$this->result = rjson_encode($list);
		} else {
			$this->errcode = '1001';
			$this->errmsg = '请输入正确的手机号';
		}
		return true;
	}
}
