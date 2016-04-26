<?php
/**
 * voa_uda_frontend_vnote_base
 * 统一数据访问/备忘应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_vnote_base extends voa_uda_frontend_base {

	/**
	 * 参与人员状态映射
	 * @var array
	 */
	public $mem_status = array(
		'normal' => voa_d_oa_vnote_mem::STATUS_NORMAL,
		'update' => voa_d_oa_vnote_mem::STATUS_UPDATE,
		'carbon_copy' => voa_d_oa_vnote_mem::STATUS_CC,
		'remove' => voa_d_oa_vnote_mem::STATUS_REMOVE
	);
	/** 配置信息 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.vnote.setting', 'oa');
	}

	/**
	 * 验证标题
	 * @param string $str
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_subject(&$str, &$data, $odata = array()) {
		$str = trim($str);
		/**if (empty($str)) {
			$this->errmsg(100, 'subject_short');
			return false;
		}*/

		if (empty($odata) || $odata['vn_subject'] != $str) {
			$data['vn_subject'] = $str;
		}

		return true;
	}

	/**
	 * 验证内容
	 * @param string $str
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_message(&$str, &$data, $odata = array()) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'message_too_short');
			return false;
		}

		if (empty($odata) || $odata['vnp_message'] != $str) {
			$data['vnp_message'] = $str;
		}

		return true;
	}

	/**
	 * 验证审批人
	 * @param string $uid
	 * @return boolean
	 */
	public function chk_approveuid(&$uid) {
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
	public function chk_carboncopyuids($uidstr, &$uids) {
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
