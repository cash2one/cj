<?php
/**
 * voa_c_api_order_get_list
 * 地址接口
 * 1.返回本地默认地址(如果上次买过)
 * 2.返回微信共享收货地址的参数
 * $Author$	linshiling
 * $Id$
 */
class voa_c_api_order_get_address extends voa_c_api_order_abstract {

	public function execute() {

		try {
			$openid = $this->_member['openid'];
			// $openid = 'o06msuFDO7_xZOdSNAHZq6fe_zJ0';
			if (! $openid) {
				return $this->_set_errcode('无法获取openid');
			}

			// 获取本地上次购买的地址
			$dft_address = array();
			$rs = $this->uda->address($openid, $dft_address);
			if (! $rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}

			// 获取微信地址共享接口参数
			$wepay = &service::factory('voa_wepay_service');
			$params = array();
			$rs = $wepay->get_addr_params($params);
			if (! $rs) {
				return $this->_set_errcode($this->uda->errmsg);
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = array(
			'address' => $dft_address,
			'ads_params' => $params
		);

		return true;
	}
}
