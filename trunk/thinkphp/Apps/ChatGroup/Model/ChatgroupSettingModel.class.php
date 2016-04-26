<?php
/**
 * ChatgroupSettingModel.class.php
 * $author$
 */

namespace ChatGroup\Model;

use Common\Model\AbstractSettingModel;

class ChatgroupSettingModel extends AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();

		// 字段前缀
		$this->prefield = 'cgs_';
	}
}
