<?php
/**
 * 编辑用户通讯录信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_member_profile_edit extends voa_c_frontend_base {

	public function execute() {
		/** 处理 post 提交 */
		if ($this->_is_post()) {
			$serv = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			try {
				$serv->begin();

				/** 编辑操作 */
				$mobile = trim($this->request->get('mobilephone'));
				$telephone = trim($this->request->get('telephone'));
				$email = trim($this->request->get('email'));
				if (!empty($mobile)) {
					$this->_edit_mobilephone($mobile);
				} else if(!empty($telephone)) {
					$this->_edit_telephone($telephone);
				} else if(!empty($email)) {
					$this->_edit_email($email);
				}

				$serv->commit();
			} catch (Exception $e) {
				$serv->rollback();
				$this->_error_message('用户资料编辑失败');
			}

			$this->_success_message('用户资料操作成功', '/profile/show');
		}

		/** 获取用户信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $servm->fetch_by_uid(startup_env::get('wbs_uid'));

		/** 获取通讯录信息 */
		$servab = &service::factory('voa_s_oa_common_addressbook', array('pluginid' => 0));
		$address = $servab->fetch($user['cab_id']);
		if (empty($address)) {
			$this->_error_message('数据错误, 请联系管理员');
		}

		$this->view->set('address', $address);
		/** 部门 */
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		$this->view->set('department', $departments[$address['cd_id']]);

		/** 职位 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');
		$this->view->set('job', $jobs[$address['cj_id']]);
		$this->view->set('navtitle', '编辑用户信息');

		$this->_output('member/profile/edit');
	}

	protected function _edit_email($email) {
		$email = trim($email);
		if (!validator::is_email($email)) {
			throw new Exception('邮箱地址错误');
			return false;
		}

		/** 更新通讯录 */
		$servab = &service::factory('voa_s_oa_common_addressbook', array('pluginid' => 0));
		$servab->update(array(
			'cab_email' => $email
		), array("cab_id" => $this->_user['cab_id']));
	}

	/**
	 * 编辑电话号码
	 * @param string $phone 电话号码
	 * @throws Exception
	 */
	protected function _edit_telephone($phone) {
		$phone = trim($phone);
		if (empty($phone) || !preg_match('/^[\d\-]+$/i', $phone)) {
			throw new Exception('电话号码错误');
			return false;
		}

		/** 更新通讯录 */
		$servab = &service::factory('voa_s_oa_common_addressbook', array('pluginid' => 0));
		$servab->update(array(
			'cab_telephone' => $phone
		), array("cab_id" => $this->_user['cab_id']));
	}

	/**
	 * 编辑手机号码
	 * @param string $mobile 手机号码
	 */
	protected function _edit_mobilephone($mobile) {
		$mobile = trim($mobile);
		$mobile = ltrim($mobile, '0');
		if (empty($mobile) || !preg_match('/^1[1-9]\d{9}$/i', $mobile)) {
			throw new Exception('手机号码错误');
			return false;
		}

		/** 判断手机号码是否已经存在 */
		$servab = &service::factory('voa_s_oa_common_addressbook', array('pluginid' => 0));
		$ab = $servab->fetch_by_mobilephone($mobile);
		if (!empty($ab)) {
			throw new Exception('该号码已被注册');
			return false;
		}

		/** 更新通讯录 */
		$servab->update(array(
			'cab_mobilephone' => $mobile
		), array("cab_id" => $this->_user['cab_id']));

		/** 更新用户表的手机 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$servm->update(array(
			'm_mobilephone' => $mobile
		), array('m_uid' => $this->_user['m_uid']));
	}
}
