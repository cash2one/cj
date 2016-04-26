<?php
/**
 * voa_uda_frontend_minutes_base
 * 统一数据访问/会议记录应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_minutes_base extends voa_uda_frontend_base {

	/** 配置信息 */
	protected $_sets = array();

	/**
	 * 会议记录参与人状态文字描述
	 * @var array
	 */
	public $minutes_mem_status = array(
		voa_d_oa_minutes_mem::STATUS_NORMAL => '正常',
		voa_d_oa_minutes_mem::STATUS_UPDATE => '已更新',
		voa_d_oa_minutes_mem::STATUS_CARBON_COPY => '抄送人',
	);

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.minutes.setting', 'oa');
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
	public function val_recvuid(&$uid) {
		$uid = (int)$uid;
		if (0 >= $uid) {
			$this->errmsg(102, 'recvuid_error');
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
