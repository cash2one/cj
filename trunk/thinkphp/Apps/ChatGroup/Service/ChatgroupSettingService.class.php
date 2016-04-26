<?php
/**
 * ChatgroupSettingService.class.php
 * $author$
 */

namespace ChatGroup\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class ChatgroupSettingService extends AbstractSettingService {

	// 插件名称
	const PLUGIN_NAME = 'chatgroup';

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("ChatGroup/ChatgroupSetting");
	}

}
