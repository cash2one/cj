<?php
/**
 * 销售轨迹相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_footprint_insert extends voa_uda_frontend_footprint_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 新销售轨迹入库
	 * @param array $footprint
	 * @param array $cculist
	 * @throws Exception
	 * @return boolean
	 */
	public function footprint_new(&$footprint, &$cculist) {
		$gp2field = array(
			'time' => 'val_visittime',
			'subject' => 'val_subject',
			'type' => 'val_type'
		);
		if (!$this->_submit2table($gp2field, $footprint)) {
			return false;
		}

		/** 附件ids */
		$at_idstr = (string)$this->_request->get('at_ids');
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

		/** 抄送人uids */
		$uidstr = (string)$this->_request->get('carboncopyuids');
		$ccuids = array();
		if (!$this->chk_carboncopyuids($uidstr, $ccuids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array());
		/** 读取用户信息, 包括请假人和抄送人信息 */
		$ccuids[startup_env::get('wbs_uid')] = startup_env::get('wbs_uid');
		$cculist = $servm->fetch_all_by_ids($ccuids);

		/** 获取组新成员uid begin */
		/** 读取小组成员 */
		$serv_mt = &service::factory('voa_s_oa_footprint_team', array('pluginid' => startup_env::get('pluginid')));
		$team_users = $serv_mt->fetch_by_uid(startup_env::get('wbs_uid'), $ccuids);
		$team_uids = array();
		foreach ($team_users as $u) {
			$team_uids[$u['fpmt_to_uid']] = $u['fpmt_to_uid'];
		}

		$team_new_uids = array_diff($ccuids, $team_uids);


		/** 获取组新成员uid end */

		/** 数据入库 */
		$serv_fp = &service::factory('voa_s_oa_footprint', array('pluginid' => startup_env::get('pluginid')));
		$serv_m = &service::factory('voa_s_oa_footprint_mem', array('pluginid' => startup_env::get('pluginid')));
		$serv_fpat = &service::factory('voa_s_oa_footprint_attachment', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 报告标题信息入库 */
			$footprint = array_merge($footprint, array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'fp_visitweek' => rgmdate($footprint['fp_visittime'], 'W'),
				'fp_visitynd' => rgmdate($footprint['fp_visittime'], 'Y-n-d'),
				'fp_status' => voa_d_oa_footprint::STATUS_NORMAL
			));
			$fp_id = $serv_fp->insert($footprint, true);
			$footprint['fp_id'] = $fp_id;

			if (empty($fp_id)) {
				throw new Exception('footprint_new_failed');
			}

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				$serv_m->insert(array(
					'fp_id' => $fp_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'fpm_status' => voa_d_oa_footprint_mem::STATUS_CARBON_COPY
				));
			}

			/** 附件对应信息入库 */
			foreach ($attachs as $v) {
				$serv_fpat->insert(array(
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'fp_id' => $fp_id,
					'at_id' => $v['at_id'],
					'fpat_status' => voa_d_oa_footprint_attachment::STATUS_NORMAL
				));
			}

			/** 新的组成员入库 */
			foreach ($team_new_uids as $uid) {
				if ($uid == startup_env::get('wbs_uid')) {
					continue;
				}

				$serv_mt->insert(array(
					'fp_id' => $fp_id,
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username'),
					'fpmt_to_uid' => $uid
				));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'footprint_new_failed');
			return false;
		}

		return true;
	}

	/**
	 * 对轨迹进行回复操作
	 * @param array $footprint
	 * @param array &$post
	 * @return boolean
	 */
	public function footprint_reply($footprint, &$post) {
		$gp2field = array(
			'message' => 'val_message'
		);
		if (!$this->_submit2table($gp2field, $post)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_footprint_post', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();

			$post = array_merge($post, array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'fp_id' => $footprint['fp_id']
			));

			$fppt_id = $serv->insert($post, true);
			$post['fppt_id'] = $fppt_id;

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			$this->errmsg(100, 'footprint_reply_failed');
			return false;
		}

		return true;
	}
}
