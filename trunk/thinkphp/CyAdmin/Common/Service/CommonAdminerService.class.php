<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/28
 * Time: 下午6:22
 */

namespace Common\Service;

class CommonAdminerService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/CommonAdminer");
	}

}
