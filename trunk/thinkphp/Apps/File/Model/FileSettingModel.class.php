<?php
/**
 * FileSettingModel.class.php
 * @create-time: 2015-07-01
 */
namespace File\Model;

class FileSettingModel extends \Common\Model\AbstractSettingModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 'is_';
	}
}
