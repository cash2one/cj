<?php
/**
 * 请假数据过滤
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askoff_base extends voa_uda_frontend_base {
	/** 配置信息 */
	protected $_sets = array();

	/**
	 * 请假申请状态文字描述
	 * @var array
	 */
	public $askoff_status = array(
		voa_d_oa_askoff::STATUS_NORMAL => '审批中',
		voa_d_oa_askoff::STATUS_APPROVE => '已批准',
		voa_d_oa_askoff::STATUS_APPROVE_APPLY => '通过并转审批',
		voa_d_oa_askoff::STATUS_REFUSE => '审批不通过'
	);

	/**
	 * 审批进度状态文字
	 * @var array
	 */
	public $askoff_proc_status = array(
		voa_d_oa_askoff_proc::STATUS_NORMAL => '审批中',
		voa_d_oa_askoff_proc::STATUS_APPROVE => '已通过',
		voa_d_oa_askoff_proc::STATUS_APPROVE_APPLY => '通过并转审批',
		voa_d_oa_askoff_proc::STATUS_REFUSE => '审批不通过',
		voa_d_oa_askoff_proc::STATUS_CARBON_COPY => '抄送',
		voa_d_oa_askoff_proc::STATUS_REMOVE => '已删除'
	);

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.askoff.setting', 'oa');
	}

	/**
	 * 验证标题
	 * @param string $str
	 * @return boolean
	 */
	public function val_subject(&$str) {
		$str = trim($str);
		/**if (empty($str)) {
			$this->errmsg(100, 'subject_short');
			return false;
		}*/

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
	 * 验证开始时间
	 * @param string $begintime
	 * @return boolean
	 */
	public function val_begintime(&$begintime) {
		$btime = (string)$begintime;
		$begintime = rstrtotime($btime);
		if (0 >= $begintime) {
			$this->errmsg(103, 'begintime_invalid');
			return false;
		}

		return true;
	}

	/**
	 * 验证结束时间
	 * @param string $endtime
	 * @return boolean
	 */
	public function val_endtime(&$endtime) {
		$etime = (string)$endtime;
		$endtime = rstrtotime($etime);
		if (0 >= $endtime) {
			$this->errmsg(104, 'endtime_invalid');
			return false;
		}

		return true;
	}

	/**
	 * 验证请假类型
	 * @param string $type
	 * @return boolean
	 */
	public function val_type(&$type) {
		$types = $this->_sets['types'];
		if (!array_key_exists($type, $types)) {
			$this->errmsg(105, 'type_invalid');
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
