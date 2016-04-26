<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/4
 * Time: 下午5:15
 */

namespace Project\Model;

class ProjectDraftModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->prefield = 'pd_';
	}

}