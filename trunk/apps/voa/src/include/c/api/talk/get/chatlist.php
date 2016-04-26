<?php
/**
 * voa_c_api_talk_get_chatlist
 * 聊天信息列表
 * $Author$
 * $Id$
 */

class voa_c_api_talk_get_chatlist extends voa_c_api_talk_abstract {

	public function execute() {


		$chatlist = array();
		$uda = new voa_uda_frontend_talk_chatrcd();
		if (!$uda->execute($this->_params, $chatlist)) {
			return true;
		}

		$chatlist = rhtmlspecialchars($chatlist);
		ksort($chatlist);

		// 更新 lastview
		$uda_view = new voa_uda_frontend_talk_updatelastview();
		$lastview = array();
		$uda_view->execute(array(
			'uid' => $this->_member['m_uid'],
			'tv_uid' => $this->_params['tv_id'],
			'viewts' => startup_env::get('timestamp'),
			'newct'	=>	0,
		), $lastview);

		// 更新 lastview
		$viewer = new voa_uda_frontend_talk_getviewer();
		$newguest = $viewer->newguest($this->_member['m_uid']);

		$this->_result = array(
			'data'	=>	array_values($chatlist),
			'newguest'	=> $newguest,
		);
		return true;
	}

}

