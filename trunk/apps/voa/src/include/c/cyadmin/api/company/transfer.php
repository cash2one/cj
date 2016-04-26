<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/14
 * Time: 下午11:44
 */

class voa_c_cyadmin_api_company_transfer extends voa_c_cyadmin_api_base {

	public function execute() {

		$post = $this->request->postx();

		$uda = &uda::factory('voa_uda_cyadmin_company_transfer');

		// 验证数据
		$error = array();
		if (!$uda->filter($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];
			return false;
		};

		if (!$uda->update_data($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];
			return false;
		};

		$this->_errmsg = '操作成功!';

		return true;
	}



}
