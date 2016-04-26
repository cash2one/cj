<?php
/**
 * 用户信息的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_member_insert extends voa_uda_frontend_member_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 添加新用户
	 * @param array $submit 提交的用户信息
	 * <pre>
	 * 如果不更改密码则令 m_password 为false
	 * 如果不更改头像则令 m_face 为false
	 * </pre>
	 * @param array $member <strong style="color:red">(引用结果)</strong>返回的用户信息
	 * @return boolean
	 */
	public function add($submit, &$member, $sendmail = true, $to_qywx = true) {

		/** 如果有职位信息, 则 */
		$cj_name = isset($submit['cj_name']) ? (string)$submit['cj_name'] : '';
		$cj_name = trim($cj_name);
		if (!empty($cj_name) && empty($submit['cj_id'])) {
			$serv_job = &service::factory('voa_s_oa_common_job');
			$job_info = $serv_job->fetch_by_cj_name($cj_name);
			if (empty($job_info)) {
				$uda_job = &uda::factory('voa_uda_frontend_job_update');
				$job_new = array('cj_name' => $cj_name, 'cj_displayorder' => 99);
				$job_his = array();
				$job_info = array();
				if ($uda_job->update($job_his, $job_new, $job_info)) {
					$submit['cj_id'] = $job_info['cj_id'];
				}
			} else {
				$submit['cj_id'] = $job_info['cj_id'];
			}
		}

		// 检查必须填写的项目
		/**foreach ($this->member_key_required as $key => $name) {
			if (empty($submit[$key])) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_FIELD_LOSE, $name);
			}
		}*/

		// 设置输出的数据
		$member = $submit;

		// 检查手机号码
		/**$is_mobilephone = true;
		if (!$this->check_member_mobilephone($member['m_mobilephone'], true)) {
			$is_mobilephone = false;
		}

		// 检查邮箱
		$is_email = true;
		if (!$this->check_member_email($member['m_email'], true)) {
			$is_email = false;
		}

		// weixinid
		$is_weixinid = true;
		if (!$this->check_member_weixinid($member['mf_weixinid'])) {
			$is_weixinid = false;
		}

		// 手机号/邮箱/weixinid
		if (!$is_email && !$is_mobilephone && (empty($member['mf_weixinid']) || !$is_weixinid)) {
			$this->set_errmsg(voa_errcode_oa_member::MOBILE_EMAIL_WEIXINID_IS_EMPTY);
			return false;
		}*/

		// 设置未定义的字段值为空
		foreach (array_merge($this->member_field, $this->member_main) as $key => $name) {
			if (!isset($member[$key])) {
				$member[$key] = '';
			}
		}

		// 检查的字段填写合法性
		$check = array(
			/**'check_member_username' => 'm_username',
			'check_member_cd_id' => 'cd_id',
			'check_member_active' => 'm_active',
			'check_member_number' => 'm_number',
			'check_member_cj_id' => 'cj_id',
			'check_member_gender' => 'm_gender',
			'check_member_address' => 'mf_address',
			'check_member_idcard' => 'mf_idcard',
			//'check_member_telephone' => 'mf_telephone',
			'check_member_qq' => 'mf_qq',
			//'check_member_weixinid' => 'mf_weixinid',
			'check_member_birthday' => 'mf_birthday',
			'check_member_remark' => 'mf_remark',*/
			'check_member_cj_id' => 'cj_id',
			'check_member_active' => 'm_active'
		);
		foreach ($check as $method => $_v) {
			if (!$this->$method($member[$_v])) {
				return false;
			}
		}

		// 获取姓名的首字母组合
		$member['m_index'] = '';
		$this->get_username_index($member['m_username'], $member['m_index']);

		// 生成唯一标识ID
		if (empty($member['m_openid'])) {
			$this->_make_userid($member['m_username'], $member['m_openid']);
		}

		// 要求设置密码
		if (isset($member['m_password']) && !empty($member['m_password'])) {
			// 指定了密码
			$password = $member['m_password'];
			if (!validator::is_md5($password)) {
				return $this->set_errmsg(voa_errcode_oa_member::MEMBER_PASSWORD_NEW_NOT_MD5);
			}
		} else {
			// 未指定密码则使用手机号末六位做为密码
			$password = md5(substr($member['m_mobilephone'], -6));
		}

		list($member['m_password'], $member['m_salt']) = voa_h_func::generate_password($password, null, false);

		// 企业微信通讯录接口
		$qywx_addressbook = new voa_wxqy_addressbook();

		// 用户主表字段
		$member_main = array(
			'm_openid', 'm_mobilephone', 'm_email', 'm_active', 'm_username', 'm_index',
			'm_password', 'm_number', 'cd_id', 'cj_id', 'm_gender', 'm_face', 'm_salt', 'm_wechatid'
		);
		// 用户扩展表字段
		$member_field = array(
			'mf_address', 'mf_idcard', 'mf_telephone', 'mf_qq', 'mf_weixinid', 'mf_birthday',
			'mf_remark',
		);

		// 用户主表数据
		$data_member = array();
		foreach ($member_main as $key) {
			if (isset($member[$key])) {
				$data_member[$key] = $member[$key];
			}
		}
		// 用户扩展表数据
		$data_member_field = array();
		foreach ($member_field as $key) {
			if (isset($member[$key])) {
				$data_member_field[$key] = $member[$key];
			}
		}

		$uda_member_update = &uda::factory('voa_uda_frontend_member_update');

		try {

			$this->serv_member->begin();

			// 添加到主表
			$m_uid = $this->serv_member->insert($data_member, true);

			// 添加到扩展表
			$data_member_field['m_uid'] = $m_uid;
			$this->serv_member_field->insert($data_member_field, false, true);

			// 添加到搜索表
			$uda_member_update->member_search_update($m_uid, $member);

			// 添加到用户与部门对应表
			$uda_member_update->member_department_update($m_uid, $cd_id);

			// 连接企业微信接口进行添加
			if ($this->use_qywx && $to_qywx) {

				// 构造微信接口需要的数据
				$qywx_data = array();
				if (!$this->local_to_wxqy($member, $qywx_data)) {
					throw new Exception($this->errmsg, $this->errcode);
					return false;
				}

				$result = array();
				if (!$qywx_addressbook->user_create($qywx_data, $result)) {
					// 与接口通讯失败
					$this->errcode = $qywx_addressbook->errcode;
					$this->errmsg = $qywx_addressbook->errmsg;
					throw new Exception($this->errmsg, $this->errcode);
					return false;
				}
			}

			$this->serv_member->commit();

		} catch (Exception $e) {

			$this->serv_member->rollback();

			logger::error($e);
			//throw new controller_exception($e->getMessage(), $e->getCode());
			//return $this->set_errmsg(voa_errcode_oa_member::MEMBER_INSERT_FAILED);
			if (empty($this->errmsg)) {
				$this->set_errmsg(voa_errcode_oa_member::MEMBER_INSERT_FAILED);
			}

			return false;
		}

		/** by zhuxun begin */
		if (validator::is_email($data_member['m_email']) && $sendmail && 'citic.vchangyi.com' != $_SERVER['HTTP_HOST']) {
			$sets = voa_h_cache::get_instance()->get('setting', 'oa');
			$mail = &uda::factory('voa_uda_uc_mailcloud_insert');
			$mail->send_reg_mail(array($data_member['m_email']), '邀请您加入企业号', array(
				'%sitename%' => array($sets['sitename']),
				'%username%' => array($data_member['m_username']),
				'%qrcode%' => array('<img src="'.$sets['qrcode'].'" width=200 />')
			), $sets['sys_email_account'], $sets['sys_email_user']);
		}
		/** by zhuxun end */

		return true;
	}

}
