<?php
/**
 * WeixinSettingModel.class.php
 * $author$
 */

namespace Common\Model;
use Common\Model\AbstractSettingModel;

class WeixinSettingModel extends AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'ws_';
	}

}
