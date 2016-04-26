<?php
/**
 * voa_c_admincp_office_vote_edit
 * 企业后台/应用宝/微评选/编辑
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_vote_edit extends voa_c_admincp_office_vote_base {

	/** 当前投票选项 */
	private $_options = array();
	/** 当前允许投票人员 */
	private $_permit_users = array();
	/** 当前投票设置 */
	private $_vote = array();
	/** 当前投票id */
	private $_v_id = 0;
	/** 所有用户列表 */
	private $_members = array();

	public function execute() {

		$v_id = $this->request->get('v_id');
		$v_id = rintval($v_id, false);
		if ($v_id < 1 || !($vote = parent::_get_vote($this->_module_plugin_id, $v_id))) {
			$this->message('error', '指定投票不存在或已删除');
		}

		$this->_v_id = $v_id;
		$this->_vote = $vote;
		$this->_options = parent::_get_vote_option($this->_module_plugin_id, $v_id);
		$this->_permit_users = parent::_get_vote_permit_user($this->_module_plugin_id, $v_id);
		$serv = &service::factory('voa_s_oa_member');
		$this->_members = $serv->fetch_all($start, $limit);

		if ($this->_is_post()) {
			$this->_edit_submit();
		}

		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('v_id' => $v_id)));
		$this->view->set('vote', $vote);
		$this->view->set('options', $this->_options);
		$this->view->set('permit_users', $this->_permit_users);
		$this->view->set('memberList', $this->_members);
		$this->view->set('ismulti', $this->_vote_ismulti);
		$this->view->set('isopen', $this->_vote_isopen);
		$this->view->set('inout', $this->_vote_inout);

		$this->output('office/vote/edit_form');

	}

	/**
	 * 保存编辑提交
	 * @param array $old
	 */
	protected function _edit_submit() {

		/** 获取指定投票用户 */
		list($delete_m_ids, $new_users, $friend) = self::_reset_permit_user();

		/** 重新整理投票选项 */
		list($option_count, $new_option, $edit_option, $delete_vo_ids) = self::_reset_option();
		if ( 2 > $option_count) {
			$this->message('error', '投票选项至少应该设置2项');
		}

		/** 获取更新的投票设置 */
		$voteUpdate = self::_reset_vote($option_count, $friend);

		/** 开始更新过程 */
		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => $this->_module_plugin_id));
		$serv_vo = &service::factory('voa_s_oa_vote_option', array('pluginid' => $this->_module_plugin_id));
		$serv_vpu = &service::factory('voa_s_oa_vote_permit_user', array('pluginid' => $this->_module_plugin_id));
		$serv_vm = &service::factory('voa_s_oa_vote_mem', array('pluginid' => $this->_module_plugin_id));
		try {
			$serv_v->begin();

			/** 更新投票主表 */
			$serv_v->update($voteUpdate, array('v_id' => $this->_v_id));

			/** 开始更新投票用户 */
			/*** 删除投票的用户 **/
			if (!empty($delete_m_ids)) {
				$serv_vpu->delete_by_v_id_uid($this->_v_id, $delete_m_ids);
			}
			/*** 新增允许投票的用户 **/
			if (!empty($new_users)) {
				foreach ($new_users AS $_data) {
					$serv_vpu->insert(array(
							'v_id' => $this->_v_id,
							'm_uid' => $_data['m_uid'],
							'm_username' => $_data['m_username']
					));
				}
			}

			/** 开始更新投票选项 */
			/*** 增加新选项 **/
			if (!empty($new_option)) {
				foreach ($new_option AS $_o) {
					$serv_vo->insert($_o);
				}
			}
			/*** 删除选项 **/
			if (!empty($delete_vo_ids)) {
				$serv_vo->delete_by_id($delete_vo_ids);
				/** 删除对应的投票记录 */
				$serv_vm->delete_by_vo_id($delete_vo_ids);
			}
			/*** 更新选项 **/
			if (!empty($edit_option)) {
				foreach ($edit_option AS $_vo_id => $_data) {
					$serv_vo->update($_data, array('vo_id' => $_vo_id));
				}
			}

			/** 提交过程 */
			$serv_v->commit();
		} catch (Exception $e) {
			$serv_v->rollback();
			$this->message('error', '投票更新操作失败');
		}

		$this->message('success', '编辑投票信息操作完毕', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('v_id' => $this->_v_id)), false);

	}

	/**
	 * 获取并判断检查投票设置信息
	 * @param number $option_count
	 * @return array
	 */
	protected function _reset_vote($option_count, $friend) {
		$old = $this->_vote;
		$v_id = $old['v_id'];

		/** 投票字段 */
		$vote_fields = array(
				'v_subject', 'v_message', 'v_begintime', 'v_endtime', 'v_ismulti', 'v_minchoices', 'v_maxchoices', 'v_isopen', 'v_inout', 'v_voters'
		);
		$new = array();
		foreach ($vote_fields AS $_key) {
			$_value = $this->request->post($_key);
			if (!is_scalar($_value)) {
				continue;
			}
			if (in_array($_key, array('v_begintime', 'v_endtime'))) {
				if (!validator::is_date($_value)) {
					$this->message('error', '起始时间格式设置错误');
				} else {
					$_value = rstrtotime($_value);
				}
			}
			if ($_value != $old[$_key]) {
				$new[$_key] = $_value;
			}
		}

		$voteUpdate = array();
		if (isset($new['v_subject'])) {
			if (!is_scalar($new['v_subject']) || !validator::is_len_in_range($new['v_subject'], 1, 81)) {
				$this->message('error', '投票主题必须填写，且长度应该小于81字节');
			}
			$voteUpdate['v_subject'] = trim($new['v_subject']);
		}

		if (isset($new['v_message'])) {
			if (!validator::is_len_in_range($new['v_message'], -1, 2000)) {
				$this->message('error', '请限制投票详情描述文字长度小于2000字节');
			}
			$voteUpdate['v_message'] = $new['v_message'];
		}

		$begintime = isset($new['v_begintime']) ? $new['v_begintime'] : $old['v_begintime'];
		$endtime = isset($new['v_endtime']) ? $new['v_endtime'] : $old['v_endtime'];
		if ($endtime < $begintime) {
			$this->message('error', '投票结束时间应该大于开始时间');
		}
		if ($begintime != $old['v_begintime']) {
			$voteUpdate['v_begintime'] = $begintime;
		}
		if ($endtime != $old['v_endtime']) {
			$voteUpdate['v_endtime'] = $endtime;
		}

		if (isset($this->_vote_friend[$friend]) && $friend != $old['v_friend']) {
			$voteUpdate['v_friend'] = $friend;
		}

		if (isset($new['v_ismulti']) && isset($this->_vote_friend[$new['v_ismulti']])) {
			$voteUpdate['v_ismulti'] = $new['v_ismulti'];
		}

		if (isset($new['v_isopen']) && isset($this->_vote_isopen[$new['v_isopen']])) {
			$voteUpdate['v_isopen'] = $new['v_isopen'];
		}

		if (isset($new['v_inout']) && isset($this->_vote_inout[$new['v_inout']])) {
			$voteUpdate['v_inout'] = $new['v_inout'];
		}

		$_minchoices = $old['v_minchoices'];
		$_maxchoices = $old['v_maxchoices'];
		if (isset($new['v_minchoices']) && validator::is_int($new['v_minchoices']) && $new['v_minchoices'] > 0) {
			$_minchoices = rintval($new['v_minchoices'], false);
			if ($_minchoices > $option_count) {
				$_minchoices = $option_count;
			}
		}
		if (isset($new['v_maxchoices']) && validator::is_int($new['v_maxchoices']) && $new['v_maxchoices'] > 0) {
			$_maxchoices = rintval($new['v_maxchoices'], false);
			if ($_maxchoices > $option_count) {
				$_maxchoices = $option_count;
			}
		}
		if ($_maxchoices < $_minchoices) {
			$_maxchoices = $_minchoices;
		}
		if ($_minchoices != $old['v_minchoices']) {
			$voteUpdate['v_minchoices'] = $_minchoices;
		}
		if ($_maxchoices != $old['v_maxchoices']) {
			$voteUpdate['v_maxchoices'] = $_maxchoices;
		}

		return $voteUpdate;
	}

	/**
	 * 重新整理允许投票的用户
	 * @return array(delete_list, insert_list, friend)
	 */
	protected function _reset_permit_user() {

		/** 获取提交过来的用户uid，并整理过滤出数据为有效的 */
		$uid_arr = array();
		foreach (rintval($this->request->post('uids'), true) AS $_uid) {
			if ($_uid > 0 && validator::is_int($_uid) && !isset($uid_arr[$_uid])) {
				$uid_arr[$_uid] = $_uid;
			}
		}

		$users = array();
		$friend = voa_d_oa_vote::FRIEND_ALL;
		if (!empty($uid_arr)) {
			$users = $this->_service_single('member', 'fetch_all_by_ids', $uid_arr);
		}
		/** 根据用户数判断 */
		if (!empty($users) ) {
			$friend = voa_d_oa_vote::FRIEND_ONLY;
		} else {
			$users = array(array('m_uid' => 0,'m_username' => ''));
		}

		/** 重新整理旧的投票用户uid */
		$old_users = array();
		foreach ($this->_permit_users AS $_vpu) {
			$old_users[$_vpu['m_uid']] = $_vpu['m_uid'];
		}

		/** 重新整理投票用户 */
		/*** 新增的 **/
		$new_users = array();
		foreach ($users AS $_user) {
			/** 无效的用户 */
			if ($_user['m_uid'] && !isset($this->_members[$_user['m_uid']])) {
				continue;
			}
			/** 旧的允许投票用户中不存在此人，则加入到新增用户列表 */
			if (!isset($old_users[$_user['m_uid']])) {
				$new_users[$_user['m_uid']] = $_user;
			}
		}

		/*** 需要移除的 **/
		$delete_m_ids = array();
		foreach ($old_users AS $_m_uid => $_vpu_id) {
			if (!isset($users[$_m_uid])) {
				$delete_m_ids[] = $_vpu_id;
			}
		}

		return array($delete_m_ids, $new_users, $friend);

	}

	/**
	 * 重新整理投票选项
	 * @return array(number option_count, array new, array edit, array delete)
	 */
	protected function _reset_option() {

		/** 重新整理后的选项数 */
		$option_count = 0;
		/** 需要删除的选项 */
		$delete_vo_id = $this->request->post('delete_vo_id');
		/** 新增的选项 */
		$post_new_option = $this->request->post('new_option');
		/** 修改的选项 */
		$post_edit_option = $this->request->post('edit_option');

		!is_array($delete_vo_id) && $delete_vo_id = array();
		!is_array($post_new_option) && $post_new_option = array();
		!is_array($post_edit_option) && $post_edit_option = array();

		$new_option = $edit_option = array();
		/*** 整理新增选项 **/
		foreach ($post_new_option AS $_v) {
			if (!is_scalar($_v)) {
				continue;
			}
			$_v = trim($_v);
			/** 选项文字过长或为空 */
			if ($_v == '' || isset($_v{81})) {
				continue;
			}
			$new_option[] = array('v_id' => $this->_v_id, 'vo_option' => $_v);
			$option_count++;
		}
		/*** 整理修改的选项 **/
		foreach ($post_edit_option AS $_vo_id => $_vo) {
			/** 不是旧选项 或 已标记为删除则跳过 */
			if (!isset($this->_options[$_vo_id]) || isset($delete_vo_id[$_vo_id])) {
				continue;
			}
			/** 非法字符 */
			if (!is_scalar($_vo)) {
				continue;
			}
			$_vo = trim($_vo);
			/** 长度不符合要求 */
			if ($_vo == '' || isset($_vo{81})) {
				continue;
			}
			/** 选项发生改动 */
			if ($_vo != $this->_options[$_vo_id]['vo_option']) {
				$edit_option[$_vo_id] = array('vo_option' => $_vo);
			}
			$option_count++;
		}

		return array($option_count, $new_option, $edit_option, $delete_vo_id);

	}

}
