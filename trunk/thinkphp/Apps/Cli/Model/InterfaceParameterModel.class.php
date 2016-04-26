<?php
/**
 * InterfaceParameterModel.class.php
 * $author$
 */

namespace Cli\Model;

class InterfaceParameterModel extends AbstractModel {

	// 数字
	const TYPE_INT = 1;
	// 字串
	const TYPE_STR = 2;
	// 数组
	const TYPE_ARR = 3;

	// 必填字段
	const REQUIRED_Y = 1;
	// 选填字段
	const REQUIRED_N = 0;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function get_type_int() {

		return self::TYPE_INT;
	}

	public function get_type_str() {

		return self::TYPE_STR;
	}

	public function get_type_arr() {

		return self::TYPE_ARR;
	}

	public function get_required_y() {

		return self::REQUIRED_Y;
	}

	public function get_required_n() {

		return self::REQUIRED_N;
	}

	/**
	 * 根据 n_id 读取接口参数
	 * @param array $nids 接口n_id信息
	 */
	public function list_by_nid($nids) {

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE n_id IN (?) AND status<?", array(
			(array)$nids, $this->get_st_delete()
		));
	}

}
