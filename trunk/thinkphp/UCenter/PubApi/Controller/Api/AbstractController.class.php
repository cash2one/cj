<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace PubApi\Controller\Api;
use Com\ApiSig;

abstract class AbstractController extends \Common\Controller\Api\AbstractController {

	public function before_action($action = '') {

		return parent::before_action($action);
	}

	public function after_action($action = '') {

		return parent::after_action($action);
	}

	// 检查参数
	protected function _check_signature($action = '') {

		$filter = cfg('DEFAULT_FILTER');
		cfg('DEFAULT_FILTER', '');
		if ('post' == rstrtolower($this->_method)) {
			$params = I('post.');
		} else {
			$params = I('get.');
		}
		cfg('DEFAULT_FILTER', $filter);

		// 检查签名是否使用过
		$serv_sig = D('Common/ApiSignature', 'Service');
		$sig = $params['ts'] . $params['sig'];
		if ($serv_sig->get_by_conds(array('as_signature' => $sig))) {
			E('_ERR_SIGNATURE_EXPIRED');
			return false;
		} else {
			$serv_sig->insert(array(
				'as_signature' => $sig
			));
		}

		return ApiSig::instance()->check($params);
	}

}
