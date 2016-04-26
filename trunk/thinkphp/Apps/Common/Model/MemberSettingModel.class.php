<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/23
 * Time: 下午10:02
 */

namespace Common\Model;

class MemberSettingModel extends AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'm_';
	}
}
