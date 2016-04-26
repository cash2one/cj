<?php
/**
 * InterfaceStepModel.class.php
 * $author$
 */

namespace Cli\Model;

class InterfaceStepModel extends AbstractModel {

	// 未执行
	const EXECUTED_N = 0;
	// 已执行
	const EXECUTED_Y = 1;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function get_executed_n() {

		return self::EXECUTED_N;
	}

	public function get_executed_y() {

		return self::EXECUTED_Y;
	}

	/**
	 * 根据 fid 读取所有
	 * @param array $fids 测试流程ID
	 */
	public function list_by_fid($fids) {

		return $this->_m->fetch_array("SELECT `b`.*, `a`.`s_id`, `a`.`f_id`, `a`.`login_uid` FROM __TABLE__ AS `a`
			LEFT JOIN `oa_interface` AS `b`
			ON `a`.`n_id`=`b`.`n_id`
			WHERE `a`.`f_id` IN (?) AND `a`.`status`<? ORDER BY `a`.`s_order` ASC", array(
				(array)$fids, $this->get_st_delete()
			)
		);
	}

}
