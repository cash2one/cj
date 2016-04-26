<?php
/**
 * 报销数据过滤
 * $Author$
 * $Id$
 */

class voa_uda_frontend_reimburse_base extends voa_uda_frontend_base {
	/** 配置信息 */
	protected $_sets = array();

	/**
	 * 报销状态文字描述
	 * @var array
	 */
	public $reimburse_status = array(
			voa_d_oa_reimburse::STATUS_NORMAL => '审批中',
			voa_d_oa_reimburse::STATUS_TRANSMIT => '通过并转审批',
			voa_d_oa_reimburse::STATUS_APPROVE => '已批准',
			voa_d_oa_reimburse::STATUS_REFUSE => '已拒绝',
	);

	/**
	 * 审批进度状态文字描述
	 * @var array
	 */
	public $proc_status = array(
			voa_d_oa_reimburse_proc::STATUS_NORMAL => '审批申请中',
			voa_d_oa_reimburse_proc::STATUS_TRANSMIT => '通过并转审批',
			voa_d_oa_reimburse_proc::STATUS_APPROVE => '已批准',
			voa_d_oa_reimburse_proc::STATUS_REFUSE => '已拒绝',
			voa_d_oa_reimburse_proc::STATUS_CC => '抄送',
	);

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.reimburse.setting', 'oa');
	}

	/**
	 * 验证主题
	 * @param string $subject
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_subject(&$subject, &$data, $odata = array()) {
		$subject = (string)$subject;
		if (empty($subject)) {
			$this->errmsg(101, 'subject_too_short');
			return false;
		}

		if (empty($odata) || $odata['rb_subject'] != $subject) {
			$data['rb_subject'] = $subject;
		}

		return true;
	}

	/**
	 * 验证报销类型
	 * @param string $type
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_type(&$type, &$data, $odata = array()) {
		$types = $this->_sets['types'];
		if (!array_key_exists($type, $types)) {
			$this->errmsg(105, 'type_invalid');
			return false;
		}

		if (empty($odata) || $odata['rbb_type'] != $type) {
			$data['rbb_type'] = $type;
		}

		return true;
	}

	/**
	 * 验证金额
	 * @param string $expend
	 * @param array $data
	 * @param array $odata
	 * @return boolean
	 */
	public function val_expend($expend, &$data, $odata = array()) {
		$expend = (float)$expend;
		$expend = intval($expend * 100);

		if (empty($odata) || !isset($odata['rbb_expend']) || $odata['rbb_expend'] != $expend) {
			$data['rbb_expend'] = $expend;
		}

		return true;
	}

	/**
	 * 验证时间
	 * @param string $time
	 * @return boolean
	 */
	public function val_time(&$time, &$data, $odata = array()) {
		$btime = (string)$time;
		$time = rstrtotime($btime);
		if (0 >= $time) {
			$this->errmsg(103, 'time_invalid');
			return false;
		}

		if (empty($data['rbb_time']) || $odata['rbb_time'] != $time) {
			$data['rbb_time'] = $time;
		}

		return true;
	}

	/**
	 * 验证原因(批注)
	 * @param unknown $reason
	 * @param unknown $data
	 * @param unknown $odata
	 * @return boolean
	 */
	public function val_reason($reason, &$data, $odata = array()) {
		$reason = trim($reason);
		if (empty($odata) || $odata['rbb_reason'] != $reason) {
			$data['rbb_reason'] = $reason;
		}

		return true;
	}

	/**
	 * 验证内容
	 * @param string $str
	 * @param unknown $data
	 * @param unknown $odata
	 * @return boolean
	 */
	public function val_message($str, &$data, $odata = array()) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'message_too_short');
			return false;
		}

		if (empty($odata) || $odata['rbpt_message'] != $str) {
			$data['rbpt_message'] = $str;
		}

		return true;
	}

	/**
	 * 验证单个附件id
	 * @param int $id
	 * @param unknown $data
	 * @param unknown $odata
	 * @return boolean
	 */
	public function val_at_id($id, &$data, $odata = array()) {
		$id = (int)$id;
		if ($id == $odata['at_id']) {
			return true;
		}

		$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		/** 清除旧附件 */
		if (0 < $odata['at_id']) {
			$serv_at->delete_by_conditions(array(
				'm_uid' => startup_env::get('wbs_uid'),
				'at_id' => $odata['at_id']
			));
		}

		/** 如果附件id为0, 则 */
		if (0 == $id) {
			return true;
		}

		$att = $serv_at->fetch_by_id($id);
		if (empty($att)) {
			$id = 0;
			return true;
		}

		$data['at_id'] = $id;

		return true;
	}

	/**
	 * 验证多个附件id
	 * @param string $ids
	 * @param array $data (引用结果)
	 * @param array $odata
	 * @return boolean
	 */
	public function val_at_ids($ids, &$data) {

		$new_ids = array();
		foreach (rintval(explode(',', $ids), true) as $_id) {
			$_id = (string)$_id;
			$_id = trim($_id);
			if (!is_numeric($_id) || $_id <= 0 || intval($_id) != $_id) {
				continue;
			}
			$new_ids[] = $_id;
		}
		unset($_id);

		$data['attach_list'] = array();
		if (empty($new_ids)) {
			return true;
		}

		$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		$data['attach_list'] = $serv_at->fetch_by_ids($new_ids);

		return true;

/*
		// 存在旧数据
		if (!empty($odata)) {

			// 旧的附件id
			$o_ids = array();
			foreach ($odata as $_rbbat) {
				$o_ids[] = $_rbbat['at_id'];
			}
			unset($_rbbat);

			// 新附件id
			$new_ids = array();
			foreach ($ids as $_id) {
				if (!in_array($_id, $o_ids)) {
					$new_ids[] = $_id;
				}
			}
			unset($_id);

			// 已经删除的id
			$remove_ids = array();
			foreach ($o_ids as $_id) {
				if (!in_array($_id, $ids)) {
					$remove_ids[] = $_id;
				}
			}
			unset($_id);

			// 存在已删除的附件
			if (!empty($remove_ids)) {
				$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
				$serv_at->delete_by_conditions(array(
					'm_uid' => startup_env::get('wbs_uid'),
					'at_id' => $remove_ids
				));
				$serv_rbbat = &service::factory('voa_s_oa_reimburse_bill_attachment');
				foreach ($odata as $_rbbat) {
					if (in_array($_rbbat['at_id'], $remove_ids)) {
						$serv_rbbat->delete_by_conditions(array(
							'rbb_id' => $_rbbat['rbb_id'],
							'm_uid' => startup_env::get('wbs_uid'),
							'at_id' => $_rbbat['at_id']
						));
					}
				}
			}
		}
*/
	}

	/**
	 * 验证清单id
	 * @param unknown $ids
	 */
	public function chk_rbb_id(&$ids) {

		if (!is_array($ids)) {
			$ids = explode(',',$ids);
		}

		/** 过滤非数字 */
		foreach ($ids as $k => $v) {
			$v = (int)$v;
			if (0 >= $v) {
				unset($ids[$k]);
			}
		}

		/** 如果清单为空, 则 */
		if (empty($ids)) {
			$this->errmsg(100, 'rbb_id_is_empty');
			return false;;
		}

		return true;
	}

	/**
	 * 验证审核人uid
	 * @param int $uid
	 * @return boolean
	 */
	public function chk_approveuid(&$uid) {
		$uid = (int)$uid;
		if (0 >= $uid) {
			$this->errmsg(102, 'approveuid_error');
			return false;
		}

		if ($uid == startup_env::get('wbs_uid')) {
			$this->errmsg(100, 'approve_user_is_self');
			return false;
		}

		return true;
	}

	/**
	 * 验证抄送人uid
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
