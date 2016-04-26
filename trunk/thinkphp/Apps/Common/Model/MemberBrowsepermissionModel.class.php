<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/20
 * Time: 下午9:08
 */

namespace Common\Model;

use Common\Model\AbstractModel;

class MemberBrowsepermissionModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'mb_';
	}

}
