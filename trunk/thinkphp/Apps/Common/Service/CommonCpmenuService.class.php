<?php
/**
 * CommonCpmenuService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractService;

class CommonCpmenuService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/CommonCpmenu');
	}

}
