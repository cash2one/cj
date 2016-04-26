<?php
/**
 * 审批数据过滤
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_base extends voa_uda_frontend_base {
	/** 配置信息 */
	protected $_sets = array();

	/**
	 * 审批申请状态文字描述
	 * @var array
	 */
	public $askfor_status = array(
		voa_d_oa_askfor::STATUS_NORMAL => '审批中',
		voa_d_oa_askfor::STATUS_APPROVE => '已批准',
		voa_d_oa_askfor::STATUS_APPROVE_APPLY => '通过并转审批',
		voa_d_oa_askfor::STATUS_REFUSE => '审批不通过'
	);

	/**
	 * 审批进度状态文字
	 * @var array
	 */
	public $askfor_proc_status = array(
		voa_d_oa_askfor_proc::STATUS_NORMAL => '审批中',
		voa_d_oa_askfor_proc::STATUS_APPROVE => '已通过',
		voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY => '通过并转审批',
		voa_d_oa_askfor_proc::STATUS_REFUSE => '审批不通过',
		voa_d_oa_askfor_proc::STATUS_CARBON_COPY => '抄送',
		voa_d_oa_askfor_proc::STATUS_REMOVE => '已删除'
	);

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.askfor.setting', 'oa');
	}

	/**
	 * 验证标题
	 * @param string $str
	 * @return boolean
	 */
	public function val_subject(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, 'subject_short');
			return false;
		}

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
