<?php
/**
 * 编辑投票信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_vote_edit extends voa_c_frontend_vote_base {
	protected $_p_uids = array();
	protected $_v_id;
	protected $_vote;

	public function execute() {
		/** 获取投票信息 */
		$this->_v_id = intval($this->request->get('v_id'));
		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => startup_env::get('pluginid')));
		$this->_vote = $serv_v->fetch_by_id($this->_v_id);
		if (empty($this->_vote) || startup_env::get('wbs_uid') != $this->_vote['m_uid']) {
			$this->_error_message('当前投票记录不存在');
		}

		/** 如果投票已经开始 */
		if ($this->_vote['v_begintime'] < startup_env::get('timestamp')) {
			$this->_error_message('投票已经开始, 不能编辑');
		}

		/** 如果已经结束 */
		if ($this->_vote['v_endtime'] < startup_env::get('timestamp')) {
			$this->_error_message('当前投票已经结束, 不能编辑');
		}

		/** 读取所有选项 */
		$serv_vo = &service::factory('voa_s_oa_vote_option', array('pluginid' => startup_env::get('pluginid')));
		$options = $serv_vo->fetch_by_v_id($this->_v_id);

		/** 读取允许投票的用户 */
		$serv_vpu = &service::factory('voa_s_oa_vote_permit_user', array('pluginid' => startup_env::get('pluginid')));
		$permit_users = $serv_vpu->fetch_by_v_id($this->_v_id);
		foreach ($permit_users as $k => $u) {
			if (0 == $u['m_uid']) {
				unset($permit_users[$k]);
				continue;
			}

			$this->_p_uids[$u['m_uid']] = $u['m_uid'];
		}

		/** 处理编辑 */
		if ($this->_is_post()) {
			$this->_edit();
		}

		/** 时间格式化 */
		$this->_vote['_begintime'] = rgmdate($this->_vote['v_begintime'], 'Y-m-d H:i');
		$this->_vote['_endtime'] = rgmdate($this->_vote['v_endtime'], 'Y-m-d H:i');

		/** 根据用户 uid 读取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($this->_p_uids);
		voa_h_user::push($users);
		/** form 地址 */
		$this->view->set('form_action', '/vote/edit/'.$this->_v_id.'/post');
		$this->view->set('vote', $this->_vote);
		$this->view->set('users', $users);
		$this->view->set('options', $options);
		/** 开始/结束时间 */
		$this->view->set('b_ymd', rgmdate($this->_vote['v_begintime'], 'Y/m/d'));
		$this->view->set('e_ymd', rgmdate($this->_vote['v_endtime'], 'Y/m/d'));
		/** 时间选择起点 */
		$this->view->set('range_start', rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
		/** 允许的用户uid */
		$this->view->set('uids_str', implode(',', $this->_p_uids));

		$this->_output('vote/post');
	}

	/** 获取有权限投票的用户 */
	function _get_permit_user() {
		$uids = trim($this->request->get('uids'));
		$uid_arr = explode(',', $this->request->get('uids'));
		/** 剔重 */
		$uid_arr = array_unique($uid_arr);
		/** 获取需要删除的投票用户 */
		$del_uids = array_diff($this->_p_uids, $uid_arr);
		/** 获取新增投票用户*/
		$uid_new = array_diff($uid_arr, $this->_p_uids);
		$users = array();
		$friend = voa_d_oa_vote::FRIEND_ALL;
		if (!empty($uid_new)) {
			$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $serv_m->fetch_all_by_ids($uid_new);
		}

		/** 如果选择了投票用户 */
		if (0 < count($users) - count($del_uids) + count($this->_p_uids)) {
			$friend = voa_d_oa_vote::FRIEND_ONLY;
		} else {
			$users = array(array('m_uid' => 0));
		}

		return array($users, $friend, $del_uids);
	}

	/** 更新可投票用户 */
	function _update_permit_user($new, $users, $del_uids) {
		if ($this->_vote['v_friend'] == $new['v_friend'] && voa_d_oa_vote::FRIEND_ALL == $this->_vote['v_friend']) {
			return;
		}

		$serv_vpu = &service::factory('voa_s_oa_vote_permit_user', array('pluginid' => startup_env::get('pluginid')));
		/** 如果当前投票不限用户 */
		if ($new['v_friend'] == voa_d_oa_vote::FRIEND_ALL) {
			$serv_vpu->delete_by_v_id($this->_v_id);
		} else {
			/** 当前投票指定用户并且更新前不指定, 则 */
			if ($this->_vote['v_friend'] == voa_d_oa_vote::FRIEND_ALL) {
				$serv_vpu->delete_by_v_id($this->_v_id);
			}
		}

		/** 插入新投票用户 */
		foreach ($users as $u) {
			$serv_vpu->insert(array(
				'v_id' => $this->_v_id,
				'm_uid' => $u['m_uid'],
				'm_username' => $u['m_username']
			));
		}

		/** 删除投票用户 */
		if (!empty($del_uids)) {
			$serv_vpu->delete_by_v_id_uid($this->_v_id, $del_uids);
		}
	}

	/** 处理编辑操作 */
	function _vote_edit() {
		/** 投票开始/结束时间 */
		$begintime = rstrtotime($this->request->get('begintime'));
		$endtime = rstrtotime($this->request->get('endtime'));
		if ($endtime < startup_env::get('timestamp')) {
			$this->_error_message('结束时间必须大于当前时间');
		}

		if ($begintime >= $endtime) {
			$this->_error_message('结束时间必须大于开始时间');
		}

		/** 主题/内容 */
		$subject = trim($this->request->get('subject'));
		$message = trim($this->request->get('message'));
		if (0 >= strlen($subject)) {
			$this->_error_message('主题不能为空');
		}

		/** 新的选项 */
		$options = array();
		foreach ($this->request->get('options') as $v) {
			$v = trim($v);
			if (!empty($v)) {
				$options[] = $v;
			}
		}

		/** 旧的选项 */
		$oldoptions = array();
		$del_vo_ids = array();
		foreach ($this->request->get('oldoptions') as $k => $v) {
			$v = trim($v);
			if (!empty($v)) {
				$oldoptions[$k] = $v;
			} else {
				$del_vo_ids[] = $k;
			}
		}

		if (2 > count($options) + count($oldoptions)) {
			$this->_error_message('请输入投票选项, 最少2个选项');
		}

		/** 是否多选 */
		$ismulti = intval($this->request->get('ismulti'));
		/** 最少/多项 */
		$minchoices = intval($this->request->get('minchoices'));
		$maxchoices = intval($this->request->get('maxchoices'));
		$minchoices = 1 > $minchoices ? 1 : $minchoices;
		$maxchoices = $minchoices > $maxchoices ? $minchoices : $maxchoices;

		/** 是否开启 */
		$isopen = intval($this->request->get('isopen'));
		/** 对内还是对外 */
		$inout = intval($this->request->get('inout'));

		/** 获取指定投票用户 */
		list($users, $friend, $del_uids) = $this->_get_permit_user();

		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => startup_env::get('pluginid')));
		$serv_vo = &service::factory('voa_s_oa_vote_option', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_v->begin();

			/** 入库 */
			$vote_u = array(
				'v_subject' => $subject,
				'v_message' => $message,
				'v_begintime' => $begintime,
				'v_endtime' => $endtime,
				'v_friend' => $friend,
				'v_ismulti' => $ismulti,
				'v_minchoices' => $minchoices,
				'v_maxchoices' => $maxchoices,
				'v_isopen' => $isopen,
				'v_inout' => $inout
			);
			$serv_v->update($vote_u, array('v_id' => $this->_v_id));

			/** 投票用户 */
			$this->_update_permit_user($vote_u, $users, $del_uids);

			/** 新选项 */
			foreach ($options as $v) {
				$serv_vo->insert(array(
					'v_id' => $this->_v_id,
					'vo_option' => $v
				));
			}

			/** 更新选项 */
			foreach ($oldoptions as $k => $v) {
				$serv_vo->update(array(
					'vo_option' => $v
				), array('vo_id' => $k, 'v_id' => $this->_v_id));
			}

			/** 删除不要的选项 */
			if (!empty($del_vo_ids)) {
				$serv_vo->delete_by_v_id_vo_ids($this->_v_id, $del_vo_ids);
			}

			$serv_v->commit();
		} catch (Exception $e) {
			$serv_v->rollback();
			$this->_error_message('投票新增操作失败');
		}

		/** 增加成功 */
		$this->_success_message('投票编辑操作成功', "/vote/view/{$this->_v_id}");
	}
}

