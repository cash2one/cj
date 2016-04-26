<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/22
 * Time: 下午4:43
 */

namespace Common\Model;

use Common\Model\AbstractModel;

class MemberShareModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'msh_';
	}

}
