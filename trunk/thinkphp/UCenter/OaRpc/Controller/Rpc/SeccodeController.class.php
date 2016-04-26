<?php
/**
 * SeccodeController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;

class SeccodeController extends AbstractController {

	/**
	 * 生成验证码
	 * @return string
	 */
	public function generate_seccode($formhash) {

		$serv_code = D('Common/Seccode', 'Service');
		$code = '';
		if (!$serv_code->generate_seccode($formhash, $code)) {
			return '';
		}

		return $code;
	}

}
