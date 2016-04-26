<?php
/**
 * CommonSyscacheModel.class.php
 * $author$
 */

namespace Common\Model;

class CommonSyscacheModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'csc_';
	}
}
