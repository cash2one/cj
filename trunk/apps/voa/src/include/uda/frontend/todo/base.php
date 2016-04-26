<?php
/**
 * voa_uda_frontend_todo_base
 * 统一数据访问/待办事项应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_todo_base extends voa_uda_frontend_base {

	/**
	 * 参与人员状态映射
	 * @var array
	 */
	public $mem_status = array(
		'normal' => voa_d_oa_todo_mem::STATUS_NORMAL,
		'update' => voa_d_oa_todo_mem::STATUS_UPDATE,
		'carbon_copy' => voa_d_oa_todo_mem::STATUS_CARBON_COPY,
		'remove' => voa_d_oa_todo_mem::STATUS_REMOVE
	);
	/** 配置信息 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.todo.setting', 'oa');
	}

	/**
	 * 验证提醒时间是否正确
	 * @param int $calltime
	 * @return boolean
	 */
	public function val_calltime(&$calltime) {
		$calltime = rstrtotime($calltime);
		if (0 >= $calltime) {
			$this->errmsg(110, 'calltime_invalid');
			return false;
		}

		return true;
	}

	/**
	 * 验证截止时间是否正确
	 * @param int $exptime
	 * @return boolean
	 */
	public function val_exptime(&$exptime) {
		$exptime = rstrtotime($exptime);
		if (0 >= $exptime) {
			$this->errmsg(110, 'exptime_invalid');
			return false;
		}

		return true;
	}

	/**
	 * 验证标题
	 * @param string $str
	 * @return boolean
	 */
	public function val_subject(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, 'subject_too_short');
			return false;
		}

		return true;
	}

	/**
	 * 验证完成状态
	 * @param interger $interger
	 * @return boolean
	 */
	public function val_completed(&$interger) {
		$interger = intval($interger);

		return true;
	}

	/**
	 * 验证星标状态
	 * @param interger $interger
	 * @return boolean
	 */
	public function val_stared(&$interger) {
		$interger = intval($interger);

		return true;
	}

	/**
	 * 验证内容
	 * @param string $str
	 * @return boolean
	 */
	public function val_message(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'message_too_short');
			return false;
		}

		return true;
	}

	/**
	 * 验证审批人
	 * @param string $uid
	 * @return boolean
	 */
	public function val_approveuid(&$uid) {
		$uid = (int)$uid;
		if (0 >= $uid) {
			$this->errmsg(102, 'approveuid_error');
			return false;
		}

		return true;
	}

	/**
	 * 验证抄送人
	 * @param string $uidstr
	 * @param array $uids
	 * @return boolean
	 */
	public function val_carboncopyuids($uidstr, &$uids) {
		$uidstr = (string)$uidstr;
		$uidstr = trim($uidstr);
		$tmps = empty($uidstr) ? array() : explode(',', $uidstr);
		$uids = array();
		foreach ($tmps as $uid) {
			$uid = (int)$uid;
			if (0 < $uid) {
				$uids[$uid] = $uid;
			}
		}

		return true;
	}
}
