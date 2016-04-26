<?php
/**
 * 处理用户注册
 * $Author$
 * $Id$
 */

class voa_c_frontend_member_register extends voa_c_frontend_base {

	/**
	 * _before_action
	 *
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		$this->_error_message('请先进入后台导入用户信息');
		return true;
		/** 检查 openid 在 ucenter 中是否已存在 */
		if (!$this->_chk_openid()) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 处理用户提交, 进行注册 */
		if ($this->_is_post()) {
			$this->_register();
		}

		$this->view->set('refer', get_referer());
		$this->view->set('navtitle', '注册');

		$this->_output('member/register');
	}

	/** 检查用户是否已关注 */
	protected function _chk_openid() {
		/** openid */
		$this->_openid = $this->_get_openid();

		/** 如果没有 openid, 则说明当前访问非法 */
		if (empty($this->_openid)) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 根据 openid 读取用户信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $servm->fetch_by_openid($this->_openid, true);
		if (!empty($user)) {
			if(voa_d_oa_member::STATUS_REMOVE != $user['m_status']) {
				$this->_success_message('register_succeed', get_referer("index.php"));
			}
		}

		return true;
	}

	/** 用户注册 */
	protected function _register() {
		/** 姓名 /工号/身份证号*/
		$username = $this->request->get('username');
		$number = $this->request->get('number');
		$cardnum = $this->request->get('cardnum');
		/** 验证手机号码 */
		$mobilephone = $this->request->get('mobilephone');
		/** 去除左侧的字符 0 */
		$mobilephone = ltrim($mobilephone, "0");
		if (!validator::is_mobile($mobilephone)) {
			$this->_error_message('mobilephone_invalid');
			return false;
		}

		/** 判断手机号码是否重复 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$usermp = $servm->fetch_by_mobilephone($mobilephone);
		if (!empty($usermp)) {
			$this->_error_message('mobilephone_duplicate');
		}

		/** 根据 openid 读取用户信息 */
		$user = $servm->fetch_by_openid($this->_openid, true);
		if (!empty($user)) {
			if(voa_d_oa_member::STATUS_REMOVE != $user['m_status']) {
				$this->_success_message('register_succeed', get_referer("index.php"));
			}
		}

		/** 判断通讯录中是否有该号码, 有则直接通过 */
		$status = voa_d_oa_member::STATUS_NORMAL;
		$servab = &service::factory('voa_s_oa_common_addressbook', array('pluginid' => 0));
		$addr_book = $servab->fetch_by_mobilephone($mobilephone);
		if (!empty($addr_book) && $addr_book['cab_realname'] == $username
				&& $addr_book['cab_number'] == $number
				&& substr($addr_book['cab_idcard'], -6) == $cardnum
				&& 0 < $addr_book['cd_id'] && 0 < $addr_book['cj_id']) {
			$status = voa_d_oa_member::STATUS_NORMAL;
		}

		$serv_mf = &service::factory('voa_s_oa_member_field', array('pluginid' => 0));
		try {
			$servm->begin();

			/** 如果通讯录中没有记录, 则 */
			if (empty($addr_book)) {
				$addr_book = array(
					'cab_number' => $number,
					'cab_realname' => $username,
					'cab_mobilephone' => $mobilephone,
					'cab_idcard' => $cardnum
				);
				$cab_id = $servab->insert($addr_book, true);
				$addr_book['cab_id'] = $cab_id;
			}

			/** 用户信息入库 */
			$salt = random(6);
			$mem = array(
				'm_openid' => $this->_openid,
				'cab_id' => $addr_book['cab_id'],
				'm_username' => $username,
				'm_mobilephone' => $mobilephone,
				'm_number' => $number,
				'm_password' => '',
				'm_adminid' => 0,
				'm_groupid' => 0,
				'cd_id' => $addr_book['cd_id'],
				'cj_id' => $addr_book['cj_id'],
				'm_salt' => '',
				'm_status' => $status
			);
			$uid = $servm->insert($mem, true, true);
			$mem['m_uid'] = $uid;

			/** 用户详情信息入库 */
			$serv_mf->insert(array('m_uid' => $uid), true, true);

			/** 更新通讯录的 m_uid */
			$servab->update(array('m_uid' => $uid), array('cab_id' => $addr_book['cab_id']));

			$servm->commit();
		} catch (Exception $e) {
			$servm->rollback();
			$this->_error_message('注册失败');
		}

		/** 发送消息通知 */

		if (voa_d_oa_member::STATUS_NORMAL == $status) {
			$this->_success_message("register_succeed", "index.php");
		} else {
			$this->_success_message('register_verify_succeed', get_referer());
		}
	}
}

