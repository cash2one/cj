<?php
/**
 * ApiSignatureModel.class.php
 * $author$
 */

namespace Common\Model;

class ApiSignatureModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'as_';
	}
}
