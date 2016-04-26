<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/28
 * Time: 下午6:22
 */

namespace Common\Model;

class CommonAdminerModel extends \Common\Model\AbstractModel {

	// 构造方法
	public function __construct() {

		$this->prefield = 'ca_';
		parent::__construct();
	}
}
