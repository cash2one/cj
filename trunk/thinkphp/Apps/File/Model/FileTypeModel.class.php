<?php
/**
 * FileTypeModel.class.php
 * $author$
 */
namespace File\Model;

class FileTypeModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 字段前缀
		$this->prefield = 't_';
	}
}
