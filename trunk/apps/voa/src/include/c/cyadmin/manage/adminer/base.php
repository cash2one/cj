<?php
/**
 * voa_c_cyadmin_manage_adminer_base
 * 主站后台/后台管理/管理员管理/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_manage_adminer_base extends voa_c_cyadmin_manage_base {

	/**
	 * 登录名长度限制，min <= AND <= max
	 * @var array
	 */
	protected $_username_length_limit	=	array(
			'min'=>3,
			'max'=>15,
	);


	protected function _before_action($action) {

		return parent::_before_action($action);
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 格式化管理员信息
	 * @param array $aminer
	 * @return array
	 */
	protected function _adminer_format($adminer = array()) {

		// 帐号锁定状态文字
		$adminer['_locked'] = isset($this->_adminer_locked_map[$adminer['ca_locked']]) ? $this->_adminer_locked_map[$adminer['ca_locked']] : '';

		if (isset($this->_adminergroup_list[$adminer['cag_id']])) {
			// 所在管理组存在

			// 管理组信息
			$cag = $this->_adminergroup_list[$adminer['cag_id']];

			// 管理组标题
			$adminer['_cag_title'] = $cag['cag_title'];
			// 管理组启用状态
			$adminer['_cag_enable'] = isset($this->_adminergroup_enable_map[$cag['cag_enable']]) ? $this->_adminergroup_enable_map[$cag['cag_enable']] : '';

		} else {
			// 所在管理组不存在
			$adminer['_cag_title'] = '';
			$adminer['_cag_enable'] = '';
		}

		// 上次登录时间
		$adminer['_lastlogin'] = rgmdate($adminer['ca_lastlogin']);

		// 移除安全信息
		unset($adminer['ca_password'], $adminer['ca_salt']);

		return $adminer;
	}

	/**
	 * 返回管理员列表
	 * @return array
	 */
	protected function _adminer_list() {

		// 每页显示数
		$perpage = 20;

		// 管理员总数
		$total = $this->_serv_adminer->count_all();
		// 分页显示
		$multi = '';
		// 管理员列表
		$list = array();

		if (!$total) {
			// 如果无数据
			return array($total, $multi, $list);
		}

		// 分页配置
		$pager_options = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
		);
		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		// 管理员列表
		$list = $this->_serv_adminer->fetch_all($pager_options['start'], $pager_options['per_page']);
		// 管理组列表
		$adminergroup_list = $this->_adminergroup_list();

		// 格式化列表输出
		foreach ($list as &$_ca) {
			$_ca = $this->_adminer_format($_ca);
		}
		unset($_ca);

		return array($total, $multi, $list);
	}

	/**
	 * 返回指定 ca_id 的管理员信息
	 * @param number $ca_id
	 * @param boolean $return_default 是否返回表默认值
	 * @return boolean|array
	 */
	protected function _adminer_get($ca_id, $return_default = false) {

		// 找到指定 ca_id 的管理员信息
		$adminer = $this->_serv_adminer->fetch($ca_id);
		if (empty($adminer)) {
			// 如果未找到，且未设置返回默认值
			if ($return_default) {
				// 允许返回默认值
				$adminer = $this->_serv_adminer->fetch_all_field();
			} else {
				return false;
			}
		}

		return $adminer;
	}

	/**
	 * 删除指定ca_id管理员
	 * @param number $ca_id
	 */
	protected function _adminer_delete($ca_id){
		$adminer = $this->_adminer_get($ca_id, false);
		if (!empty($adminer) && $adminer['ca_locked'] != voa_d_cyadmin_common_adminer::LOCKED_SYS) {
			// 存在此管理员且其不是系统默认帐号
			return $this->_serv_adminer->delete($ca_id);
		}
	}

	/**
	 * 编辑管理员信息
	 * @param unknown $adminer 旧数据原始数据
	 * @param unknown $submit 提交的数据
	 * @param string $result_msg <strong>(引用)</strong>返回的消息
	 * @return false|array
	 */
	protected function _adminer_update($adminer = array(), $submit = array(), &$result_msg = '') {

		// 发生更改的数据
		$updated = array();
		//判断上级绑定有没发生改变
		$upid = $submit['upid'];
		$serv_up = &service::factory('voa_s_cyadmin_common_subordinates');
		$un_record = $serv_up->get_by_conds(array('un_id' => $adminer['ca_id']));
		if($un_record){
			if($un_record['ca_id'] != $upid){
				$serv_up->update_by_conds(array('un_id' => $adminer['ca_id']), array('ca_id' => $upid));
				//数据有变动
				$update ['ca_id'] = $adminer['ca_id'];
			}else{
				//数据没有变动			
			}
		}else{
			$serv_up->insert(array('ca_id' => $upid, 'un_id' => $adminer['ca_id']));
		}
		unset($submit['upid']);
		// 检查哪些数据改变了
		if ($adminer['ca_id']) {
			foreach ($adminer as $key => $value) {
				if (isset($submit[$key]) && $submit[$key] != $value) {
					$updated[$key] = $submit[$key];
				}
			}
		} else {
			$updated = $submit;
		}

		if (isset($updated['ca_locked'])) {
			if ($adminer['ca_locked'] == voa_d_cyadmin_common_adminer::LOCKED_SYS || $updated['ca_locked'] == voa_d_cyadmin_common_adminer::LOCKED_SYS) {
				// 任何时候都禁止修改变更系统最高管理员
				unset($updated['ca_locked']);
			}
		}

		if (empty($updated)) {
			$result_msg = '没有发生改变的数据，无须提交';
			return false;
		}

		if (!$adminer['ca_id']) {
			if (!isset($updated['ca_username'])) {
				$result_msg = '登录名必须设置';
				return false;
			}
			if (!isset($updated['ca_password'])) {
				$result_msg = '登录密码必须设置';
				return false;
			}
			if (!isset($updated['cag_id'])) {
				$result_msg = '所属管理组必须选择';
				return false;
			}
			if ($adminer['ca_locked'] == voa_d_cyadmin_common_adminer::LOCKED_SYS) {
				if (isset($updated['cag_id'])) {
					$result_msg = '系统帐号禁止修改管理组';
					return false;
				}
				if (isset($updated['ca_locked'])) {
					$result_msg = '系统帐号禁止修改登录状态';
					return false;
				}
			}
		}


		// 检查登录名
		if (isset($updated['ca_username'])) {
			$updated['ca_username'] = (string)$updated['ca_username'];
			if (!validator::is_len_in_range($updated['ca_username'], $this->_username_length_limit['min'], $this->_username_length_limit['max'])) {
				$result_msg = '登录名长度必须限制 '.$this->_username_length_limit['min'].'到'.$this->_username_length_limit['max'].' 字节之间';
				return false;
			}
			if ($updated['ca_username'] != rhtmlspecialchars($updated['ca_username'])) {
				$result_msg = '登录名禁止包含特殊字符';
				return false;
			}
			if ($this->_serv_adminer->count_by_username_notid($updated['ca_username'], $adminer['ca_id']) > 0) {
				$result_msg = '登录名“'.$updated['ca_username'].'”已经被使用，请更换一个';
				return false;
			}
			$update['ca_username'] = $updated['ca_username'];
		}

		// 检查登录密码
		if (isset($updated['ca_password']) && $updated['ca_password'] != '') {
			$updated['ca_password'] = (string)$updated['ca_password'];
			$salt = random(6);
			$passwd = $this->_generate_passwd($updated['ca_password'], $salt);
			$update['ca_salt'] = $salt;
			$update['ca_password'] = $passwd;
		}

		// 检查管理组设置
		if (isset($updated['cag_id'])) {
			$updated['cag_id'] = rintval($updated['cag_id'], false);
			if (!$this->_get_usergroup($updated['cag_id'])) {
				$result_msg = '所选管理组不存在';
				return false;
			}
			$update['cag_id'] = $updated['cag_id'];
		}

		// 登录锁定状态
		if ($adminer['ca_locked'] != voa_d_cyadmin_common_adminer::LOCKED_SYS && isset($updated['ca_locked'])) {
			$updated['ca_locked'] = rintval($updated['ca_locked'], false);
			if (!isset($this->_adminer_locked_map[$updated['ca_locked']])) {
				$result_msg = '请正确选择是否锁定登录';
				return false;
			}
			$update['ca_locked'] = $updated['ca_locked'];
		}

		// 检查真实姓名
		if (isset($updated['ca_realname'])) {
			$updated['ca_realname'] = (string)$updated['ca_realname'];
			if ($updated['ca_realname'] == '' && !validator::is_realname($updated['ca_realname'])) {
				$result_msg = '真实姓名长度应该小于 45 字节且不能包含特殊字符';
			}
			$update['ca_realname'] = $updated['ca_realname'];
		}

		// 手机号码
		if (isset($updated['ca_mobilephone'])) {
			$updated['ca_mobilephone'] = (string)$updated['ca_mobilephone'];
			if ($updated['ca_mobilephone'] != '' && !validator::is_mobile($updated['ca_mobilephone'])) {
				$result_msg = '请填写正确的手机号码';
			}
			$update['ca_mobilephone'] = $updated['ca_mobilephone'];
		}
		// 职位
		if (isset($updated['ca_job'])) {
			$updated['ca_job'] = (string)$updated['ca_job'];

			$update['ca_job'] = $updated['ca_job'];
		}
		// 邮箱
		if (isset($updated['ca_email'])) {	
			$update['ca_email'] = $updated['ca_email'];
		}
		if (empty($update)) {
			$result_msg = '数据未改动无须提交';
			return false;
		}

		if ($adminer['ca_id']) {
			// 编辑

			$this->_serv_adminer->update($update, $adminer['ca_id']);
		} else {
			// 新增
			$this->_serv_adminer->insert($update);
		}

		if ($adminer['ca_id'] && $adminer['ca_id'] == $this->_user['ca_id'] && isset($update['ca_password'])) {
			// 如果当前修改的是正在登录的用户自身的密码，则重新生成cookie信息

			$skey = $this->_generate_skey($adminer['ca_username'], $update['ca_password']);
			$cookie_life = $this->session->getx('adminer_remember') ? intval($this->session->getx('adminer_remember')) : 0;
			$this->session->setx('username', $adminer['ca_username'], $cookie_life);
			$this->session->setx('skeycp', $skey, $cookie_life);
			unset($skey, $cookie_life);
		}

		// 返回已更新的数据
		return $update;
	}

}
