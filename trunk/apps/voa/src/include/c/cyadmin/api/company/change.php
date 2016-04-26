<?php
/**
 * Created by PhpStorm.
 * 更改客户状态
 * User: zhoutao
 * Date: 15/10/19
 * Time: 下午1:19
 */

class voa_c_cyadmin_api_company_change extends voa_c_cyadmin_api_base {

	public function execute() {

		$post = $this->request->postx();
		$uda = &uda::factory('voa_uda_cyadmin_company_change');

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

		$this->_errcode = 0;
		$this->_errmsg = '更新成功';
		return true;
	}


}
