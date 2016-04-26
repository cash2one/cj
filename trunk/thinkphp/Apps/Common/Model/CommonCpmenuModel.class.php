<?php
/**
 * CommonCpmenuModel.class.php
 * $author$
 */

namespace Common\Model;
use Common\Model\AbstractModel;

class CommonCpmenuModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'ccm_';
	}

}
