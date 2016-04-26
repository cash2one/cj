<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/11/12
 * Time: 上午11:43
 */

namespace Askfor\Model;

class AskforCommentModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'afc_';
	}

}
