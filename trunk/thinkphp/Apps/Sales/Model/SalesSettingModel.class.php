<?php
/**
 * SalesSettingModel.class.php
 * $author$
 */

namespace Sales\Model;

use Common\Model\AbstractSettingModel;

class SalesSettingModel extends AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();

		// 字段前缀
		$this->prefield = '';
	}
}
