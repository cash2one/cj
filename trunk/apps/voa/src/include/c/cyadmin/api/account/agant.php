<?php
/**
 * voa_c_cyadmin_api_account_agant
 * 代理设置添加
 * Created by zhoutao.
 * Created Time: 2015/7/29  10:30
 */

class voa_c_cyadmin_api_account_agant extends voa_c_cyadmin_api_account_base {

	public function execute () {
		$postx = $this->request->postx();

		if (!empty($postx)) {
			$out = null;
			$postx['post_ip'] = $this->request->get_client_ip();
			$callback = $this->_uda_enterprise_add->agant_setting($postx, $out);
			if (!empty($callback['errmsg']) && !empty($callback['errcode'])) {
				$this->_errcode = $callback['errcode'];
				$this->_errmsg = $callback['errmsg'];
				return false;
			}
		}

		$this->_result = array(
			'添加成功！'
		);
		return true;
	}

}
