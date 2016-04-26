<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/20
 * Time: 下午9:02
 * 人员浏览权限表
 */

namespace Common\Service;

use Common\Service\AbstractService;

class MemberBrowsepermissionService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/MemberBrowsepermission');
	}


}
