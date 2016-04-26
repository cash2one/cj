<?php
/**
 * extended.php
 * 试用延长接口
 * Created by zhoutao.
 * Created Time: 2015/8/17  20:00
 */

class voa_c_cyadmin_api_company_extended extends voa_c_cyadmin_api_base {

	public function execute () {

		$post = $this->request->postx();

		// 获取企业信息
		$uda = &uda::factory('voa_uda_cyadmin_company_extended');
		$ep_data = array();
		if (!$uda->extended($post, $ep_data)) {
			$this->_errcode = $ep_data['errcode'];
			$this->_errmsg = $ep_data['errmsg'];

			return false;
		}

		$this->_errcode = 0;
		$this->_errmsg = '操作成功';
		return true;
	}

}
