<?php
/**
 * Created by PhpStorm.
 * 停止 开启 服务
 * User: zhoutao
 * Date: 15/10/20
 * Time: 下午4:03
 */

class voa_c_cyadmin_api_company_stop extends voa_c_cyadmin_api_base {

	public function execute() {

		// 获取数据
		$post = $this->request->postx();
		$uda = &uda::factory('voa_uda_cyadmin_company_stop');

		// 验证数据
		$error = array();
		if (!$uda->filter($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];
			return false;
		};

		// 更新数据
		$result = array();
		if (!$uda->stop_or_start($post, $result)) {
			$this->_errcode = $result['errcode'];
			$this->_errmsg = $result['errmsg'];
			return false;
		};

		$this->_errcode = 0;
		$this->_errmsg = '切换成功';
		return true;
	}



}
