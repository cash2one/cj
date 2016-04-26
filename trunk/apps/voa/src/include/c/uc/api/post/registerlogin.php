<?php
/**
 * voa_c_uc_api_post_registerlogin
 * 完善注册，当手机号已经被使用时，直接登录
 * Created by zhoutao.
 * Created Time: 2015/7/11  15:22
 */

class voa_c_uc_api_post_registerlogin extends voa_c_uc_api_base {

	/** 企业手机号和信息关联表 */
	protected $_uda_enterprise = null;

	public function execute() {

		// 根据手机号，这里用的快速登录的UDA方法获取公司信息
		$this->_uda_enterprise = &uda::factory('voa_uda_uc_login_enterpriseadminer');
		$postx = $this->request->postx();
		$data = array(
			'mobilephone' => (string)$postx['mobilephone'],
		);
		$list = array();
		$this->_uda_enterprise->mobile($data, $list);

		$this->result = $list[0]['ep_domain'];
		return true;
	}
}
