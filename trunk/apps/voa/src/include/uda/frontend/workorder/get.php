<?php
/**
 * get.php
 * 派单 - 查询相关
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_get extends voa_uda_frontend_workorder_abstract {

	public function __construct() {
		parent::__construct();
	}



	/**
	 * 是否允许派单
	 * @param array $request 请求的参数
	 * + uid 当前操作人
	 * + is_admin 是否管理模式
	 * @return boolean
	 */
	public function allow_send($request) {

		// TODO 目前业务逻辑不需要对派单人做限制，允许所有人派单

		return true;
	}

	/**
	 * 获取指定人对工单可执行的操作
	 * @param array $uid 指定人员
	 * @param array $workorder 工单详情
	 * @param array $operator 收单人列表
	 * @param array $allow_actions (引用结果)可执行的操作列表
	 * @param boolean $is_admin 是否是管理模式请求
	 * @return array
	 */
	public function my_action($uid, $workorder, $operator_list, &$allow_actions, $is_admin = false) {

		/**
		 * 可操作的动作有如下，可见 voa_d_oa_workorder::ACTION_XX 的定义
		 * 具体的映射名关系可见 voa_uda_frontend_workorder_abstract->actions
		 * send 派发新工单
		 * refuse 拒绝接单
		 * confirm 确认接单
		 * complete 完成接单
		 * cancel 撤销派单
		 * mycancel 撤销接单
		 * 以下逻辑判断未必会存在全部状态，程序代码罗列所有状态判断，只为确保逻辑清晰以及避免遗漏
		 */

		// 管理模式
		if ($is_admin) {
			return $this->__action_by_adminer($allow_actions, $workorder, $operator_list);
		}

		// 派单人
		if ($uid == $workorder['uid']) {
			return $this->__action_by_sender($allow_actions, $workorder, $operator_list);
		}

		// 执行人
		if ($uid == $workorder['operator_uid']) {
			return $this->__action_by_operator($allow_actions, $workorder, $operator_list);
		}

		// 不在接收人列表范围
		if (!isset($operator_list[$uid])) {
			$allow_actions = array();
			return true;
		}

		// 其他接收人
		return $this->__action_by_other($allow_actions, $workorder, $operator_list, $operator_list[$uid]);
	}

	/**
	 * 获取派单人可执行的动作
	 * @param array $actions (引用结果)可执行的动作
	 * @param array $workorder 工单详情
	 * @param array $operator 收单人列表
	 * @return boolean
	 */
	protected function __action_by_sender(&$actions, $workorder, $operator_list) {

		// 可允许的动作
		$actions = array();
		switch ($workorder['wostate']) {
			case voa_d_oa_workorder::WOSTATE_WAIT://待执行
				$actions = array(voa_d_oa_workorder::ACTION_CANCEL);// 可撤销派单
				break;
			case voa_d_oa_workorder::WOSTATE_REFUSE://已拒绝
				$actions = array();// 可撤销派单
				break;
			case voa_d_oa_workorder::WOSTATE_CONFIRM://已确认
				$actions = array();// 可撤销派单
				break;
			case voa_d_oa_workorder::WOSTATE_COMPLETE://已完成
				$actions = array();
				break;
		}

		return true;
	}

	/**
	 * 获取管理模式可执行的动作
	 * @param array $actions (引用结果)可执行的动作
	 * @param array $workorder 工单详情
	 * @param array $operator 收单人列表
	 * @return boolean
	 */
	protected function __action_by_adminer(&$actions, $workorder, $operator_list) {

		// 可允许的动作
		$action = array();

		// TODO 目前按派单人权限进行分配，可根据业务需要具体设置
		$this->__action_by_sender($actions, $workorder, $operator_list);

		return true;
	}

	/**
	 * 接单人可执行的动作
	 * @param array $actions (引用结果)可执行的动作
	 * @param array $workorder 工单详情
	 * @param array $operator 收单人列表
	 * @return boolean
	 */
	protected function __action_by_operator(&$actions, $workorder, $operator_list) {

		// 可允许的动作
		$actions = array();
		switch ($workorder['wostate']) {
			case voa_d_oa_workorder::WOSTATE_WAIT://待执行
				$actions = array(voa_d_oa_workorder::ACTION_REFUSE, voa_d_oa_workorder::ACTION_CONFIRM);// 可拒绝、确认
				break;
			case voa_d_oa_workorder::WOSTATE_REFUSE://已拒绝
				$actions = array();
				break;
			case voa_d_oa_workorder::WOSTATE_CONFIRM://已确认
				$actions = array(voa_d_oa_workorder::ACTION_COMPLETE);// 可完成
				break;
			case voa_d_oa_workorder::WOSTATE_COMPLETE://已完成
				$actions = array();
				break;
		}

		return true;
	}

	/**
	 * 其他收单人（非接单人）可执行的动作
	 * @param array $actions (引用结果)可执行的动作
	 * @param array $workorder 工单详情
	 * @param array $operator 收单人列表
	 * @param array $my 当前人的操作信息
	 * @return boolean
	 */
	protected function __action_by_other(&$actions, $workorder, $operator_list, $my) {

		// 可允许的动作
		$actions = array();
		switch ($workorder['wostate']) {
			case voa_d_oa_workorder::WOSTATE_WAIT:// 待执行
			case voa_d_oa_workorder::WOSTATE_REFUSE:// 历史执行人已拒绝
				// 检查“我”的状态
				/**
				 * 以下某些状态并不会出现，在此判断仅为避免万一
				 */
				switch ($my['worstate']) {
					case voa_d_oa_workorder_receiver::WORSTATE_WAIT:// 等待接单
						$actions = array(voa_d_oa_workorder::ACTION_REFUSE, voa_d_oa_workorder::ACTION_CONFIRM);
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_REFUSE:// 我已拒绝
						$actions = array();
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_CONFIRM:// 我已确认，理论上此状态不会存在
						$actions = array();
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_MYCOMPLETE:// 我已完成，理论上此状态不会存在
						$actions = array();
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_ROBBED:// 我被抢单（别人已确认），理论上此状态不会存在
						$actions = array();
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_MYCANCEL:// 我已撤销接单
						$actions = array();
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_COMPLETE:// 别人已完成，理论上此状态不会存在
						$actions = array();
						break;
					case voa_d_oa_workorder_receiver::WORSTATE_CANCEL:// 派单人已撤单，理论上此状态不会存在
						$actions = array();
						break;
				}
				break;
			case voa_d_oa_workorder::WOSTATE_CONFIRM:// 执行人已确认
				$actions = array();
				break;
			case voa_d_oa_workorder::WOSTATE_COMPLETE:// 执行人已完成
				$actions = array();
				break;
		}

		return true;
	}

}
