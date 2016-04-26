<?php
/**
 * 请假相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askoff_insert extends voa_uda_frontend_askoff_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 请假数据入库
	 * @param array $askoff 请假主题信息
	 * @param array $post 请假详情信息
	 * @param array $mem 审批人信息
	 * @param array $cculist 抄送人信息
	 * @return boolean
	 */
	public function askoff_new(&$askoff, &$post, &$mem, &$cculist) {
		/** 请假内容 */
		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		/** 当前请假人信息入库 */
		$approveuid = (string)$this->_request->get('approveuid');
		if (!$this->val_approveuid($approveuid)) {
			return false;
		}

		/** 请假开始时间 */
		$begintime = (string)$this->_request->get('begintime');
		if(strpos($begintime, '-')) {
			$this->val_begintime($begintime);
		}

		/** 请假结束时间 */
		$endtime = (string)$this->_request->get('endtime');
		if(strpos($endtime, '-')) {
			$this->val_endtime($endtime);
		}

		if ($begintime >= $endtime) {
			$this->errmsg(105, 'begintime_or_endtime_error');
			return false;
		}

		/** 判断请假类型是否正确 */
		$type = (string)$this->_request->get('type');
		if (!$this->val_type($type)) {
			return false;;
		}

		/** 抄送人 */
		$uidstr = (string)$this->_request->get('carboncopyuids');
		$ccuids = array();
		if (!$this->val_carboncopyuids($uidstr, $ccuids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		/** 读取用户信息, 包括请假人,审批人和抄送人信息 */
		$ccuids[] = $approveuid;
		$ccuids[] = startup_env::get('wbs_uid');
		$cculist = $servm->fetch_all_by_ids($ccuids);


		/** 请假审批人 */
		$mem = $cculist[$approveuid];
		unset($cculist[$approveuid]);

		if (empty($approveuid) || empty($mem)) {
			$this->errmsg(150, 'approveuser_is_empty');
			return false;
		}

		/** 不能审批自己的申请信息 */
		if ($mem['m_uid'] == startup_env::get('wbs_uid')) {
			$this->errmsg(151, 'askoff_verify_self');
			return false;
		}

		// 上传的附件id by Deepseath@20141222#332
		$upload_attach_ids = (string)$this->_request->post('at_ids');
		$upload_attach_ids = trim($upload_attach_ids);
		// 检查附件id
		$attach_ids = array();
		// 判断是否上传了附件 且 系统是否允许上传图片
		if (!empty($upload_attach_ids) && !empty($this->_sets['upload_image'])) {

			// 整理附件id
			foreach (explode(',', $upload_attach_ids) as $_id) {
				if (!is_numeric($_id)) {
					continue;
				}
				$_id = (int)$_id;
				if ($_id > 0 && !isset($attach_ids[$_id])) {
					$attach_ids[$_id] = $_id;
				}
			}
			// 上传的图片数
			$count = count($attach_ids);
			// 设置了最少上传图片数 且上传的图片数量小于要求的数
			if (!empty($this->_sets['upload_image_min_count']) && $count < $this->_sets['upload_image_min_count']) {
				$this->errmsg(1511, '至少要求上传 '.$this->_sets['upload_image_min_count'].' 张图片，您上传了 '.$count.' 张');
				return false;
			}
			// 不能超出系统要求的上传数
			if ($count > $this->_sets['upload_image_max_count']) {
				$this->errmsg(1512, '最多只允许上传 '.$this->_sets['upload_image_max_count'].' 张图片，您已上传了 '.$count.' 张');
				return false;
			}
		}
		// 获取附件信息  by Deepseath@20141222#332
		$attachs = array();
		if (!empty($attach_ids)) {
			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$attachs = array();
			$attachs = $serv_at->fetch_by_conditions(array(
				'at_id' => array($attach_ids, '='),
				'm_uid' => startup_env::get('wbs_uid')
			));
		}

		/** 数据入库 */
		$servao = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$servpt = &service::factory('voa_s_oa_askoff_post', array('pluginid' => startup_env::get('pluginid')));
		$servpc = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$servat = &service::factory('voa_s_oa_askoff_attachment', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 申请信息入库 */
			$askoff = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'ao_type' => $type,
				'ao_begintime' => $begintime,
				'ao_endtime' => $endtime,
				'ao_status' => voa_d_oa_askoff::STATUS_NORMAL
			);

			$ao_id = $servao->insert($askoff, true);
			$askoff['ao_id'] = $ao_id;

			if (empty($ao_id)) {
				throw new Exception('askoff_new_failed');
			}

			/** 审批人信息入库 */
			$aopc_id = $servpc->insert(array(
				'ao_id' => $ao_id,
				'm_uid' => $mem['m_uid'],
				'm_username' => $mem['m_username'],
				'aopc_status' => voa_d_oa_askoff_proc::STATUS_NORMAL
			), true);


			if (empty($aopc_id)) {
				throw new Exception('askoff_new_failed');
			}

			/** 把进度 id 更新到请假表 */
			$askoff['aopc_id'] = $aopc_id;
			$servao->update(array(
				'aopc_id' => $aopc_id
			), array('ao_id' => $ao_id));

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				$servpc->insert(array(
					'ao_id' => $ao_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'aopc_status' => voa_d_oa_askoff_proc::STATUS_CARBON_COPY
				));
			}

			/** 请假详情信息入库 */
			$post = array(
				'ao_id' => $ao_id,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'aopt_subject' => '',
				'aopt_message' => $message,
				'aopt_first' => voa_d_oa_askoff_post::FIRST_YES
			);
			$servpt->insert($post);

			// 附件入库   by Deepseath@20141222#310
			foreach ($attachs as $v) {
				$servat->insert(array(
					'ao_id' => $ao_id,
					'aopt_id' => 0,// 标记为请假主题图片
					'at_id' => $v['at_id'],
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'aoat_status' => voa_d_oa_askoff_attachment::STATUS_NORMAL
				));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(152, 'askoff_new_failed');
			return false;
		}

		return true;
	}
}
