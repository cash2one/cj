<?php
/**
 * AddressbookSettingModel.class.php
 * $author$
 */

namespace Addressbook\Model;

class AddressbookSettingModel extends \Common\Model\AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'abs_';
	}
}
