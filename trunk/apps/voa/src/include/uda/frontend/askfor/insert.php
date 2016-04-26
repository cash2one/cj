<?php
/**
 * 审批相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_insert extends voa_uda_frontend_askfor_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 审批数据入库
	 * @param array $askfor 审批主题信息
	 * @param array $post 审批详情信息
	 * @param array $mem 审批人信息
	 * @param array $cculist 抄送人信息
	 * @return boolean
	 */
	public function askfor_new(&$askfor) {
		/** 审批主题 */
		$subject = (string)$this->_request->get('subject');
		if (!$this->val_subject($subject)) {
			return false;
		}
		/** 审批内容 */
		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		// 上传的附件id
		$upload_attach_ids = (string)$this->_request->post('at_ids');
		$upload_attach_ids = trim($upload_attach_ids);
		// 检查附件id
		$attach_ids = array();
		// 判断是否上传了附件 且 系统是否允许上传图片
		if (!empty($upload_attach_ids) && !empty($this->_p_sets['upload_image'])) {

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
// 			// 上传的图片数
// 			$count = count($attach_ids);
// 			// 设置了最少上传图片数 且上传的图片数量小于要求的数
// 			if (!empty($this->_p_sets['upload_image_min_count']) && $count < $this->_p_sets['upload_image_min_count']) {
// 				$this->_error_message('至少要求上传 '.$this->_p_sets['upload_image_min_count'].' 张图片，您上传了 '.$count.' 张');
// 			}
// 			// 不能超出系统要求的上传数
// 			if (!empty($this->_p_sets['upload_image_max_count']) && $count > $this->_p_sets['upload_image_max_count']) {
// 				$this->_error_message('最多只允许上传 '.$this->_p_sets['upload_image_max_count'].' 张图片，您已上传了 '.$count.' 张');
// 			}
		}

		// 获取附件信息
		$attachs = array();
		if (!empty($attach_ids)) {
			$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
			$attachs = array();
			$attachs = $serv_at->fetch_by_conditions(array(
				'at_id' => array($attach_ids, '='),
				'm_uid' => startup_env::get('wbs_uid')
			));
		}

		$serv_afat = &service::factory('voa_s_oa_askfor_attachment', array('pluginid' => startup_env::get('pluginid')));

		/** 数据入库 */
		$servao = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$sercmt = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
		$servpc = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 申请信息入库 */
			$askfor = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'af_subject' => $subject,
				'af_message' => $message,
				'af_status' => voa_d_oa_askfor::STATUS_NORMAL
			);
			$af_id = $servao->insert($askfor, true);
			$askfor['af_id'] = $af_id;
			if (empty($af_id)) {
				throw new Exception('askfor_new_failed');
			}

			/** 审批流程ID */
			$aft_id = rintval($this->request->get('aft_id'));

			/** 取得审批流程 */
			$template = array();
			$uda  = &uda::factory('voa_uda_frontend_askfor_template_get');
			$uda->template_get($template);

			/** 审批人信息入库 */
			$afp_id = $servpc->insert(array(
				'af_id' => $af_id,
				'm_uid' => $template['m_uid'],
				'm_username' => $template['m_username'],
				'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
			), true);
			if (empty($afp_id)) {
				throw new Exception('askfor_proc_new_failed');
			}

			/** 把进度 id 更新到审批表 */
			$askfor['afp'] = $afp_id;
			$servao->update(array(
				'afp_id' => $afp_id
			), array('af_id' => $af_id));

			/** 自定义字段入库 */
			$cols = (array)$this->request->get('cols');
       
			if (!empty($cols)) {
				$servcd = &service::factory('voa_s_oa_askfor_customdata', array('pluginid' => startup_env::get('pluginid')));
				$cols_data = array();
				foreach ($cols as $k =>$col) {
					foreach ($template['cols'] as $v) {
						if ($k == $v['afcc_id']) {
							$cols_data[] = array(
								'af_id' => $af_id,
								'field' => $v['field'],
								'name' => $v['name'],
								'value' => $col,
							);
						}
					}
				}
				$servcd->insert_multi($cols_data);
			}

			// 附件入库
			foreach ($attachs as $v) {
				$serv_afat->insert(array(
					'af_id' => $af_id,
					'afc_id' => 0,// 标记为任务的图片
					'at_id' => $v['at_id'],
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'afat_status' => voa_d_oa_askfor_attachment::STATUS_NORMAL
				));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(152, 'askfor_new_failed');
			return false;
		}

		return true;
	}
}
