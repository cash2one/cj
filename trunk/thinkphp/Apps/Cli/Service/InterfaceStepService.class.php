<?php
/**
 * InterfaceStepService.class.php
 * $author$
 */

namespace Cli\Service;

class InterfaceStepService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Cli/InterfaceStep");
	}
}
