<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/28
 * Time: 下午7:05
 */

namespace Common\Model;

class CommonPluginGroupModel extends \Common\Model\AbstractModel {

	// 构造方法
	public function __construct() {

		$this->prefield = 'cpg_';
		parent::__construct();
	}
}
