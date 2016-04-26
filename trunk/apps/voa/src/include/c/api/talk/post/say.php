<?php
/**
 * voa_c_api_talk_post_say
 * 发言
 * $Author$
 * $Id$
 */

class voa_c_api_talk_post_say extends voa_c_api_talk_abstract {

	public function execute() {

		// 销售 uid
		$uid = startup_env::get('wbs_uid');
		if (empty($uid)) {
			$uid = $this->_get('uid');
			$tv_uid = $this->session->get('tv_uid');
		} else {
			// 客户 tv_uid
			$tv_uid = $this->_get('tv_id');
		}

		// uid
		$this->_params['uid'] = $uid;
		$this->_params['tv_uid'] = $tv_uid;

		try {
			// 聊天记录入库
			$uda = new voa_uda_frontend_talk_say();
			$say = array();
			if (!$uda->execute($this->_params, $say)) {
				return true;
			}

			// 如果是客户, 则更新 lastview
			if (voa_d_oa_talk_wechat::TYPE_VIEWER == $say['tw_type']) {
				$uda_view = new voa_uda_frontend_talk_updatelastview();
				$lastview = array();
				$goods_id = (int)$this->_get('goods_id');

				$uda_view->execute(array(
					'uid' => $uid,
					'tv_uid' => $say['tv_uid'],
					'lastts' => startup_env::get('timestamp'),
					'goodsid' => $goods_id,
					'message' => $say['message']
				), $lastview);

				// 如果读取超过10分钟未读, 则
				if ($lastview['viewts'] + 600 < startup_env::get('timestamp')) {
					$uda_nt = new voa_uda_frontend_talk_wxqynotice();
					$uda_nt->set_session($this->session);
					$notice = array();
					$goods_id = (int)$this->_get('goods_id');
					$uda_nt->execute(array('uid' => $uid, 'tv_uid' => $say['tv_uid'], 'goods_id' => $goods_id), $notice);
					$say['sendnotice'] = 1;
				}
			}
		} catch (Exception $e) {
			logger::error($e);
			$this->_set_errcode($e->getMessage());
		}

		$this->_result = $say;

		return true;
	}

}
