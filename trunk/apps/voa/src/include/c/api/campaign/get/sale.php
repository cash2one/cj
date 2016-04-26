<?php
/**
 * voa_c_api_campaign_get_sale
 * User: Muzhitao
 * Date: 2015/8/31 0031
 * Time: 14:23
 */

class voa_c_api_campaign_get_sale extends voa_c_api_campaign_base {

	public function execute() {

		$effect = (float)($this->request->get('effect'));

		try {
			$total = '';
			$uda = &uda::factory('voa_uda_frontend_campaign_total');

			// 获取数据
			$total = $uda->get_effect_total($effect);
		} catch (help_exception $h) {
			$this->_errcode = $h->getCode();
			$this->_errmsg = $h->getMessage();
		} catch (Exception $e) {
			logger::error($e);

			return $this->_api_system_message($e);
		}

		// 返回数据
		$this->_result = array('total' => $total);

		return true;
	}
}

// end