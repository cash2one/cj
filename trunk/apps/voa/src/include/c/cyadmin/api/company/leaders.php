<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/29
 * Time: 下午10:34
 */

class voa_c_cyadmin_api_company_leaders extends voa_c_cyadmin_api_base {

	/**
	 * 一次添加多个企业的负责人
	 * @return bool
	 */
	public function execute() {

		// 获取数据
		$post = $this->request->postx();

		// 数据过滤
		$uda = &uda::factory('voa_uda_cyadmin_company_leader');
		$error = array();
		if (!$uda->leaders_filter($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];

			return false;
		}

		// 判断权限, 然后更新
		if (!$uda->leaders_authority_and_update($post, $error)) {
			$this->_errcode = $error['errcode'];
			$this->_errmsg = $error['errmsg'];

			return false;
		}

		// 提示语句
		$this->_errmsg = $error['errmsg'];

		return true;
	}

}
