<?php
/**
 * CommonPluginGroupModel.class.php
 * $author$
 */

namespace Common\Model;
use Common\Model\AbstractModel;

class CommonPluginGroupModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'cpg_';
	}

}
