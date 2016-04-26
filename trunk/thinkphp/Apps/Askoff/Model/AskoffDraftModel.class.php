<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/4
 * Time: 下午5:15
 */

namespace Askoff\Model;

class AskoffDraftModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();

		$this->prefield = 'aod_';
	}

}