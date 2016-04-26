<?php
/**
 * voa_c_uc_api_post_checkmobile
 * 判断手机号是否存在
 * Created by zhoutao.
 * Created Time: 2015/7/10  17:36
 */

class voa_c_uc_api_post_checkmobile extends voa_c_uc_api_base {

	/** 企业uda */
	protected $_uda_enterprise = null;

	public function execute() {

		$this->_uda_enterprise = &uda::factory('voa_uda_uc_enterprise');

		if (!$this->_uda_enterprise->check_enterprise_mobilephone($this->_params['mobilephone'], 0)) {
			$this->errcode = $this->_uda_enterprise->errcode;
			$this->errmsg = $this->_uda_enterprise->errmsg;
			return false;
		}

		$this->result = '验证成功！';
		return true;
	}

}
