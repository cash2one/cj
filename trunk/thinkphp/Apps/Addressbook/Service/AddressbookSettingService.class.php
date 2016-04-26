<?php
/**
 * AddressbookSettingService.class.php
 * $author$
 */
namespace Addressbook\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class AddressbookSettingService extends AbstractSettingService {

	// 插件名称
	const PLUGIN_NAME = 'addressbook';

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Addressbook/AddressbookSetting');
	}


}
