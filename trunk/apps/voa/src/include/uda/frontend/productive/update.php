<?php
/**
 * 活动/产品相关的编辑操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_productive_update extends voa_uda_frontend_productive_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 活动/产品入库
	 * @param array $params 数据数组
	 * @param array &$productive 活动/产品数据
	 * @param array &$memlist 接收人数据
	 * @param array &$cculist 抄送人信息
	 * @throws Exception
	 * @return boolean
	 */
	public function productive_edit($params, &$productive, &$memlist, &$cculist) {

		$this->_params = $params;
		$user = $this->get('wbs_user');
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));

		/** 根据 csp_id 读取活动/产品记录 */
		$csp_id = (int)$this->get('csp_id', 0);
		if ($csp_id != $productive['csp_id']) {
			$pt_list = $serv_pt->fetch_by_csp_id_status($csp_id, array(
				voa_d_oa_productive::STATUS_DOING,
				voa_d_oa_productive::STATUS_WAITING
			));
			foreach ($pt_list as $_pt) {
				if ($user['m_uid'] == $_pt['m_uid']) {
					$productive = $_pt;
					break;
				}
			}
		}

		/** 取商家信息 */
		$shops = voa_h_cache::get_instance()->get('shop', 'oa');
		$cur_shop = $shops[$productive['csp_id']];

		/** 验证目标人 */
		$mem_uidstr = (string)$this->get('mem_uids');
		$mem_uids = array();
		if (!$this->chk_uids($mem_uidstr, $mem_uids)) {
			return false;
		}

		/** 验证抄送人 */
		$cc_uidstr = (string)$this->get('cc_uids');
		$cc_uids = array();
		if (!$this->chk_uids($cc_uidstr, $cc_uids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		/** 读取用户信息, 包括接收人和抄送人信息 */
		$cc_uids[$user['m_uid']] = $user['m_uid'];
		unset($mem_uids[$user['m_uid']]);
		$all_uids = array_merge($mem_uids, $cc_uids);
		$user_list = $servm->fetch_all_by_ids($all_uids);

		/** 数据入库 */
		$serv_ptat = &service::factory('voa_s_oa_productive_attachment', array('pluginid' => startup_env::get('pluginid')));
		$serv_score = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$serv_mem = &service::factory('voa_s_oa_productive_mem', array('pluginid' => startup_env::get('pluginid')));
		$serv_task = &service::factory('voa_s_oa_productive_tasks', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();

			/** 更新活动/产品信息 */
			$serv_pt->update(array('pt_status' => voa_d_oa_productive::STATUS_DONE), array('pt_id' => $productive['pt_id']));

			/** 总评分入库 */
			$pt_score = array(
				'm_uid' => $user['m_uid'],
				'cr_id' => $cur_shop['cr_id'],
				'pt_id' => $productive['pt_id'],
				'pti_id' => 0,
				'ptsr_score' => $this->get('total'),
				'ptsr_date' => rgmdate(startup_env::get('timestamp'), 'Ymd'),
				'ptsr_type' => voa_d_oa_productive_score::TYPE_DATE,
				'csp_id' => $productive['csp_id']
			);

			$serv_score->insert($pt_score);

			/** 目标人/抄送人信息入库 */
			$pt_mems = array();
			foreach ($mem_uids as $_uid) {
				if (!array_key_exists($_uid, $user_list)) {
					continue;
				}

				/** 剔除抄送人 */
				unset($cc_uids[$_uid]);

				$memlist[$_uid] = $user_list[$_uid];
				$pt_mems[] = array(
					'pt_id' => $productive['pt_id'],
					'm_uid' => $_uid,
					'm_username' => $user_list[$_uid]['m_username'],
					'ptm_status' => voa_d_oa_productive_mem::STATUS_NORMAL
				);
			}

			foreach ($cc_uids as $_uid) {
				if (!array_key_exists($_uid, $user_list)) {
					continue;
				}

				$cculist[$_uid] = $user_list[$_uid];
				$pt_mems[] = array(
					'pt_id' => $productive['pt_id'],
					'm_uid' => $_uid,
					'm_username' => $user_list[$_uid]['m_username'],
					'ptm_status' => voa_d_oa_productive_mem::STATUS_CC
				);
			}

			$serv_mem->insert_multi($pt_mems);

			/** 更新任务完成数 */
			if (0 < $productive['ptt_id']) {
				$serv_task->productive_fin(array($productive['ptt_id']));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'productive_new_failed');
			return false;
		}

		return true;
	}

	/**
	 * 活动/产品打分操作
	 * @param array $params 参数
	 * @param array $item_score 打分详情
	 * @param array $item_edscore 旧数据
	 */
	public function productive_score_edit($params, &$item_score, &$item_edscore = array()) {

		$this->_params = $params;
		$user = $this->get('wbs_user');
		/** 验证附件id */
		$at_idstr = (string)$this->get('at_ids');
		$at_ids = array();
		if (!$this->chk_at_ids($at_idstr, $at_ids)) {
			return false;
		}

		/** 读取附件信息 */
		$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		$attachs = array();
		$attachs = $serv_at->fetch_by_conditions(array(
			'at_id' => array($at_ids, '='),
			'm_uid' => $user['m_uid']
		));

		/** 读取附件信息 */
		$serv_ptat = &service::factory('voa_s_oa_productive_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_ptat->fetch_by_pt_id_pti_id($params['pt_id'], $params['pti_id']);
		$del_ptat_ids = array();
		foreach ($attach_list as $_at) {
			if (array_key_exists($_at['at_id'], $attachs)) {
				unset($attachs[$_at['at_id']]);
				continue;
			}

			$del_ptat_ids[] = $_at['ptat_id'];
		}

		/** 分数检查 */
		$score = (int)$this->get('score', 0);
		/** 问题信息 */
		$message = (string)$this->get('message', '');
		// 判断当前打分项是否有固定分值
		if (0 < $this->_items[$params['pti_id']]['pti_fix_score']) {
			$score = $this->_items[$params['pti_id']]['pti_fix_score'];
			if (empty($message)) {
				$this->errmsg(100, $this->_sets['score_title_describe'].'不能为空');
				return false;
			}
		}

		/** 数据入库 */
		$serv_score = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_score->begin();

			/** 打分信息入库 */
			$item_score = array(
				'ptsr_score' => $score,
				'ptsr_message' => $message,
				'm_uid' => $user['m_uid'],
				'cr_id' => $params['cr_id'],
				'csp_id' => $params['csp_id'],
				'pt_id' => $params['pt_id'],
				'pti_id' => $params['pti_id'],
				'ptsr_date' => rgmdate(startup_env::get('timestamp'), 'Ymd'),
				'ptsr_type' => voa_d_oa_productive_score::TYPE_DATE,
				'ptsr_status' => 0 < $score ? voa_d_oa_productive_score::STATUS_DONE : voa_d_oa_productive_score::STATUS_DOING
			);

			if (!empty($item_edscore)) {
				$serv_score->update($item_score, array('ptsr_id' => $item_edscore['ptsr_id']));
				$item_score['ptsr_id'] = $item_edscore['ptsr_id'];
			} else {
				$item_score['ptsr_id'] = $serv_score->insert($item_score, true);
			}

			/** 清除已存在附件 */
			if (!empty($del_ptat_ids)) {
				$serv_ptat->delete_by_ids($del_ptat_ids);
			}

			/** 附件信息入库 */
			$left_ats = 5 - count($attach_list) + count($del_ptat_ids);
			$pt_ats = array();
			foreach ($attachs as $_at) {
				if (0 >= $left_ats --) {
					break;
				}

				$pt_ats[] = array(
					'm_uid' => $_at['m_uid'],
					'pt_id' => $item_score['pt_id'],
					'pti_id' => $item_score['pti_id'],
					'ptsr_id' => $item_score['ptsr_id'],
					'at_id' => $_at['at_id']
				);
			}

			$serv_ptat->insert_multi($pt_ats);

			$serv_score->commit();
		} catch (Exception $e) {
			$serv_score->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'productive_socre_new_failed');
			return false;
		}

		return true;
	}
}
