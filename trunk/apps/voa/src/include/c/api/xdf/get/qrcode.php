<?php
/**
 * Class voa_c_api_xdf_get_qrcode
 * 接口/新东方/获取二维码的URL【redmine:#1240】
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_c_api_xdf_get_qrcode extends voa_c_api_xdf_base {

	public function execute() {

		//签名合法性验证
		if (!$this->_validate_sig()) {
			$this->_set_errcode('100:invalid request address');

			return false;
		}

		//生成code
		$scode = voa_h_login::code_create();

		//二维码url
		$qrcode_url_base = $this->qrcode_url_base;
		$url = $qrcode_url_base."?scode=".$scode;

		//返回二维码地址、scode
		$this->_result = array('scodeurl' => $url, 'scode' => $scode);

		return true;
	}
}
