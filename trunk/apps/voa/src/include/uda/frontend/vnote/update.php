<?php
/**
 * 备忘相关的更新操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_vnote_update extends voa_uda_frontend_vnote_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 编辑备忘信息
	 */
	public function vnote_edit($vnote, $ccusers) {
		$vnp_id = (int)$this->_request->get('vnp_id');
		$post = array();
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
		$cculist = $servm->fetch_all_by_ids($ccuids);

		/** 获取需要删除的用户和新加入的用户 */
		$del_uids = array();
		$new_uids = array();
		if (!$this->_get_del_new_user($ccusers, $cculist, $del_uids, $new_uids)) {
			return false;
		}

		/** 数据入库 */
		$serv_vn = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$serv_p = &service::factory('voa_s_oa_vnote_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_m = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		try {
			$servm->begin();

			/** 更新备忘信息 */
			$serv_vn->update(array(
				'vn_subject' => rsubstr($post['vnp_message'], 81)
			), array('vn_id' => $vnote['vn_id']));

			/** 更新备忘详情信息 */
			$serv_p->update($post, array('vnp_id' => $vnp_id));

			/** 抄送人信息入库 */
			foreach ($new_uids as $uid) {
				$serv_m->insert(array(
					'vn_id' => $vnote['vn_id'],
					'm_uid' => $uid,
					'm_username' => $cculist[$uid]['m_username'],
					'vnm_status' => voa_d_oa_vnote_mem::STATUS_CC
				));
			}

			/** 清理需要删除的用户信息 */
			$ids = array();
			foreach ($del_uids as $uid) {
				$ids[] = $ccusers[$uid]['vnm_id'];
			}

			$ids && $serv_m->delete_by_ids($ids);

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->errmsg(150, 'vnote_new_failed');
			return false;
		}

		return true;
	}

	/**
	 * 取需要删除的uid和新增uid
	 * @param array $ccusers
	 * @param array $postusers
	 * @param array $deluids
	 * @param array $newuids
	 * @return boolean
	 */
	protected function _get_del_new_user($ccusers, $postusers, &$deluids, &$newuids) {
		foreach ($ccusers as $u) {
			if (empty($postusers[$u['m_uid']]) && $u['m_uid'] != startup_env::get('wbs_uid')) {
				$deluids[] = $u['m_uid'];
			}
		}

		foreach ($postusers as $u) {
			if (empty($ccusers[$u['m_uid']])) {
				$newuids[] = $u['m_uid'];
			}
		}

		return true;
	}
}
