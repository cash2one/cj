<?php
/**
 * 报销相关的更新操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_reimburse_update extends voa_uda_frontend_reimburse_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 更新清单信息
	 * @param array $bill
	 * @return boolean
	 */
	public function reimburse_bill_update(&$bill) {
		/** 整理需要更新的数据 */
		$data = array();
		/** 数据和处理方法的对应关系 */
		$gps = array(
			'type' => 'val_type', /** 类型 */
			'time' => 'val_time', /** 账单发生时间 */
			'expend' => 'val_expend', /** 花费 */
			'reason' => 'val_reason', /** 原因 */
			'at_ids' => 'val_at_ids'// 附件id
		);
		if (!$this->_submit2table($gps, $data, $bill)) {
			return false;
		}

		// 附件列表
		$attach_list = !empty($data['attach_list']) ? $data['attach_list'] : array();
		unset($data['attach_list']);

		// 上传的附件id by Deepseath@20141231#314
		$attachs = array();
		// 待删除的附件id
		$remove_atids = array();
		// 待移除的清单附件id
		$remove_rbbatids = array();

		$serv_rbbat = &service::factory('voa_s_oa_reimburse_bill_attachment', array('pluginid' => startup_env::get('pluginid')));
		if ($attach_list) {

			// 整理旧的清单附件
			$old_at_ids = array();
			foreach ($serv_rbbat->fetch_all_by_rbb_ids($bill['rbb_id']) as $_rbbat) {
				if (!isset($attach_list[$_rbbat['at_id']])) {
					$remove_atids['at_id'] = $_rbbat['at_id'];
					$remove_rbbatids[] = $_rbbat['rbbat_id'];
				}
				$old_at_ids[$_rbbat['at_id']] = $_rbbat['at_id'];
			}
			unset($_rbbat);
			// 新增的附件
			foreach ($attach_list as $_at_id => $_attach) {
				if (!isset($old_at_ids[$_at_id])) {
					$attachs[$_at_id] = $_attach;
				}
			}
			unset($_at_id, $_attach);
		}
		// 上传的图片数
		$count = count($attachs);
		// 设置了最少上传图片数 且上传的图片数量小于要求的数
		if (!empty($this->_sets['upload_image_min_count']) && $count < $this->_sets['upload_image_min_count']) {
			$this->errmsg(1151, '至少要求上传 '.$this->_sets['upload_image_min_count'].' 张图片，您上传了 '.$count.' 张');
			return false;
		}
		// 不能超出系统要求的上传数
		if ($count > $this->_sets['upload_image_max_count']) {
			$this->errmsg(1152, '最多只允许上传 '.$this->_sets['upload_image_max_count'].' 张图片，您已上传了 '.$count.' 张');
			return false;
		}

		$serv = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		try {
			$serv->begin();

			if ($remove_atids) {
				$serv_at->delete_by_ids($remove_atids);
			}
			if ($remove_rbbatids) {
				$serv_rbbat->delete_by_ids($remove_rbbatids);
			}
			if ($attachs) {
				// 附件入库   by Deepseath@20141231#314
				foreach ($attachs as $v) {
					$serv_rbbat->insert(array(
						'rbb_id' => $bill['rbb_id'],
						'at_id' => $v['at_id'],
						'm_uid' => startup_env::get('wbs_uid'),
						'm_username' => startup_env::get('wbs_username'),
						'rbbat_status' => voa_d_oa_reimburse_bill_attachment::STATUS_NORMAL
					));
				}
			}

			/** 更新清单信息 */
			$serv->update($data, array('rbb_id' => $bill['rbb_id']));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		/** 返回最新的数据 */
		$bill = array_merge($bill, $data);

		return true;
	}

	/**
	 * 拒绝操作
	 * @param array $reimburse 详情信息
	 * @param array $proc 进度信息
	 * @return boolean
	 */
	public function reimburse_refuse($reimburse, $proc) {
		$data = array();
		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message, $data)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_reimburse_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_pc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();

			/** 更新报销状态 */
			$serv->update(array(
				'rb_status' => voa_d_oa_reimburse::STATUS_REFUSE,
				'rb_uid' => startup_env::get('wbs_uid'),
				'rb_username' => startup_env::get('wbs_username')
			), array('rb_id' => $reimburse['rb_id']));

			/** 更新进度状态 */
			$serv_pc->update(array(
				'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_REFUSE,
				'rbpc_remark' => $data['rbpt_message']
			), array('rbpc_id' => $proc['rbpc_id']));

			$serv_pt->insert(array(
				'rb_id' => $reimburse['rb_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'rbpt_subject' => '',
				'rbpt_message' => $data['rbpt_message'],
				'rbpt_first' => voa_d_oa_reimburse_post::FIRST_NO
			));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 通过操作
	 * @param array $reimburse 详情信息
	 * @param array $proc 进度信息
	 * @return boolean
	 */
	public function reimburse_approve($reimburse, $proc) {
		$data = array();
		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message, $data)) {
			return false;
		}

		/** 判断是否有权限 */
		$adminids = empty($this->_sets['adminids']) || !is_array($this->_sets['adminids']) ? array() : $this->_sets['adminids'];
		if (!empty($adminids) && !in_array(startup_env::get('wbs_uid'), $adminids)) {
			$this->errmsg(100, 'no_privilege');
			return false;;
		}

		$serv = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_reimburse_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_pc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();

			/** 更新审批 */
			$serv->update(array(
				'rb_status' => voa_d_oa_reimburse::STATUS_APPROVE,
				'rb_uid' => startup_env::get('wbs_uid'),
				'rb_username' => startup_env::get('wbs_username')
			), array('rb_id' => $reimburse['rb_id']));

			/** 更新审批进度状态 */
			$serv_pc->update(array(
				'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_APPROVE,
				'rbpc_remark' => $data['rbpt_message']
			), array('rbpc_id' => $proc['rbpc_id']));

			$serv_pt->insert(array(
				'rb_id' => $reimburse['rb_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'rbpt_subject' => '',
				'rbpt_message' => $data['rbpt_message'],
				'rbpt_first' => voa_d_oa_reimburse_post::FIRST_NO
			));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 转审批操作
	 * @param array $reimburse 详情信息
	 * @param array $proc 进度信息
	 * @param array &$mem 转审批用户信息
	 * @return boolean
	 */
	public function reimburse_transmit($reimburse, $proc, &$mem) {
		/** 报销审批人 */
		$approveuid = (int)$this->_request->get('approveuid');
		if ($approveuid == startup_env::get('wbs_uid')) {
			$this->errmsg(100, 'approve_user_is_self');
			return false;
		}

		$message = (string)$this->_request->get('message');
		if (empty($message)) {
			$message = '同意';
		}

		/** 从进度中读取抄送人和审核人记录 */
		$all_uids = array();
		unset($all_uids[startup_env::get('wbs_uid')]);
		/** 获取审核人进度记录和已存在进度的用户 uid */
		$approve_proc = array();
		$new_uids = array();
		$this->_get_proc_and_uids($reimburse['rb_id'], $all_uids, $approveuid, $approve_proc, $new_uids);

		/** 读取用户信息, 包括审批人和抄送人信息 */
		$ccuids = array($approveuid);
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$cculist = $serv_m->fetch_all_by_ids($ccuids);

		/** 如果用户不存在 */
		if (!array_key_exists($approveuid, $cculist)) {
			$this->errmsg(151, 'approve_user_invalid');
			return false;
		}

		/** 取出审批人 */
		$mem = $cculist[$approveuid];
		unset($cculist[$approveuid]);

		$serv_rb = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$serv_pc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_reimburse_post', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_m->begin();

			/** 删除已存在的记录 */
			if (!empty($approve_proc)) {
				$serv_pc->delete_by_ids(array($approve_proc['rbpc_id']));
			}

			/** 转审批信息入库 */
			$rbpc_id = $serv_pc->insert(array(
				'rb_id' => $reimburse['rb_id'],
				'm_uid' => $mem['m_uid'],
				'm_username' => $mem['m_username'],
				'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_NORMAL
			), true);

			/** 更新审批 */
			$serv_rb->update(array(
				'rbpc_id' => $rbpc_id,
				'rb_status' => voa_d_oa_reimburse::STATUS_TRANSMIT,
				'rb_uid' => startup_env::get('wbs_uid'),
				'rb_username' => startup_env::get('wbs_username')
			), array('rb_id' => $reimburse['rb_id']));

			/** 更新审批进度状态 */
			$serv_pc->update(array(
				'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_TRANSMIT,
				'rbpc_remark' => $message
			), array('rbpc_id' => $proc['rbpc_id']));

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				/** 如果是自己 */
				if ($v['m_uid'] == startup_env::get('wbs_uid') || $approveuid == $v['m_uid']) {
					continue;
				}

				$serv_pc->insert(array(
					'rb_id' => $reimburse['rb_id'],
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_CC
				));
			}

			$serv_pt->insert(array(
				'rb_id' => $reimburse['rb_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'rbpt_subject' => '',
				'rbpt_message' => $message,
				'rbpt_first' => voa_d_oa_reimburse_post::FIRST_NO
			));

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			/** 入库操作失败 */
			$this->errmsg(152, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 读取审核人进度
	 * @param int $rb_id 报销id
	 * @param array $uids uid 数组
	 * @param int $approveuid 审核人 uid
	 * @param array $proc_cc 审批人的进度信息
	 * @param array $new_uids 新的用户uid数组
	 * @return 返回审核人进度和新抄送人 uid 数组
	 */
	protected function _get_proc_and_uids($rb_id, $uids, $approveuid, &$proc_cc, &$new_uids) {
		/** 审核人进度信息 */
		$uids[] = $approveuid;
		$serv_pc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv_pc->fetch_by_rb_id($rb_id);
		/** 已经存在于进度的用户 uid */
		$exist_uids = array();
		foreach ($procs as $k => $v) {
			$exist_uids[] = $v['m_uid'];
			/** 如果当前审核人已经在进度中有记录, 则 */
			if ($v['m_uid'] == $approveuid) {
				$proc_cc = $v;
				if ($v['rbpc_status'] != voa_d_oa_reimburse_proc::STATUS_CC) {
					$this->errmsg(200, 'reimburse_duplicte_user');
					return false;
				}
			}
		}

		$new_uids = array_diff($uids, $exist_uids);
		return true;
	}
}
