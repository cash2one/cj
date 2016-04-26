<?php
/**
 * 展示用户通讯录信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_addressbook_show extends voa_c_frontend_addressbook_base {

	public function execute() {
		/** 获取用户信息 */
		$uid =(int)$this->request->get('uid');
		$uid = empty($uid) ? startup_env::get('wbs_uid') : $uid;

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $servm->fetch_by_uid($uid);

		/** 获取通讯录信息 */
		$servmf = &service::factory('voa_s_oa_member_field', array('pluginid' => 0));
		$member_field = $servmf->fetch_by_id($user['m_uid']);
		if (empty($member_field)) {
			$this->_error_message('数据错误, 请联系管理员');
		}

		/** 部门 */
		$departments = voa_h_cache::get_instance()->get('department', 'oa');

		/** 职位 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');

		// 屏蔽手机号
		if ('demo' == startup_env::get('domain')) {
			$user['m_mobilephone'] = substr($user['m_mobilephone'], 0, -4).'****';
			$ems = explode('@', $user['m_email']);
			$user['m_email'] = (4 < strlen($ems[0]) ? substr($ems[0], 0, -4) : $ems[0])."****@".$ems[1];
		}

		$this->view->set('member_field', $member_field);
		$this->view->set('department', $departments[$user['cd_id']]);
		$this->view->set('job', 0 < $user['cj_id'] ? $jobs[$user['cj_id']] : array());
		$this->view->set('user', $user);

		$p_set = voa_h_cache::get_instance()->get('plugin.member.setting', 'oa');
		$fields = isset($p_set['fields']) ? $p_set['fields'] : array();

		$field_data = array();
		foreach ($fields as $_k => $_v) {
			$value = '';
			if (is_numeric($_k)) {
				$value = $member_field['mf_ext'.$_k];
			} else {
				$value = $member_field['mf_'.$_k];
			}
			if (!$value) {
				continue;
			}
			$field_data[] = array(
				'name' => $_v['desc'],
				'value' =>  $value
			);
		}

		$this->view->set('field_data', $field_data);

		$this->_output('addressbook/show');
	}
}
