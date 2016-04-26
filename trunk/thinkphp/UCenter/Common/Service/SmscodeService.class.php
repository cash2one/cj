<?php
/**
 * SmscodeService.class.php
 * $author$
 */

namespace Common\Service;

class SmscodeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Smscode");
	}

}
