<?php
/**
 * InterfaceFlowModel.class.php
 * $author$
 */

namespace Cli\Model;

class InterfaceFlowModel extends AbstractModel {

	// 未执行
	const EXEC_READY = 0;
	// 执行中
	const EXEC_RUNNING = 1;
	// 已执行
	const EXEC_COMPLETE = 2;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function get_exec_ready() {

		return self::EXEC_READY;
	}

	public function get_exec_running() {

		return self::EXEC_RUNNING;
	}

	public function get_exec_complete() {

		return self::EXEC_COMPLETE;
	}

	/**
	 * 读取未完成的接口测试流程
	 * @param array $execute 接口执行状态
	 */
	public function list_not_complete() {

		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `f_exec` IN (?) AND `status`<? ORDER BY f_exec DESC", array(
			array(self::EXEC_READY, self::EXEC_RUNNING), $this->get_st_delete()
		));
	}
}
