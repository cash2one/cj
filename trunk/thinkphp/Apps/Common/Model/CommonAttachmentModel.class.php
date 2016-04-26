<?php
/**
 * CommonAttachmentModel.class.php
 * $author$
 */
namespace Common\Model;

class CommonAttachmentModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'at_';
	}

}
