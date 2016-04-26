<?php
/**
 * voa_uda_frontend_footprint_base
 * 统一数据访问/销售轨迹应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_footprint_base extends voa_uda_frontend_base {

	/**
	 * 轨迹权限状态映射
	 * @var array
	 */
	public $mem_status = array(
		'normal' => voa_d_oa_footprint_mem::STATUS_NORMAL,
		'update' => voa_d_oa_footprint_mem::STATUS_UPDATE,
		'carbon_copy' => voa_d_oa_footprint_mem::STATUS_CARBON_COPY,
		'remove' => voa_d_oa_footprint_mem::STATUS_REMOVE
	);
	/** 配置信息 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.footprint.setting', 'oa');
	}

	/**
	 * 验证时间是否正确
	 * @param int $visittime
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_visittime(&$visittime, &$data, $odata = array()) {
		$visittime = rstrtotime($visittime);
		if (0 >= $visittime) {
			$this->errmsg(110, 'visittime_invalid');
			return false;
		}

		if (empty($odata) || $odata['fp_visittime'] != $visittime) {
			$data['fp_visittime'] = $visittime;
		}

		return true;
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
		if (empty($str)) {
			$this->errmsg(100, 'subject_too_short');
			return false;
		}

		if (empty($odata) || $odata['fp_subject'] != $str) {
			$data['fp_subject'] = $str;
		}

		return true;
	}

	/**
	 * 验证信息
	 * @param string $str
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_message(&$str, &$data, $odata = array()) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, 'message_too_short');
			return false;
		}

		if (empty($odata) || $odata['fppt_message'] != $str) {
			$data['fppt_message'] = $str;
		}

		return true;
	}

	/**
	 * 验证类型
	 * @param string $type
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_type(&$type, &$data, $odata = array()) {
		$type = trim($type);
		if (empty($type)) {
			$this->errmsg(100, 'type_is_empty');
			return false;
		}

		if (empty($odata) || $odata['fp_type'] != $type) {
			$data['fp_type'] = $type;
		}

		return true;
	}

	/**
	 * 验证地址
	 * @param string $str
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_address(&$str, &$data, $odata = array()) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'address_short');
			return false;
		}

		if (empty($odata) || $odata['fp_address'] != $str) {
			$data['fp_address'] = $str;
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

	/**
	 * 验证附件id
	 * @param unknown $at_idstr
	 * @param unknown $at_ids
	 * @return boolean
	 */
	public function chk_at_ids($at_idstr, &$at_ids) {
		$str = (string)$at_idstr;
		$str = trim($str);
		$tmps = empty($str) ? array() : explode(',', $str);
		$at_ids = (array)$at_ids;
		foreach ($tmps as $id) {
			$id = (int)$id;
			if (0 < $id) {
				$at_ids[$id] = $id;
			}
		}

		return true;
	}
}
