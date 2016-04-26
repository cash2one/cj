<?php

class voa_c_api_addressbook_get_profile extends voa_c_api_addressbook_base {
	public function execute() {
		// 待返回的数据
		$data = array();
		if (empty($this->_params['uid'])) {
			$this->_set_errcode(voa_errcode_api_addressbook::ID_EMPTY_ERROR);
			return ;
		}

		// 初始化当前通讯录id
		$uid = $this->_params['uid'];

		//voa_s_oa_member

		$member = $this->_sev_member->fetch($uid);

		$fields = $this->_sev_member_field->fetch_by_id($uid);
		$member = array_merge($member, $fields);
		if (empty($member) || !$member['m_uid'] || $member['m_uid'] != $uid) {
			$this->_set_errcode(voa_errcode_api_addressbook::ITEM_EMPTY_ERROR);
		} else {
			$addressbook_format = $this->_addressbook_format($member);
			$this->_result = $addressbook_format;
		}

		return;
	}




}
