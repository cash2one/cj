<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/11
 * Time: 下午3:51
 */

namespace Askfor\Service;

class AskforCustomdataService extends AbstractService {
	//构造方法
	public function __construct() {
		$this->_d = D("Askfor/AskforCustomdata");
		parent::__construct();
	}
}
