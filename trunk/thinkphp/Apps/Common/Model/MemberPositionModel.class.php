<?php
/**
 * MemberPositionModel.class.php
 * $author$ zhubeihai
 */
namespace Common\Model;

use Common\Model\AbstractModel;

class MemberPositionModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'mp_';
	}

}
