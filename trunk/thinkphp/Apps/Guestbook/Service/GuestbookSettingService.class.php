<?php
/**
 * GuestbookSettingService.class.php
 * $author$
 */

namespace Guestbook\Service;
use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class GuestbookSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Guestbook/GuestbookSetting');
	}

}
