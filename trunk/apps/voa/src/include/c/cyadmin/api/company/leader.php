<?php
/**
 * Created by PhpStorm.
 * 更改负责人接口
 * User: zhoutao
 * Date: 15/10/21
 * Time: 下午12:40
 */

class voa_c_cyadmin_api_company_leader extends voa_c_cyadmin_api_base {

	public function execute() {

		$post = $this->request->postx();
		$uda = &uda::factory('voa_uda_cyadmin_company_leader');

		// 验证数据
		$error = array();
		if (!$uda->filter($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];
			return false;
		};

		// 更新数据
		$error = array();
		if (!$uda->update_data($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];
			return false;
		};

		// 更新最后操作时间
		$uda = &uda::factory('voa_uda_cyadmin_enterprise_profile');
		$uda->add_last_operation($post['ep_id']);

		$this->_errcode = 0;
		$this->_errmsg = '更新成功';
		return true;
	}

}
