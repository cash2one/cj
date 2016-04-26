<?php
/**
 * @Author: ppker
 * @Date:   2015-09-16 11:16:20
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-09-24 11:27:25
 * @Description 微信Js签名信息接口
 */

namespace PubApi\Controller\Api;

class WxjsController extends AbstractController {

	// 执行接口函数
	public function Signature_get() {

		// 接受到的参数
		$url = I('get.url', '', 'trim');
		if (empty($url)) {
			return $this->_set_error("_ERR_URL_IS_NOT_EXIST");
		}

		$Wxqy = &\Common\Common\Wxqy\Service::instance();
		$cfg = array();
		$jsapi = $Wxqy->jsapi_signature($cfg, $url);
		// 对返回的$cfg 进行格式化处理
		$jscfg = array();
		$Wxqy->jsapi_signature_format($jscfg, $cfg);
		if (empty($jscfg)) {
			E("_ERR_SING_GET_ERROR");
			return false;
		}

		$this->_result = $jscfg;
		return true;
	}

}
