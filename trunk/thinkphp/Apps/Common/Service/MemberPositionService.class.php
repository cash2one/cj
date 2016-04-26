<?php
/**
 * MemberPositionService.class.php
 * $author$
 */
namespace Common\Service;

use Common\Service\AbstractService;

class MemberPositionService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/MemberPosition');
	}

}
