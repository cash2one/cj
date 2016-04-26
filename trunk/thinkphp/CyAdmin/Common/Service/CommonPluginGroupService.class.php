<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/28
 * Time: 下午7:02
 */

namespace Common\Service;

class CommonPluginGroupService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/CommonGroup");
	}

}
