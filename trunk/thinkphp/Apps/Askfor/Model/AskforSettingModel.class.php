<?php
/**
 * GuestbookSettingModel.class.php
 * $author$
 */
namespace Askfor\Model;

class AskforSettingModel extends \Common\Model\AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'afs_';
	}
}
