<?php
/**
 * 备忘相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_vnote_insert extends voa_uda_frontend_vnote_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 对备忘的回复
	 * @param array $post
	 * @return boolean
	 */
	public function vnote_reply(&$post) {
		/** 数据和处理函数对应关系 */
		$gps = array(
			'message' => 'val_message'
		);
		if (!$this->_submit2table($gps, $post)) {
			return false;
		}

		/** 审批 id */
		$vn_id = intval($this->_request->get('vn_id'));

		/** 获取备忘信息 */
		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$vnote = $serv->fetch_by_id($vn_id);
		if (empty($vn_id) || empty($vnote)) {
			$this->errmsg(100, 'vnote_is_not_exists');
			return false;
		}

		/** 获取备忘用户 */
		$serv_m = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		$mem = $serv_m->fetch_by_conditions(array('vn_id' => $vn_id, 'm_uid' => startup_env::get('wbs_uid')));
		if (empty($mem)) {
			$this->errmsg(101, 'no_privilege');
			return false;
		}

		/** 评论信息入库 */
		$serv_pt = &service::factory('voa_s_oa_vnote_post', array('pluginid' => startup_env::get('pluginid')));
		$post = array_merge($post, array(
			'vn_id' => $vn_id,
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username')
		));
		$serv_pt->insert($post);

		return true;
	}

	/**
	 * 新备忘入库
	 * @param unknown $vnote
	 * @param unknown $post
	 * @param unknown $mem
	 * @param unknown $cculist
	 * @throws Exception
	 * @return boolean
	 */
	public function vnote_new(&$vnote, &$post, &$cculist) {
		/** 数据和处理函数对应关系 */
		$gps = array(
			'message' => 'val_message'
		);
		if (!$this->_submit2table($gps, $post)) {
			return false;
		}

		/** 抄送人 */
		$uidstr = (string)$this->_request->get('carboncopyuids');
		$ccuids = array();
		if (!$this->chk_carboncopyuids($uidstr, $ccuids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array());
		/** 读取用户信息, 包括请假人和抄送人信息 */
		$ccuids[startup_env::get('wbs_uid')] = startup_env::get('wbs_uid');
		$cculist = $servm->fetch_all_by_ids($ccuids);

		/** 数据入库 */
		$serv_vn = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$serv_p = &service::factory('voa_s_oa_vnote_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_m = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();
			/** 备忘标题信息入库 */
			$vnote = array(
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'vn_subject' => rsubstr($post['vnp_message'], 81),
				'vn_status' => voa_d_oa_vnote::STATUS_NORMAL,
				'vn_created' => startup_env::get('timestamp')
			);
			$vn_id = $serv_vn->insert($vnote, true);
			$vnote['vn_id'] = $vn_id;

			if (empty($vn_id)) {
				throw new Exception('vnote_new_failed');
			}

			/** 备忘信息入库 */
			$post = array_merge($post, array(
				'vn_id' => $vn_id,
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'vnp_first' => voa_d_oa_vnote_post::FIRST_YES
			));
			$vnp_id = $serv_p->insert($post, true);

			if (empty($vnp_id)) {
				throw new Exception('vnote_new_failed');
			}

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				$serv_m->insert(array(
					'vn_id' => $vn_id,
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'vnm_status' => voa_d_oa_vnote_mem::STATUS_CC
				));
			}

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'vnote_new_failed');
			return false;
		}

		return true;
	}
}
