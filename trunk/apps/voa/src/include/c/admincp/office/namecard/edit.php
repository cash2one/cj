<?php
/**
 * voa_c_admincp_office_namecard_edit
 * 企业后台/微办公管理/微名片/名片编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_edit extends voa_c_admincp_office_namecard_base {

	public function execute() {

		$nc_id = $this->request->get('nc_id');
		$nc_id = rintval($nc_id, false);

		$namecard = $this->_service_single('namecard', $this->_module_plugin_id, 'fetch_by_id', $nc_id);
		if (empty($namecard)) {
			$this->message('error', '指定名片不存在或已被删除');
		}

		$this->_member_list = $this->_get_member_by_uids(array($namecard['m_uid']));
		$this->_job_list = $this->_get_job_by_ncj_ids($this->_module_plugin_id, array($namecard['ncj_id']));
		$this->_company_list = $this->_get_company_by_ncc_ids($this->_module_plugin_id, $namecard['ncc_id']);
		$this->_folder_list = $this->_get_folder_by_ncf_ids($this->_module_plugin_id, $namecard['ncf_id']);

		$namecard = $this->_format_namecard($namecard);

		if ($this->_is_post()) {
			$update = $this->_namecard_check_field($_POST, $namecard);
			if (empty($update)) {
				$this->message('error', '名片信息未发生改变无须提交');
			}
			if (!is_array($update)) {
				$this->message('error', $update);
			}
			$this->_service_single('namecard', $this->_module_plugin_id, 'update', $update, array('nc_id' => $nc_id));
			$this->message('success', '编辑名片信息操作完毕', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
		}

		$this->view->set('namecard', $namecard);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('nc_id' => $nc_id)));

		$this->output('office/namecard/edit_form');

	}

	protected function _namecard_check_field($new, $old) {
		$update = array();
		if (isset($new['ncf_name']) && $new['ncf_name'] != $old['_folder']) {
			//群组名改变
		}
		if (isset($new['ncc_name']) && $new['ncc_name'] != $old['_company']) {
			//公司名称改变
		}
		if (isset($new['ncj_name']) && $new['ncj_name'] != $old['_job']) {
			//职务名称改变
		}
		if (isset($new['nc_realname']) && $new['nc_realname'] != $old['nc_realname']) {
			if ($new['nc_realname'] && !validator::is_len_in_range($new['nc_realname'], 0, 50)) {
				return '真实姓名长度不能超过 50字节';
			}
			$update['nc_realname'] = $new['nc_realname'];
		}
		if (isset($new['nc_mobilephone']) && $new['nc_mobilephone'] != $old['nc_mobilephone']) {
			if ($new['nc_mobilephone'] && !validator::is_mobile($new['nc_mobilephone'])) {
				return '请正确填写手机号码';
			}
			$update['nc_mobilephone'] = $new['nc_mobilephone'];
		}
		if (isset($new['nc_wxuser']) && $new['nc_wxuser'] != $old['nc_wxuser']) {
			if ($new['nc_wxuser'] && !validator::is_len_in_range($new['nc_wxuser'], 0, 40)) {
				return '微信号长度不能超过 40字节';
			}
			$update['nc_wxuser'] = $new['nc_wxuser'];
		}
		if (isset($new['nc_gender']) && $new['nc_gender'] != $old['nc_gender']) {
			if (!isset($this->_namecard_gender[$new['nc_gender']])) {
				$new['nc_gender'] = 0;
			}
			$update['nc_gender'] = $new['nc_gender'];
		}
		if (isset($new['nc_active']) && $new['nc_active'] != $old['nc_active']) {
			if (!isset($this->_namecard_active[$new['nc_active']])) {
				$new['nc_active'] = 0;
			}
			$update['nc_active'] = $new['nc_active'];
		}
		if (isset($new['nc_telephone']) && $new['nc_telephone'] != $old['nc_telephone']) {
			if ($new['nc_telephone'] && !validator::is_phone($new['nc_telephone'])) {
				return '请正确填写电话号码';
			}
			$update['nc_telephone'] = $new['nc_telephone'];
		}
		if (isset($new['nc_email']) && $new['nc_email'] != $old['nc_email']) {
			if ($new['nc_email'] && !validator::is_email($new['nc_email'])) {
				return '请正确填写邮箱地址';
			}
			$update['nc_email'] = $new['nc_email'];
		}
		if (isset($new['nc_qq']) && $new['nc_qq'] != $old['nc_qq']) {
			if ($new['nc_qq'] && !validator::is_qq($new['nc_qq'])) {
				return '请正确填写QQ号码';
			}
			$update['nc_qq'] = $new['nc_qq'];
		}
		if (isset($new['nc_birthday']) && $new['nc_birthday'] != $old['nc_birthday']) {
			if ($new['nc_birthday'] != '0000-00-00' && $new['nc_birthday'] && !validator::is_date($new['nc_birthday'])) {
				return '请正确填写生日日期';
			}
			if (!$new['nc_birthday']) {
				$new['nc_birthday'] = '0000-00-00';
			}
			$update['nc_birthday'] = $new['nc_birthday'];
		}
		if (isset($new['nc_address']) && $new['nc_address'] != $old['nc_address']) {
			if ($new['nc_address'] && !validator::is_len_in_range($new['nc_address'], 0, 250)) {
				return '地址长度不能超过250字节';
			}
			$update['nc_address'] = $new['nc_address'];
		}
		if (isset($new['nc_remark']) && $new['nc_remark'] != $old['nc_remark']) {
			if ($new['nc_remark'] && !validator::is_len_in_range($new['nc_remark'], 0, 250)) {
				return '备注文字长度不能超过250字节';
			}
			$update['nc_remark'] = $new['nc_remark'];
		}

		return $update;

	}

}
