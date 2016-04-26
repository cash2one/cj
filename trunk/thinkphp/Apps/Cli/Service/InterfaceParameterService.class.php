<?php
/**
 * InterfaceParameterService.class.php
 * $author$
 */

namespace Cli\Service;

class InterfaceParameterService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Cli/InterfaceParameter");
	}
}
