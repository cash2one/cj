<?php
/**
 * CommonAdminerModel.class.php
 * $author$
 */

namespace Common\Model;

class CommonAdminerModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'ca_';
	}
}
