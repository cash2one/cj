<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:39
 */

namespace Common\Service;

class CommonDepartmentConnectService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Common/CommonDepartmentConnect");
		parent::__construct();
	}

}
