<?php
/**
 * voa_c_cyadmin_api_account_add
 * 添加代理
 * Created by zhoutao.
 * Created Time: 2015/7/28  15:50
 */

class voa_c_cyadmin_api_account_add extends voa_c_cyadmin_api_account_base {

	public function execute() {
		$postx = $this->request->postx();

		if (!empty($postx)) {
			$out = '';
			$session = '';
			$postx['post_ip'] = $this->request->get_client_ip();
			$callback = $this->_uda_enterprise_add->add($postx, $out, $session);
			if (!empty($callback['errmsg']) && !empty($callback['errcode'])) {
				$this->_errcode = $callback['errcode'];
				$this->_errmsg = $callback['errmsg'];
				return false;
			}
		} else {
			$this->_errcode = 19999;
			$this->_errmsg = '提交数据不能为空';
			return false;
		}

		$this->_result = array(
			'添加成功！'
		);
		return true;
	}




}
