<?php
/**
 * voa_c_admincp_system_adminer_base
 * 企业后台/系统设置/管理员/基本控制
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_system_adminer_base extends voa_c_admincp_system_base {

	/**
	 * 登录名长度限制，min <= AND <= max
	 * /system/adminer/base
	 * @var array
	 */
	protected $_username_length_limit	=	array(
			'min'=>3,
			'max'=>15,
	);

	/**
	 * 模块初始化启动
	 * /system/adminer/base
	 * @param mixed $action
	 * @access protected
	 * @return void
	 */
	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		/** 管理员状态数组推送到模板内 */
		$this->view->set('userLockedStatus', $this->adminer_locked_description);

		/** 系统管理员的状态标记 */
		$this->view->set('systemadminer', $this->adminer_locked['sys']);

		/** 管理组列表 */
		$this->view->set('groupList', $this->_adminergroup_list());

		$addAdminerGroupLink	=	$this->cpurl($this->_module, 'adminergroup', 'add', '');
		$this->view->set('addAdminerGroupLink', $this->linkShow($addAdminerGroupLink, '', '添加新管理组', 'fa-plus', ' class="font12"'));

		$addDepartmentLink		=	$this->cpurl($this->_module, 'department', 'add', '');
		$this->view->set('addDepartmentLink', $this->linkShow($addDepartmentLink, '', '添加新部门', 'fa-plus', ' class="font12"'));

		return true;

	}

	/**
	 * 返回指定管理员信息
	 * /system/adminer/base
	 * @param number $cg_id
	 */
	protected function _adminer($cg_id){
		return $this->_service_single('common_adminer', 'fetch', $cg_id);
	}

	/**
	 * 获取管理员详情，不存在则返回数据字段默认值
	 * /system/adminer/base
	 * @param number $cag_id
	 * @return array
	 */
	protected function _adminer_detail($ca_id = 0) {

		if ($ca_id) {
			$adminer = $this->_adminer($ca_id);
			$adminer = $adminer ? $adminer : self::_adminer_detail(0);
		} else {
			$adminer = $this->_service_single('common_adminer', 'fetch_all_field', array());
		}

		return $adminer;
	}

	/**
	 * (system/base) 返回所有管理员信息
	 * /system/adminer/base
	 * @return array
	 */
	protected function _adminer_list() {

		if (!empty($this->_adminer_list_all)) {
			return $this->_adminer_list_all;
		}

		$list = array();
		$adminerGroup = $this->_adminergroup_list();
		$departmentList = $this->_department_list();
		$tmp = $this->_service_single('common_adminer', 'fetch_all', array());

		foreach ($tmp AS $_ca) {

			$ca = $_ca;
			$ca['_locked'] = $this->_adminer_lock_status($_ca['ca_locked']);

			if ( isset($adminerGroup[$_ca['cag_id']]) ) {
				$ca['_grouptitle'] = $adminerGroup[$_ca['cag_id']]['cag_title'];
				$ca['_groupenable'] = $this->_adminergroup_enable_status($adminerGroup[$_ca['cag_id']]['cag_enable']);
			} else {
				$ca['_grouptitle'] = '*<del>'.$_ca['cag_id'].'</del>*';
				$ca['_groupenable'] = '';
			}

			$ca['_lastlogin'] = rgmdate($_ca['ca_lastlogin'],'Y-m-d H:i');
			unset($ca['ca_password'], $ca['ca_salt']);

			$list[$_ca['ca_id']] = $ca;
		}

		unset($tmp, $ca);
		return $this->_adminer_list_all = $list;
	}

	/**
	 * (system/adminer/base) 删除管理员
	 *
	 * @param number $ca_id
	 */
	protected function _adminer_delete($ca_id) {

		$adminer = $this->_adminer($ca_id);
		if (empty($adminer['ca_id']) || $adminer['ca_id'] != $ca_id) {
			return false;
		}

		if ($adminer['ca_locked'] == voa_d_oa_common_adminer::LOCKED_SYS) {
			$this->message('error', '禁止删除系统管理员');
			return false;
		}

		// 删除对应的关联表信息
		$uc_data = array(
			'ep_id' => $this->_setting['ep_id'],
			// 更改的手机号
			'mobile' => $adminer['ca_mobilephone']
		);

		$data = array();
		$url = config::get('voa.uc_url') . 'PubApi/Api/EnterpriseAdminer/Del';
		if (!voa_h_api::instance()->postapi($data, $url, $uc_data)) {
			$this->message('error', '更新手机信息时，更新关联发生错误');
			return false;
		}

		if (0 < $data['errcode']) {
			$this->message('error', $data['errmsg']);
			return false;
		}

		// 删除管理员信息
		$this->_service_single('common_adminer', 'delete', $ca_id);
	}

	/**
	 * 响应提交添加或者编辑动作
	 * /system/adminer/base
	 * @param number $ca_id
	 * @param boolean $returnMessage 操作成功后是否返回提示信息
	 */
	protected function _response_submit_edit($ca_id, $returnMessage = true) {

		error_reporting(E_ALL);

		// 当前管理的组的详情
		$adminerDetail = $this->_adminer_detail($ca_id);
		// 如果是新增，判断管理员数量是否超过限制
		if ((!$ca_id || !$adminerDetail['ca_id']) && $this->_service_single('common_adminer', 'count_all', array()) >= voa_d_oa_common_adminer::COUNT_MAX) {
			$this->message('error', '系统限制只允许添加最多 '.voa_d_oa_common_adminer::COUNT_MAX.' 个管理员，请返回');
		}

		// 获取提交来的数据
		$param = array(
			'ca_mobilephone' => '',
			'ca_email' => '',
			'ca_username' => '',
			'ca_password' => '',
			'cag_id' => '',
			'ca_locked' => '',
		);
		foreach ($param as $key => $value) {
			if (!isset($_POST[$key])) {
				if ($key == 'ca_password') {
					$param[$key] = '';
				} else {
					$param[$key] = $adminerDetail[$key];
				}
			} else {
				$param[$key] = $this->request->post($key);
			}
		}

		// 整理后待更新的数据
		$newParam = array();

		// 新增 或 手机号码发生变动
		if (!$ca_id || $param['ca_mobilephone'] != $adminerDetail['ca_mobilephone']) {
			if ($param['ca_mobilephone'] && !validator::is_mobile($param['ca_mobilephone'])) {
				$this->message('error', '请填写正确的手机号码');
			}
			if ($param['ca_mobilephone'] && $this->_service_single('common_adminer', 'count_by_mobilephone_notid', $param['ca_mobilephone'], $ca_id) > 0) {
				$this->message('error', '手机号已被其他管理员使用，请更换一个');
			}
			// 新手机号码
			$newParam['ca_mobilephone'] = $param['ca_mobilephone'];
			// 更新UC登录关联表
			if ($ca_id != 0) {
				$uc_data = array(
					'ep_id' => $this->_setting['ep_id'],
					// 当前手机号
					'cur_mobile' => $adminerDetail['ca_mobilephone'],
					// 更改的手机号
					'mobile' => $newParam['ca_mobilephone'],
					'realname' => $param['ca_username'],
					'userstatus' => 1 == $param['ca_locked'] ? 2 : 1,
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
		}

		// 新增 或 email 发生变动
		if (!$ca_id || $param['ca_email'] != $adminerDetail['ca_email']) {
			if ($param['ca_email'] && !validator::is_email($param['ca_email'])) {
				$this->message('error', '请正确填写Email');
			}
			if ($param['ca_email'] && $this->_service_single('common_adminer', 'count_by_email_notid', $param['ca_email'], $ca_id) > 0) {
				$this->message('error', 'Email 已被其他管理员使用，请更换一个');
			}
			// 新 email
			$newParam['ca_email'] = $param['ca_email'];
		}

		if (empty($param['ca_mobilephone']) && empty($param['ca_email'])) {
			$this->message('error', '手机号码 和 email 必须至少填写一项');
		}

		// 新增 或 显示名发生改变
		if (!$ca_id || $param['ca_username'] != $adminerDetail['ca_username']) {
			if ( !isset($param['ca_username']) || !is_scalar($param['ca_username']) ) {
				$this->message('error', '姓名名必须填写');
			}
			if ( ($length = strlen($param['ca_username'])) < $this->_username_length_limit['min'] ) {
				$this->message('error', '姓名长度必须大于 '.$this->_username_length_limit['min'].' 字节');
			}
			if ( $length > $this->_username_length_limit['max'] ) {
				$this->message('error', '姓名长度必须小于 '.$this->_username_length_limit['max'].' 字节');
			}
			if ( $param['ca_username'] != rhtmlspecialchars($param['ca_username']) ) {
				$this->message('error', '姓名禁止包含特殊字符');
			}
			if ( $this->_service_single('common_adminer', 'count_by_username_notid', $param['ca_username'], $ca_id) > 0 ) {
				$this->message('error', '姓名“'.$param['ca_username'].'”已经被使用，请更换一个');
			}
			$newParam['ca_username'] = $param['ca_username'];
		}


		// 检查登录密码
		// 添加新管理员 且密码未设置
		if (!$ca_id && $param['ca_password'] == '') {
			$this->message('error', '请输入登录密码');
		}

		$pwd_modify = false;
		// 设置密码
		if ($param['ca_password'] != '') {
			// 修改密码
			$pwd_modify = true;
		}

		// 新增 或  管理组发生变动
		if (!$ca_id || $param['cag_id'] != $adminerDetail['cag_id']) {

			if (!is_scalar($param['cag_id'])) {
				$this->message('error', '请正确选择所属的管理组');
			}

			if ($param['cag_id'] <= 0) {
				$this->message('error', '请选择管理员所属的管理组');
			}

			if ($adminerDetail['ca_locked'] == voa_d_oa_common_adminer::LOCKED_SYS) {
				$this->message('error', '禁止修改系统管理员所在管理组');
			}

			if (!$this->_get_usergroup($param['cag_id'])) {
				$this->message('error', '所选系统管理组不存在，请返回确认');
			}

			// 新管理组
			$newParam['cag_id'] = $param['cag_id'];
		}

		// 新增 或 登录锁定状态发生变动
		if (!$ca_id || $param['ca_locked'] != $adminerDetail['ca_locked']) {
			if (!is_scalar($param['ca_locked']) || !isset($this->adminer_locked_description[$param['ca_locked']])) {
				$this->message('error', '请正确选择是否锁定登录');
			}
			$newParam['ca_locked'] = $param['ca_locked'];
		}

		// 移除未发生改动的数据，不写入更新
		foreach ($newParam AS $_k => $_v) {
			if (isset($adminerDetail[$_k]) && $_v == $adminerDetail[$_k]) {
				unset($newParam[$_k]);
			}
		}

		if (empty($newParam) && !$pwd_modify) {
			$this->message('error', '没有被更改的信息，无须进行提交');
		}

		$message = '';
		if (!empty($newParam)) {
			$func = 'Add';
			$uc_data = array(
				'ep_id' => $this->_setting['ep_id'],
				// 更改的手机号
				'mobile' => $param['ca_mobilephone'],
				'ca_id' => $ca_id,
				'realname' => $param['ca_username'],
				'userstatus' => 1 == $param['ca_locked'] ? 2 : 1,
				'password' => $param['ca_password']
			);
			if ($ca_id) {
				// 编辑
				$func = 'Update';
				$uc_data['cur_mobile'] = $param['ca_mobilephone'];
				$this->_service_single('common_adminer', 'update', $newParam, $ca_id);
				$message = '编辑管理员信息操作完毕';
			} else {
				// 新增
				$ca_id = $this->_service_single('common_adminer', 'insert', $newParam, true);
				$message = '添加新管理员信息操作完毕';
			}

			$data = array();
			$url = config::get('voa.uc_url') . 'PubApi/Api/EnterpriseAdminer/' . $func;
			if (!voa_h_api::instance()->postapi($data, $url, $uc_data)) {
				$this->message('error', '更新手机信息时，更新关联发生错误');
				return false;
			}

			if (0 < $data['errcode']) {
				$this->message('error', $data['errmsg']);
				return false;
			}
		}

		// 密码发生改变
		if ($ca_id && $pwd_modify) {
			// 储存在用户表的密码和盐值
			list($new_password, $new_salt) = voa_h_func::generate_password($param['ca_password'], '', false, 6);
			$serv_common_adminer = Service::factory('voa_s_oa_common_adminer');
			$serv_common_adminer->update(array(
				'ca_password' => $new_password,
				'ca_salt' => $new_salt
			), $ca_id);
			$uc_data = array(
				'ep_id' => $this->_setting['ep_id'],
				'cur_mobile' => $param['ca_mobilephone'],
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

			if (empty($message)) {
				$message = '密码修改操作完毕';
			}
		}

		// 直接返回操作提示信息
		if ( $returnMessage === true ) {
			$this->message('success', $message, $this->cpurl($this->_module, $this->_operation, 'list', ''), false);
		}

		// 只返回结果，后续另行操作
		return true;
	}
}
