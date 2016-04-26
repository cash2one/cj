<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/22
 * Time: 下午4:41
 */

namespace Common\Service;

use Common\Service\AbstractService;

class MemberShareService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/MemberShare');
	}

}
