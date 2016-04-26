<?php
/**
 * voa_c_api_talk_post_register
 * 注册
 * $Author$
 * $Id$
 */

class voa_c_api_talk_post_register extends voa_c_api_talk_abstract {

	public function execute() {

		try {
			// 获取uid
			$uid = (int)$this->_get('uid');
			$member = array();
			if (empty($uid)) {
				$serv_m = &service::factory('voa_s_oa_member');
				$lastmem = $serv_m->fetch_all_by_conditions(array(), array('m_uid' => 'DESC'), 0, 1);
				$rand_uid = rand(1, $lastmem['m_uid'] - 1);
				$rand_mem = $serv_m->fetch_all_by_conditions(array('m_uid' => array($rand_uid, '>')), 0, 1);
			} else {
				$uda_mem = new voa_uda_frontend_member_get();
				if (!$uda_mem->member_by_uid($uid, $member)) {
					return true;
				}
			}

			// 先判断是否有账号信息
			$tv_uid = $this->session->get('tv_uid');
			$viewer = array();
			if (!$this->_get_viewer($tv_uid, $viewer)) {
				// 注册用户
				$uda = new voa_uda_frontend_talk_register();
				$uda->execute($this->_params, $viewer);
			}

			// 获取当前用户记录
			$viewerlist = array();
			$uda_lv = new voa_uda_frontend_talk_listlastviewer();
			$uda_lv->execute(array('uid' => $uid, 'tv_uid' => $tv_uid), $viewerlist);

			// 统计分享被咨询数
			$goodsid = (int)$this->_get('goodsid');
			if (!empty($goodsid) && !empty($uid)) {
				$serv_tsc = new voa_s_oa_travel_sharecount();
				$serv_tsc->update_by_conds(array('goods_id' => $goodsid, 'uid' => $uid), array('`inquirycount`=`inquirycount`+?' => 1));
			}

			// 如果没有聊天记录
			if (empty($viewerlist)) {
				// 新增 lastview
				$uda_view = new voa_uda_frontend_talk_addlastview();
				$lastview = array();

				logger::error(var_export($viewer, true));
				$goodsid = (int)$this->_get('goodsid');
				$uda_view->execute(array(
					'uid' => $uid,
					'tv_uid' => $viewer['tv_uid'],
					'message' => '',
					'lastts' => startup_env::get('timestamp'),
					'goodsid' => $goodsid
				), $lastview);
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		// 设置 cookie
		$this->session->set('tv_uid', $viewer['tv_uid']);
		$viewer['avatar'] = voa_h_user::avatar($viewer['tv_uid'], $viewer);
		$this->_result = $viewer;

		return true;
	}

	/**
	 *
	 * @param int $uid 客户uid
	 * @param array $viewer 客户信息
	 * @return boolean
	 */
	protected function _get_viewer($uid, &$viewer) {

		// 如果 uid 为空
		$uid = (int)$uid;
		if (empty($uid)) {
			return false;
		}

		// 调用 uda 读取客户信息
		$uda = new voa_uda_frontend_talk_getviewer();
		$viewers = array();
		$uda->execute(array('tv_uid' => $uid), $viewers);
		// 如果客户信息为空
		if (empty($viewers)) {
			return false;
		}

		// 取客户信息
		$viewer = array_pop($viewers);

		return true;
	}

}
