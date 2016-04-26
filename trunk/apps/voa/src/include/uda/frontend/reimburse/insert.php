<?php
/**
 * 报销相关的新增操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_reimburse_insert extends voa_uda_frontend_reimburse_base {

	public function __construct() {
		parent::__construct();
	}

	public function reimburse_bill_new(&$bill) {
		/** 数据和处理方法的对应关系 */
		$gps = array(
			'type' => 'val_type', /** 类型 */
			'time' => 'val_time', /** 账单发生时间 */
			'expend' => 'val_expend', /** 花费 */
			'reason' => 'val_reason', /** 原因 */
			'at_ids' => 'val_at_ids'// 附件id
		);
		if (!$this->_submit2table($gps, $bill)) {
			return false;
		}

		// 附件列表
		$attachs = !empty($bill['attach_list']) ? $bill['attach_list'] : array();
		unset($bill['attach_list']);

		// 上传的附件id by Deepseath@20141231#314
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
		$serv_rbbat = &service::factory('voa_s_oa_reimburse_bill_attachment', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();

			/** 清单信息入库 */
			$bill['m_uid'] = startup_env::get('wbs_uid');
			$bill['m_username'] = startup_env::get('wbs_username');
			$rbb_id = $serv->insert($bill, true);
			$bill['rbb_id'] = $rbb_id;

			// 附件入库   by Deepseath@20141231#314
			foreach ($attachs as $v) {
				$serv_rbbat->insert(array(
					'rbb_id' => $rbb_id,
					'at_id' => $v['at_id'],
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'rbbat_status' => voa_d_oa_reimburse_bill_attachment::STATUS_NORMAL
				));
			}

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
	 * 报销信息入库
	 * @param array $reimburse 报销信息数组
	 * @param array $folders 所有分组
	 * @return boolean
	 */
	public function reimburse_new(&$reimburse, &$mem, &$cculist) {
		/** 数据和处理方法的对应关系 */
		$gps = array(
			'subject' => 'val_subject' /** 标题 */
		);
		if (!$this->_submit2table($gps, $reimburse)) {
			return false;
		}

		/** 报销清单id */
		$rbb_ids = $this->_request->get('rbb_id');
		if (!$this->chk_rbb_id($rbb_ids)) {
			return false;;
		}

		/** 读取清单记录 */
		$serv = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bills = $serv->fetch_by_conditions(array(
			'm_uid' => startup_env::get('wbs_uid'),
			'rbb_id' => array($rbb_ids)
		));

		if (empty($bills)) {
			$this->errmsg(100, 'rbb_id_invalid');
			return false;
		}

		/** 总花费 */
		$expend = 0;
		foreach ($bills as $v) {
			$expend += $v['rbb_expend'];
		}

		/** 报销审批人 */
		$approveuid = (int)$this->_request->get('approveuid');
		if (!$this->chk_approveuid($approveuid)) {
			return false;
		}

		/** 报销审批人/抄送人信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$ccuids = array($approveuid, startup_env::get('wbs_uid'));
		$cculist = $servm->fetch_all_by_ids($ccuids);
		/** 判断审批人是否存在 */
		if (!array_key_exists($approveuid, $cculist)) {
			$this->errmsg(100, 'approve_user_invalid');
			return false;
		}

		/** 取出审批人信息 */
		$mem = $cculist[$approveuid];
		unset($cculist[$approveuid]);

		$serv = &service::factory('voa_s_oa_reimburse', array('pluginid' => startup_env::get('pluginid')));
		$serv_b = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$serv_s = &service::factory('voa_s_oa_reimburse_bill_submit', array('pluginid' => startup_env::get('pluginid')));
		$serv_pc = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();

			/** 报销申请信息入库 */
			$reimburse['m_uid'] = startup_env::get('wbs_uid');
			$reimburse['m_username'] = startup_env::get('wbs_username');
			$reimburse['rb_expend'] = $expend;
			$reimburse['rb_time'] = startup_env::get('timestamp');
			$rb_id = $serv->insert($reimburse, true);
			$reimburse['rb_id'] = $rb_id;

			/** 更新清单状态 */
			$serv_b->update(array(
				'rbb_status' => voa_d_oa_reimburse_bill::STATUS_USED
			), array('rbb_id' => $rbb_ids));

			/** 已提交的清单和报销id对应关系入库 */
			foreach ($rbb_ids as $id) {
				$serv_s->insert(array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'rb_id' => $reimburse['rb_id'],
					'rbb_id' => $id
				));
			}

			/** 审批人信息/抄送人信息入库 */
			$rbpc_id = $serv_pc->insert(array(
				'rb_id' => $rb_id,
				'm_uid' => $mem['m_uid'],
				'm_username' => $mem['m_username'],
				'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_NORMAL
			), true);
			foreach ($cculist as $user) {
				$serv_pc->insert(array(
					'rb_id' => $rb_id,
					'm_uid' => $user['m_uid'],
					'm_username' => $user['m_username'],
					'rbpc_status' => voa_d_oa_reimburse_proc::STATUS_CC
				));
			}

			/** 更新报销审批进度信息 */
			$serv->update(array('rbpc_id' => $rbpc_id), array('rb_id' => $rb_id));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}
}
