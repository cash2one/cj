<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/29
 * Time: 下午3:27
 */

namespace OaRpc\Controller\Rpc;

class EnterpriseAppsetController extends AbstractController {

	protected $_appset = null;

	public function _initialize() {

		$this->_appset = D('EnterpriseAppset', 'Service');
	}

	/**
	 * @return 获取所有记录
	 */
	public function get_appset() {

		return $this->_appset->list_all();
	}

}