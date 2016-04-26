<?php
namespace Exam\Model;

class ExamSettingModel extends AbstractModel {

	// 数据类型: 数组
	const TYPE_ARRAY = 1;
	// 数据类型: 字串
	const TYPE_NORMAL = 0;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	// 获取数组类型标识
	public function get_type_array() {

		return self::TYPE_ARRAY;
	}

	// 获取字串类型标识
	public function get_type_normal() {

		return self::TYPE_NORMAL;
	}
}
