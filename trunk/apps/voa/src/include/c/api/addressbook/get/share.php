<?php
/**
 * 通讯录分享
 * /api/testing/get/share/?uid=123&fields=username,gender
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_addressbook_get_share extends voa_c_api_addressbook_base {

	public function execute() {

		$fieldstr = $this->_get('fields', '');
		$uid = (int)$this->_get('uid', 0);
		if (empty($uid)) {
			$uid = $this->_member['m_uid'];
		}

		$url = '';
		if (!$this->_addressbook_uda_get->share($url, $uid, $fieldstr)) {
			$this->_set_errcode(voa_errcode_api_addressbook::ADDRESSBOOK_SHARE_FAILED);
			return true;
		}

		$this->_result = array('url' => $url);
		return true;
	}

}
