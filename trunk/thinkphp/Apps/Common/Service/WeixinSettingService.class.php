<?php
/**
 * WeixinSettingService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractSettingService;

class WeixinSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/WeixinSetting');
	}

}
