<?php
/**
 * JobtrainService.php
 * 培训设置表
 * @author: anything
 * @createTime: 2015/11/19 10:23
 * @version: $Id$
 * @copyright: 畅移信息
 */

namespace Jobtrain\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class JobtrainSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Jobtrain/JobtrainSetting');
	}

}
