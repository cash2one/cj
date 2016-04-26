<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/11
 * Time: 下午3:52
 */

namespace Askfor\Model;

class AskforCustomdataModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		$this->prefield = 'afcd_';
		parent::__construct();
	}

}
