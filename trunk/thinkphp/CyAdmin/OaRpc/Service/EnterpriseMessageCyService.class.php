<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/27
 * Time: 13:50
 */

namespace OaRpc\Service;

class EnterpriseMessageCyService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("EnterpriseMessage");
		parent::__construct();
	}


}