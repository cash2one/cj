<?php
/**
 * 会议记录相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_minutes_insert extends voa_uda_frontend_minutes_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 新会议记录入库
	 * @param unknown $minutes
	 * @param unknown $post
	 * @param unknown $joinlist
	 * @param unknown $cculist
	 * @return boolean
	 */
	public function minutes_new(&$minutes, &$post, &$joinlist, &$cculist) {
		/** 标题 */
		$subject = (string)$this->_request->get('subject');
		if (!$this->val_subject($subject)) {
			return false;
		}

		/** 审批内容 */
		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		/** 会议参会人 */
		$recvuidstr = (string)$this->_request->get('recvuids');
		$recvuids = array();
		if (!$this->val_carboncopyuids($recvuidstr, $recvuids)) {
			return false;;
		}

		/** 抄送人 */
		$uidstr = (string)$this->_request->get('carboncopyuids');
		$ccuids = array();
		if (!$this->val_carboncopyuids($uidstr, $ccuids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		/** 读取用户信息, 包括审批人和抄送人信息 */
		$ccuids[startup_env::get('wbs_uid')] = startup_env::get('wbs_uid');
		$ccuids = array_merge($ccuids, $recvuids);
		$cculist = $servm->fetch_all_by_ids($ccuids);

		/** 判断是否有参会/抄送人 */
		if (empty($cculist)) {
			$this->errmsg(100, 'recvuser_is_empty');
			return false;
		}

		// 上传的附件id by Deepseath@20141230#391
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
				$this->errmsg(1151, '至少要求上传 '.$this->_sets['upload_image_min_count'].' 张图片，您上传了 '.$count.' 张');
				return false;
			}
			// 不能超出系统要求的上传数
			if (!empty($this->_sets['upload_image_max_count']) && $count > $this->_sets['upload_image_max_count']) {
				$this->errmsg(1152, '最多只允许上传 '.$this->_sets['upload_image_max_count'].' 张图片，您已上传了 '.$count.' 张');
				return false;
			}
		}

		// 获取附件信息  by Deepseath@20141230#391
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
		$serv_mi = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$serv_mim = &service::factory('voa_s_oa_minutes_mem', array('pluginid' => startup_env::get('pluginid')));
		$serv_p = &service::factory('voa_s_oa_minutes_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_miat = &service::factory('voa_s_oa_minutes_attachment', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 会议纪要标题信息入库 */
			$minutes = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'mi_subject' => $subject,
				'mi_status' => voa_d_oa_minutes::STATUS_NORMAL,
				'mi_created' => startup_env::get('timestamp')
			);
			$mi_id = $serv_mi->insert($minutes, true);
			$minutes['mi_id'] = $mi_id;

			if (empty($mi_id)) {
				throw new Exception('minutes_new_failed');
			}

			/** 会议纪要信息入库 */
			$mip_id = $serv_p->insert(array(
				'mi_id' => $mi_id,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'mip_subject' => $subject,
				'mip_message' => $message,
				'mip_first' => voa_d_oa_minutes_post::FIRST_YES
			), true);

			if (empty($mip_id)) {
				throw new Exception('minutes_new_failed');
			}

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				$mim = array(
					'mi_id' => $mi_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'mim_status' => voa_d_oa_minutes_mem::STATUS_CARBON_COPY
				);
				/** 如果是目标人 */
				if (startup_env::get('wbs_uid') == $v['m_uid'] || (!empty($recvuids) && in_array($v['m_uid'], $recvuids))) {
					$mim['mim_status'] = voa_d_oa_minutes_mem::STATUS_NORMAL;
					$joinlist[$v['m_uid']] = $v;
					unset($cculist[$v['m_uid']]);
				}

				$serv_mim->insert($mim);
			}

			// 附件入库   by Deepseath@20141230#391
			foreach ($attachs as $v) {
				$serv_miat->insert(array(
					'mi_id' => $mi_id,
					'mip_id' => 0,// 标记为会议记录主题
					'at_id' => $v['at_id'],
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'miat_status' => voa_d_oa_dailyreport_attachment::STATUS_NORMAL
				));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(100, 'minutes_new_failed');
		}

		return true;
	}
}
