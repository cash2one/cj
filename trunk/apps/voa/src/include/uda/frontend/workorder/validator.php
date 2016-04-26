<?php
/**
 * validator.php
 * 数据验证
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_workorder_validator extends voa_uda_frontend_workorder_abstract {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 检查工单状态
	 * @param number $wostate
	 * @return boolean
	 */
	public function workorder_wostate(&$wostate) {

		$wostate = (int)$wostate;
		if (!isset($this->wostate[$wostate])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::WOSTATE_ERROR);
		}

		return true;
	}

	/**
	 * 检查工单备注
	 * @param string $remark
	 * @return boolean
	 */
	public function workorder_remark(&$remark) {

		$remark = (string)$remark;
		$remark = trim($remark);
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['rule_remark'])) {
			$this->plugin_setting['rule_remark'] = array(
				voa_d_oa_workorder::LENGTH_REMARK_MIN,
				voa_d_oa_workorder::LENGTH_REMARK_MAX
			);
		}
		// 字符限制最小长度
		$min = max($this->plugin_setting['rule_remark'][0], voa_d_oa_workorder::LENGTH_REMARK_MIN);
		// 字符限制最大长度
		$max = min($this->plugin_setting['rule_remark'][1], voa_d_oa_workorder::LENGTH_REMARK_MAX);

		if (validator::is_string_count_in_range($remark, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_workorder::REMARK_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_workorder::REMARK_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 检查联系人
	 * @param string $contacter
	 * @return boolean
	 */
	public function workorder_contacter(&$contacter = '') {

		$contacter = (string)$contacter;
		$contacter = trim($contacter);
		if ($contacter != rhtmlspecialchars($contacter)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::CONTACTER_STRING_ERROR);
		}
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['rule_contacter'])) {
			$this->plugin_setting['rule_contacter'] = array(
				voa_d_oa_workorder::LENGTH_CONTACTER_MIN,
				voa_d_oa_workorder::LENGTH_CONTACTER_MAX
			);
		}
		$min = max($this->plugin_setting['rule_contacter'][0], voa_d_oa_workorder::LENGTH_CONTACTER_MIN);
		$max = min($this->plugin_setting['rule_contacter'][1], voa_d_oa_workorder::LENGTH_CONTACTER_MAX);
		if (validator::is_string_count_in_range($contacter, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_workorder::CONTACTER_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_workorder::CONTACTER_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 检查联系电话
	 * @param string $phone
	 * @return boolean
	 */
	public function workorder_phone(&$phone = '') {

		$phone = (string)$phone;
		$phone = trim($phone);

		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['rule_phone'])) {
			$this->plugin_setting['rule_phone'] = array(
				voa_d_oa_workorder::LENGTH_PHONE_MIN,
				voa_d_oa_workorder::LENGTH_PHONE_MAX
			);
		}
		$min = max($this->plugin_setting['rule_phone'][0], voa_d_oa_workorder::LENGTH_PHONE_MIN);
		$max = min($this->plugin_setting['rule_phone'][1], voa_d_oa_workorder::LENGTH_PHONE_MAX);
		if (validator::is_string_count_in_range($phone, $min, $max)) {

			if ($phone && !validator::is_phone($phone) && !validator::is_mobile($phone)) {
				return $this->set_errmsg(voa_errcode_oa_workorder::PHONE_STRING_ERROR);
			}
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_workorder::PHONE_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_workorder::PHONE_LENGTH_MAX_ERROR, $min, $max);
		}
	}

	/**
	 * 检查联系地址
	 * @param string $address
	 * @return boolean
	 */
	public function workorder_address(&$address = '') {

		$address = (string)$address;
		$address = trim($address);
		if (!validator::is_addr($address)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::ADDRESS_STRING_ERROR);
		}
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['rule_address'])) {
			$this->plugin_setting['rule_address'] = array(
				voa_d_oa_workorder::LENGTH_ADDRESS_MIN,
				voa_d_oa_workorder::LENGTH_ADDRESS_MAX
			);
		}
		$min = max($this->plugin_setting['rule_address'][0], voa_d_oa_workorder::LENGTH_ADDRESS_MIN);
		$max = min($this->plugin_setting['rule_address'][1], voa_d_oa_workorder::LENGTH_ADDRESS_MAX);
		if (validator::is_string_count_in_range($address, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_workorder::ADDRESS_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_workorder::ADDRESS_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 检查接单人是否有效
	 * @param mixed $m_uids 支持数字、分隔符号的字符串、数组
	 * @return boolean
	 */
	public function receiver_uids(&$m_uids, $comma = ',') {

		// 检查整理接单人uid格式
		if (is_numeric($m_uids)) {
			// 单个人
			$m_uids = array($m_uids);
		} elseif (is_scalar($m_uids) && strpos($m_uids, $comma) !== false) {
			// 使用分隔符分隔的多个人
			$m_uids = explode($comma, $m_uids);
		}

		// 非数组，则非法
		if (!is_array($m_uids)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_UID_ERROR);
		}

		// 检查给定的uid数组是否合法
		$tmp = array();
		foreach ($m_uids as $_m_uid) {
			if (!is_numeric($_m_uid)) {
				continue;
			}
			$_m_uid = (int)$_m_uid;
			if (!$_m_uid || isset($tmp[$_m_uid])) {
				continue;
			}
			$tmp[$_m_uid] = $_m_uid;
		}
		unset($_m_uid);
		if (empty($tmp)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_UID_NULL);
		}
		$m_uids = $tmp;
		unset($tmp);

		// 检查数据表内是否存在这些人
		$checked = array();
		$serv_member = &service::factory('voa_s_oa_member');
		foreach ($serv_member->fetch_all_by_ids($m_uids) as $_m) {
			$checked[] = $_m['m_uid'];
		}
		unset($serv_member, $_m);
		if (!$checked) {
			return $this->set_errmsg(voa_errcode_oa_workorder::OPERATOR_UID_DBNULL);
		}

		$m_uids = $checked;
		unset($checked);

		return true;
	}

	/**
	 * 检查执行说明文字
	 * @param string $caption
	 * @return boolean
	 */
	public function detail_caption(&$caption = '') {

		$caption = (string)$caption;
		$caption = trim($caption);
		if ($caption != rhtmlspecialchars($caption)) {
			return $this->set_errmsg(voa_errcode_oa_workorder::CAPTION_STRING_ERROR);
		}
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['rule_caption'])) {
			$this->plugin_setting['rule_caption'] = array(
				voa_d_oa_workorder_detail::LENGTH_CAPTION_MIN,
				voa_d_oa_workorder_detail::LENGTH_CAPTION_MAX
			);
		}
		$min = max($this->plugin_setting['rule_caption'][0], voa_d_oa_workorder_detail::LENGTH_CAPTION_MIN);
		$max = min($this->plugin_setting['rule_caption'][1], voa_d_oa_workorder_detail::LENGTH_CAPTION_MAX);
		if (validator::is_string_count_in_range($caption, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_workorder::CAPTION_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_workorder::CAPTION_LENGTH_MAX_ERROR, $max);
		}
	}

	/**
	 * 检查接收人执行状态
	 * @param number $worstate
	 * @return boolean
	 */
	public function receiver_worstate($worstate = 0) {

		$worstate = (int)$worstate;
		if (!isset($this->worstate[$worstate])) {
			return $this->set_errmsg(voa_errcode_oa_workorder::WORSTATE_ERROR);
		}

		return true;
	}

	/**
	 * 检查操作的原因文字
	 * @param string $reason
	 * @return boolean
	 */
	public function log_reason(&$reason = ''){

		$reason = (string)$reason;
		$reason = trim($reason);
		// 避免未定义的意外出现
		if (!isset($this->plugin_setting['rule_reason'])) {
			$this->plugin_setting['rule_reason'] = array(
				voa_d_oa_workorder_log::LENGTH_REASON_MIN,
				voa_d_oa_workorder_log::LENGTH_REASON_MAX
			);
		}
		$min = max($this->plugin_setting['rule_reason'][0], voa_d_oa_workorder_log::LENGTH_REASON_MIN);
		$max = min($this->plugin_setting['rule_reason'][1], voa_d_oa_workorder_log::LENGTH_REASON_MAX);
		if (validator::is_string_count_in_range($reason, $min, $max)) {
			return true;
		}

		if ($min > 0) {
			return $this->set_errmsg(voa_errcode_oa_workorder::REASON_LENGTH_ERROR, $min, $max);
		} else {
			return $this->set_errmsg(voa_errcode_oa_workorder::REASON_LENGTH_MAX_ERROR, $max);
		}
	}

}
