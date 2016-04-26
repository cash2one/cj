<?php
/**
 * voa_uda_frontend_plan_base
 * 统一数据访问/日程应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_plan_base extends voa_uda_frontend_base {

	/**
	 * 参与人员状态映射
	 * @var array
	 */
	public $mem_status = array(
		'normal' => voa_d_oa_plan_mem::STATUS_NORMAL,
		'update' => voa_d_oa_plan_mem::STATUS_UPDATE,
		'carbon_copy' => voa_d_oa_plan_mem::STATUS_CARBON_COPY,
		'remove' => voa_d_oa_plan_mem::STATUS_REMOVE
	);
	/** 配置信息 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.plan.setting', 'oa');
	}

	public function v_pl_id(&$pl_id) {
		intval($pl_id);
		return true;
	}

	/**
	 * 验证开始时间是否正确
	 * @param int $begin_at
	 * @return boolean
	 */
	public function v_begin_at(&$begin_at) {
		$begin_at = rstrtotime($begin_at);
		if (0 >= $begin_at) {
			$this->errmsg(110, 'begin_at_invalid');
			return false;
		}

		return $begin_at;
	}

	/**
	 * 验证结束时间是否正确
	 * @param int $finish_at
	 * @return boolean
	 */
	public function v_finish_at(&$finish_at) {
		$finish_at = rstrtotime($finish_at);
		if (0 >= $finish_at) {
			$this->errmsg(110, 'finish_at_invalid');
			return false;
		}

		return $finish_at + 1;
	}

	/**
	 * 验证提醒时间是否正确
	 * @param int $alarm_at
	 * @return boolean
	 */
	public function v_alarm_at(&$alarm_at) {
		$alarm_at = rstrtotime($alarm_at);
		if (0 >= $alarm_at) {
			$this->errmsg(110, 'alarm_at_invalid');
			return false;
		}

		return $alarm_at;
	}

	/**
	 * 验证标题
	 * @param string $str
	 * @return boolean
	 */
	public function v_subject(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, 'subject_too_short');
			return false;
		}

		return $str;
	}

	/**
	 * 验证内容
	 * @param string $str
	 * @return boolean
	 */
	public function v_address(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'address_too_short');
			return false;
		}

		return $str;
	}

	/**
	 * 验证类型
	 * @param string $type
	 * @return boolean
	 */
	public function v_type(&$type) {
		$type = (int)$type;
		if (0 >= $type) {
			$this->errmsg(102, 'type_error');
			return false;
		}

		return $type;
	}

	/**
	 * 验证分享人
	 * @param string $uidstr
	 * @param array $uids
	 * @return boolean
	 */
	public function v_shares($uidstr, &$uids) {
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
