<?php
/**
 * NewsTemplatesService.class.php
 * $author$
 */

namespace Common\Service;

class NewsTemplatesService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/NewsTemplates");
	}

}
