<?php
/**
 * 活动/产品相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_productive_insert extends voa_uda_frontend_productive_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 执行活动/产品计划任务, 分发
	 * @param array $taskid
	 */
	public function run_task($task) {

		/** 读取 */
		$serv_task = &service::factory('voa_s_oa_productive_tasks');
		if (empty($task)) {
			return true;
		}

		/** 切分任务中的店铺id */
		$csp_ids = explode(',', $task['it_csp_id_list']);
		if (empty($csp_ids)) {
			return true;
		}

		/** 读取用户信息 */
		$user = voa_h_user::get($task['it_assign_uid']);

		/** 组织sql */
		$sqls = array();
		foreach ($csp_ids as $_id) {
			$sqls[] = array(
				'it_id' => $task['it_id'],
				'sponsor_uid' => $task['it_submit_uid'],
				'm_uid' => $task['it_assign_uid'],
				'm_username' => $user['m_username'],
				'csp_id' => $_id,
				'pt_status' => voa_d_oa_productive::STATUS_WAITING
			);
		}

		/** 信息入库 */
		$serv_pt = &service::factory('voa_s_oa_productive');
		try {
			$serv_pt->begin();

			/** 信息入库 */
			$serv_pt->insert_multi($sqls);

			$serv_pt->commit();
		} catch (Exception $e) {
			$serv_pt->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'productive_task_failed');
			return false;
		}

		return true;
	}

	/**
	 * 新增活动/产品计划
	 * @param array $params 数据数组
	 * @param array $productive 活动/产品数据
	 */
	public function productive_new($params, &$productive) {

		$this->_params = $params;
		$gp2field = array(
			'csp_id' => 'val_csp_id'
		);
		if (!$this->_submit2table($gp2field, $productive)) {
			 return false;
		}

		/** 检查用户信息是否为空 */
		$user = $this->get('_user', array());
		if (empty($user)) {
			$this->errmsg(150, 'user_is_not_exist');
			return false;
		}

		/** 数据入库 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_pt->begin();

			$productive['m_uid'] = $user['m_uid'];
			$productive['m_username'] = $user['m_username'];
			$productive['pt_id'] = $serv_pt->insert($productive, true);

			$serv_pt->commit();
		} catch (Exception $e) {
			$serv_pt->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'productive_tasknew_failed');
			return false;
		}

		return true;
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
	public function productive_edit___($params, &$productive, &$memlist, &$cculist) {

		$this->_params = $params;
		/** 验证 */
		$gp2field = array(
			'csp_id' => 'val_csp_id',
			'note' => 'val_note',
			'longitude' => 'val_longitude',
			'latitude' => 'val_latitude'
		);
		if (!$this->_submit2table($gp2field, $productive)) {
			return false;
		}

		/** 读取店铺所在地区id */
		$shops = voa_h_cache::get_instance()->get('shop', 'oa');
		$cr_id = $shops[$productive['csp_id']]['cr_id'];

		/** 验证打分项 */
		$scorestr = (string)$this->get('scores');
		$item_scores = array();
		if (!$this->chk_item_scores($scorestr, $item_scores)) {
			return false;
		}

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
			'm_uid' => startup_env::get('wbs_uid')
		));

		/** 验证目标人 */
		$mem_uidstr = (string)$this->get('mem_uids');
		$mem_uids = array();
		if (!$this->chk_uids($mem_uidstr, $mem_uids)) {
			return false;
		}

		/** 验证抄送人 */
		$cc_uidstr = (string)$this->get('cc_uids');
		$cc_uids = array();
		if (!$this->chk_uids(cc_uidstr, $cc_uids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array());
		/** 读取用户信息, 包括接收人和抄送人信息 */
		$cc_uids[startup_env::get('wbs_uid')] = startup_env::get('wbs_uid');
		unset($mem_uids[startup_env::get('wbs_uid')]);
		$all_uids = array_merge($mem_uids, $cc_uids);
		$user_list = $servm->fetch_all_by_ids($all_uids);

		/** 数据入库 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$serv_ptat = &service::factory('voa_s_oa_productive_attachment', array('pluginid' => startup_env::get('pluginid')));
		$serv_score = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$serv_mem = &service::factory('voa_s_oa_productive_mem', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();

			$productive = array_merge($productive, array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username')
			));
			/** 活动/产品主题信息入库 */
			$pt_id = $serv_pt->insert($productive, true);
			$productive['pt_id'] = $pt_id;

			/** 附件信息入库 */
			$pt_ats = array();
			foreach ($attachs as $_at) {
				$pt_ats[] = array(
					'm_uid' => $_at['m_uid'],
					'pt_id' => $pt_id,
					'at_id' => $_at['at_id']
				);
			}

			$serv_ptat->insert_multi($pt_ats);

			/** 评分入库 */
			$pt_scores = array();
			$ymd = rgmdate(startup_env::get('timestamp'), 'Ymd');
			$total = 0;
			foreach ($item_scores as $_id => $_score) {
				$pt_scores[$_id] = array(
					'm_uid' => startup_env::get('wbs_uid'),
					'cr_id' => $cr_id,
					'pt_id' => $pt_id,
					'pti_id' => $_id,
					'ptsr_score' => $_score,
					'ptsr_date' => $ymd,
					'ptsr_type' => voa_d_oa_productive_score::TYPE_DATE,
					'csp_id' => $productive['csp_id']
				);
				$total += $_score;
			}

			$cur_score = current($pt_scores);
			$cur_score['pti_id'] = 0;
			$cur_score['ptsr_score'] = $total;
			$pt_scores[] = $cur_score;
			$serv_score->insert_multi($pt_scores);

			/** 目标人/抄送人信息入库 */
			$pt_mems = array();
			foreach ($mem_uids as $_uid) {
				if (!array_key_exists($_uid, $user_list)) {
					continue;
				}

				$memlist[] = $user_list[$_uid];
				$pt_mems[] = array(
					'pt_id' => $pt_id,
					'm_uid' => $_uid,
					'm_username' => $user_list[$_uid]['m_username'],
					'ptm_status' => voa_d_oa_productive_mem::STATUS_NORMAL
				);
			}

			foreach ($cc_uids as $_uid) {
				if (!array_key_exists($_uid, $user_list)) {
					continue;
				}

				$cculist[] = $user_list[$_uid];
				$pt_mems[] = array(
					'pt_id' => $pt_id,
					'm_uid' => $_uid,
					'm_username' => $user_list[$_uid]['m_username'],
					'ptm_status' => voa_d_oa_productive_mem::STATUS_CC
				);
			}

			$serv_mem->insert_multi($pt_mems);

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'productive_new_failed');
			return false;
		}

		return true;
	}
}
