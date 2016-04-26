<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/23
 * Time: 下午10:01
 */

namespace Common\Service;

class MemberSettingService extends AbstractSettingService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/MemberSetting");
	}

}
