<?php
/**
 * FileSettingService.class.php
 * @create-time: 2015-07-01
 */
namespace File\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class FileSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('File/FileSetting');
	}

}
