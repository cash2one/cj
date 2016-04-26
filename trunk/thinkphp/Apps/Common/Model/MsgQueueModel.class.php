<?php
/**
 * MsgQueueModel.class.php
 * $author$
 */

namespace Common\Model;

class MsgQueueModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->prefield = 'mq_';
	}
}
