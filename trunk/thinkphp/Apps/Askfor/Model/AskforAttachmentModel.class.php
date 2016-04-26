<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/11
 * Time: 下午12:17
 */
namespace Askfor\Model;

class AskforAttachmentModel extends AbstractModel {

	const IMG_COUNT = 9; //图片最大限制数量

	//构造方法
	public function __construct() {

		$this->prefield = 'afat_';
		parent::__construct();
	}

}
