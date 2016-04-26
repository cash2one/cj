<?php
/**
 * SettingModel.class.php
 * $author$
 */

namespace Common\Model;

class SettingModel extends \Common\Model\AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cs_';
	}

}
