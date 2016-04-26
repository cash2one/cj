<?php
/**
 * salessetting.php
 * 销售设置接口
 * Created by zhoutao.
 * Created Time: 2015/7/31  14:59
 */

class voa_c_cyadmin_api_company_salessetting extends voa_c_cyadmin_api_base {

	public function execute() {

		$postx = $this->request->postx();
		if (empty($postx)) {
			$this->_errcode = 10000;
			$this->_errmsg = '没有提交数据';
			return false;
		}

		$uda = &uda::factory('voa_uda_cyadmin_company_salessetting');

		// 整理,验证数据,添加 更新入库
		$data = array();
		if (! $uda->take_sure($postx, $data)) {
			$this->_errcode = $data['errcode'];
			$this->_errmsg = $data['errmsg'];
			return false;
		}

		// 更新最后操作时间
		$uda = &uda::factory('voa_uda_cyadmin_enterprise_profile');
		$uda->add_last_operation($postx['ep_id']);

		$this->_errcode = 0;
		$this->_errmsg = '添加成功';
		return true;
	}

}
