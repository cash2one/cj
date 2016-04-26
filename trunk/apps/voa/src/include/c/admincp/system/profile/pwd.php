<?php
/**
 * voa_c_admincp_system_profile_pwd
 * 企业后台/系统设置/我的资料/修改密码
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_profile_pwd extends voa_c_admincp_system_profile_base {

	public function execute() {

		$ca_id = $this->_user['ca_id'];

		// 管理员详情
		$adminerDetail = $this->_adminer_detail($ca_id);
		// 管理员不存在
		if (!$adminerDetail || !$adminerDetail['cag_id']) {
			$this->message('error', '对不起，指定的管理员不存在 或 已被删除');
		}

		// 提交修改动作
		if ( $this->_is_post() ) {
			$this->_response_submit_edit($ca_id);
		}

		$adminerDetail['_lastlogin'] = $adminerDetail['ca_lastlogin'] ? rgmdate($adminerDetail['ca_lastlogin'], 'Y-m-d H:i') : '---';
		$this->view->set('adminer', $adminerDetail);
		$this->view->set('actionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

		$this->output('system/profile/pwd');
	}

	/**
	 * 响应提交添加或者编辑动作
	 * /system/adminer/base
	 * @param number $ca_id
	 * @param boolean $returnMessage 操作成功后是否返回提示信息
	 */
	protected function _response_submit_edit($ca_id, $returnMessage = true) {

		// 管理员详情
		$adminerDetail = $this->_adminer_detail($ca_id);

		// 获取提交来的数据
		$param = array();
		$param['ca_password'] = $this->request->post('ca_password');

		// 整理后待更新的数据
		$newParam = array();

		// 检查登录密码
		if (!isset($param['ca_password']) || $param['ca_password'] == '') {
			$this->message('error', '请输入登录密码');
		}

		// 设置新密码
		if (isset($param['ca_password']) && $param['ca_password'] != '') {
			$uda_adminer_update = &uda::factory('voa_uda_frontend_adminer_update');
			$uda_adminer_update->adminer_pwd_modify($ca_id, $param['ca_password'], false);
			$result = array();
			$uda_adminer_update->adminer_login($ca_id, $result);
			if (!empty($result['auth'])) {
				// 重新写入cookie
				foreach ($result['auth'] as $c) {
					$this->session->set($c['name'], $c['value']);
				}
			}

			$uc_data = array(
				'ep_id' => $this->_setting['ep_id'],
				'cur_mobile' => $adminerDetail['ca_mobilephone'],
				'password' => $param['ca_password']
			);

			$data = array();
			$url = config::get('voa.uc_url') . 'PubApi/Api/EnterpriseAdminer/Update';
			if (!voa_h_api::instance()->postapi($data, $url, $uc_data)) {
				$this->message('error', '更新手机信息时，更新关联发生错误');
				return false;
			}

			if (0 < $data['errcode']) {
				$this->message('error', $data['errmsg']);
				return false;
			}
		}

		$message = '密码修改操作完毕，请记住新密码';

		// 直接返回操作提示信息
		if ( $returnMessage === true ) {
			$this->message('success', $message, $this->cpurl($this->_module, $this->_operation, $this->_subop, ''), false);
		}

		return true;
	}
}
