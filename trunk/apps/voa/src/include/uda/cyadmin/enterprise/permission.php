<?php

/**
 * enterprise_permission
 * @author Burce
 */
class voa_uda_cyadmin_enterprise_permission extends voa_uda_cyadmin_base {

	private $__request = array();

	/**
	 * 入库操作
	 * @param        $in
	 * @param        $out
	 * @param object $session
	 * @return bool
	 */
	public function add($in, &$out) {
		//判断两次输入密码是否一致
		if ($in['password'] != $in['repassword']) {
			$this->errmsg('20002', '两次输入密码不一致');

			return false;
		}
		// 提交的值进行过滤
		$data = array();
		if (!$this->getact($in, $data)) {
			return $this->errmsg($this->errcode, $this->errmsg);
		}

		// 获取上级领导ID
		if (isset($data['sub_id']) && !empty($data['sub_id'])) {
			$sub_id = $data['sub_id'];
		}
		unset($data['sub_id']);

		// 入库
		$serv = &service::factory('voa_s_cyadmin_common_newadminer');

		$data = $serv->insert($data);

		// 如果有上级
		if (isset($sub_id) && !empty($sub_id)) {
			$serv_sub = &service::factory('voa_s_cyadmin_common_subordinates');
			$serv_sub->insert(array('ca_id' => $sub_id, 'un_id' => $data['ca_id']));
		}

		if (!$data) {
			return false;
		}

		return true;
	}


