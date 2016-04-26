<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Stat\Service;

class StatLoseService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Stat/StatLose');
	}

}