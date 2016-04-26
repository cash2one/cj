<?php
/**
 * get.php
 * addressbook 通讯录
 * /api/testing/get/list/?aaa=bbb&ccc=dddd0l&_api_force=0
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_addressbook_put_profile extends voa_c_api_addressbook_base {

	public function execute() {

		// 接受的参数
		$fields = array(
			'uid' => array('type' => 'int', 'required' => true),
			'qq' => array('type' => 'number', 'required' => false),
			'gender' => array('type' => 'string', 'required' => false),
			'birthday' => array('type' => 'string', 'required' => false),
			'weixinid' => array('type' => 'string', 'required' => false),
			'remark' => array('type' => 'string', 'required' => false),
			'telephone' => array('type' => 'string', 'required' => false),
			'address' => array('type' => 'string', 'required' => false),
			'newpw' => array('type' => 'string', 'required' => false)
		);

		// 参数的基本检查和过滤
		$this->_check_params($fields);

		// 待返回的数据
		$data = array();
		if (empty($this->_params['uid'])) {
			$this->_set_errcode(voa_errcode_api_addressbook::ID_EMPTY_ERROR);
			return ;
		}

		if (!empty($this->_params['newpw'])) {
			// 请求了修改密码，检查密码格式是否正确
			if (!validator::is_md5($this->_params['newpw'])) {
				$this->_set_errcode(voa_errcode_api_addressbook::NEWPW_FORMAT_ERROR);
				return false;
			}
		}

		// 初始化当前通讯录id
		$uid = $this->_params['uid'];

		if ($uid) {
			$addressbook = array();
			//$this->_addressbook_uda_get->addressbook($cab_id, $addressbook);
			$addressbook = $this->_sev_member->fetch($uid);
			// 提交请求
			//$submit = $this->_params;
			$submit = $addressbook;
			$field_maps_flip = array_flip($this->_field_maps);

			// 允许修改的字段
			$allow_field	=	array('address', 'qq', 'gender', 'birthday', 'weixinid', 'remark', 'telephone');
			foreach ($this->_params as $key => $val) {
				if (in_array($key, $allow_field)) {
					if (!empty($field_maps_flip[$key])) {
						$submit[$field_maps_flip[$key]] = $val;
					}
				}
			}
			$update_member = array();
			$update_member_fields = array();
			foreach ($submit as $key => $val) {
				if (substr($key, 0, 3) == 'mf_') {
					$update_member_fields[$key] = $val;
				} else {
					$update_member[$key] = $val;
				}
			}


			if (!empty($this->_params['newpw'])) {
				// 需要修改密码
				$uda_member_update = &uda::factory('voa_uda_frontend_member_update');
				if (!$uda_member_update->pwd_modify($addressbook['m_uid'], $this->_params['newpw'], false)) {
					$this->_errcode = $uda_member_update->errno;
					$this->_errmsg = $uda_member_update->error;
					return false;
				}
			}

			if (!empty($update_member)) {
				$this->_sev_member->update($update_member, $uid);
			}
			if (!empty($update_member_fields)) {
				$this->_sev_member_field->update($update_member_fields, $uid);
			}
			/*
			$updated = array();
			if ($this->_addressbook_uda_update->update($addressbook, $submit, $updated)) {
				$this->_result = '';
				//$this->message('success', $this->_addressbook_uda_update->error, $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
			} else {
				$this->_set_errcode(voa_errcode_api_addressbook::UPDATE_ERROR);
				//$this->message('error', $this->_addressbook_uda_update->error);
			}*/
		}

		return;
	}




}