	/**
	 * 处理提交的数据
	 * @param $in
	 * @param $out
	 * @return bool
	 */
	public function getact($in, &$out) {

		//获取密码干扰串
		$salt = $this->get_salt();
		//获取数据
		if (!empty($in)) {
			$pwd = md5($in['password']);
			$passwd = md5(substr($pwd, 16) . $in['password']);
			$data['ca_email'] = $in['email'];
			$data['ca_realname'] = $in['realname'];
			$data['ca_mobilephone'] = $in['tell'];
			$data['cag_id'] = $in['cag_id'];
			$data['ca_username'] = $in['username'];
			$data['ca_salt'] = $salt;
			$data['ca_job'] = $in['job'];
			$data['ca_password'] = md5($passwd . $salt);
			$data['ca_locked'] = 0;
			$data['ca_lastlogin'] = startup_env::get('timestamp');
			$data['sub_id'] = $in['sub_id'];
		} else {
			$this->errmsg('10007', '内容不能为空');

			return false;
		}

		$fields = array(
			'ca_realname' => array('ca_realname', parent::VAR_STR, null, null, false),
			'ca_mobilephone' => array('ca_mobilephone', parent::VAR_STR, null, null, false),
			'cag_id' => array('cag_id', parent::VAR_STR, null, null, false),
			'ca_username' => array('ca_username', parent::VAR_STR, null, null, false),
			'ca_job' => array('ca_job', parent::VAR_STR, null, null, false),
			'ca_email' => array('ca_email', parent::VAR_STR, null, null, false),
			'ca_password' => array('ca_password', parent::VAR_STR, null, null, false),
			'ca_locked' => array('ca_locked', parent::VAR_INT, null, null, false),
			'ca_salt' => array('ca_salt', parent::VAR_STR, null, null, false),
			'ca_lastlogin' => array('ca_lastlogin', parent::VAR_INT, null, null, false),
			'sub_id' => array('sub_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}

		//检查是否重复添加
		$serv = &service::factory('voa_s_cyadmin_common_adminer');
		$res = $serv->fetch_by_username($data['ca_username']);

		if ($res) {
			$this->errmsg('20009', '该用户名已存在');

			return false;
		}

		if (empty($this->__request['ca_realname'])) {
			$this->errmsg('20000', '真实姓名不能为空');

			return false;
		}
		if (empty($this->__request['ca_mobilephone'])) {
			$this->errmsg('20001', '手机号码不能为空');

			return false;
		}
		if (empty($this->__request['ca_username'])) {
			$this->errmsg('20002', '登录名不能为空');

			return false;
		}
		if (empty($this->__request['ca_password'])) {
			$this->errmsg('20003', '密码不能为空');

			return false;
		}
		if (empty($this->__request['ca_email'])) {
			$this->errmsg('20004', '邮箱不能为空');

			return false;
		}
		$out = $this->__request;

		return true;
	}

	/**
	 * 获取干扰串
	 * @return string
	 */
	public function get_salt() {

		return substr(startup_env::get('timestamp'), 4, 6);
	}

	/**
	 * 后台活动编辑更新
	 * @param $in
	 * @param $out
	 * @param object session
	 * @return bool
	 */
	public function edit($in, &$out) {
		$acid = $in['acid'];

		// 处理时间


		$fields = array(
			'province' => array('province', parent::VAR_STR, null, null, false),
			'city' => array('city', parent::VAR_STR, null, null, false),
			'county' => array('county', parent::VAR_STR, null, null, false),
			'co_name' => array('co_name', parent::VAR_STR, null, null, false),
			'intro' => array('intro', parent::VAR_STR, null, null, false),
			'link_name' => array('link_name', parent::VAR_STR, null, null, false),
			'link_phone' => array('link_phone', parent::VAR_STR, null, null, false),
			'deadline' => array('deadline', parent::VAR_INT, null, null, false),
			'created_day' => array('created_day', parent::VAR_STR, null, null, false),
			'created_hour' => array('created_hour', parent::VAR_STR, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $in)) {
			return false;
		}

		if (!validator::is_string_count_in_range($in['co_name'], 1, 15)) {
			$this->errmsg('10004', '标题字数最高15字，最低1个字');

			return false;
		}

		$data = array(
			'province' => $in['province'],
			'city' => $in['city'],
			'county' => $in['county'],
			'co_name' => $in['co_name'],
			'intro' => $in['intro'],
			'link_name' => $in['link_name'],
			'link_phone' => $in['link_phone'],
			'deadline' => $in['deadline'],
			'created_day' => $in['created_day'],
			'created_hour' => $in['created_hour'],
		);
		$serv = &service::factory('voa_s_cyadmin_enterprise_account');
		$out = $serv->update_by_conds($acid, $data);
		$out = $data;

		return true;
	}

	public function agant_setting($in, $out) {

		// 提交的值进行过滤
		$data = array();
		if (!$this->filter_agant($in, $data)) {
			return array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg,
			);
		}

		// 入agant库
		$serv = &service::factory('voa_s_cyadmin_enterprise_agant');

		$data = $serv->insert($data);

		if (!$data) {
			return false;
		}

		// 更新account库
		$serv = &service::factory('voa_s_cyadmin_enterprise_account');

		$update_data = array(
			'pay_status' => $data['pay_status'],
			'deadline' => $data['deadline'],
			'pay_time' => $data['pay_time'],
			'ca_id' => $data['ca_id'],
			'post_ip' => $in['post_ip'],
		);
		$serv->update_by_conds($in['acid'], $update_data);

		return true;
	}

	/**
	 * 过滤参数
	 * @param $in
	 * @param $out
	 * @return bool
	 * @throws help_exception
	 */
	public function filter_agant($in, &$out) {
		//获取数据
		if (!empty($in)) {
			$data['pay_status'] = $in['pay_status'];
			$data['deadline'] = $in['deadline'];
			$data['pay_time'] = empty($in['pay_time']) ? 0 : rstrtotime($in['pay_time']);
			$data['ca_id'] = $in['ca_id'];
			$data['salesremark'] = $in['salesremark'];
			$data['acid'] = $in['acid'];
		} else {
			$this->errmsg('10007', '内容不能为空');

			return false;
		}

		$fields = array(
			'pay_status' => array('pay_status', parent::VAR_INT, null, null, false),
			'deadline' => array('deadline', parent::VAR_INT, null, null, false),
			'pay_time' => array('pay_time', parent::VAR_INT, null, null, false),
			'ca_id' => array('ca_id', parent::VAR_STR, null, null, false),
			'salesremark' => array('salesremark', parent::VAR_STR, null, null, false),
			'acid' => array('acid', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}

		if (empty($this->__request['pay_status'])) {
			$this->errmsg('30001', '付费状态不能为空');

			return false;
		}
		if (empty($this->__request['deadline'])) {
			$this->errmsg('30002', '代理期限不能为空');

			return false;
		}
		if (empty($this->__request['pay_time']) && $this->__request['pay_status'] == 2) {
			$this->errmsg('30003', '付费时间不能为空');

			return false;
		}
		if (empty($this->__request['ca_id'])) {
			$this->errmsg('30004', '跟进销售不能为空');

			return false;
		}
		if (empty($this->__request['acid'])) {
			$this->errmsg('30005', '丢失重要参数');

			return false;
		}

		$out = $this->__request;

		return true;
	}

}
