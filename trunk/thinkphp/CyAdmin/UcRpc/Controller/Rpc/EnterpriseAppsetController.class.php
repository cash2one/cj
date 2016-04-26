<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/28
 * Time: 15:58
 */

namespace UcRpc\Controller\Rpc;

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