<?php

/**
 * 编辑帖子
 * $Author$
 * $Id$
 *
 */
class voa_c_frontend_thread_edit extends voa_c_frontend_thread_base
{

	public function execute()
	{
		$t_id = rintval($this->request->get('t_id'));
		$serv_t = &service::factory('voa_s_oa_thread', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$thread = $serv_t->fetch_by_id($t_id);
		// 判断是否有权限
		if ($thread['m_uid'] != startup_env::get('wbs_uid')) {
			$this->_error_message('无权限编辑');
		}

		// 查询主题内容
		$serv_tp = &service::factory('voa_s_oa_thread_post', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$threadPost = $serv_tp->fetch_thread_by_tid($t_id);
		$thread = array_merge($threadPost, $thread);
		$thread['_rtime_show'] = rgmdate($thread['t_remindtime'], 'n月j日 H:i');
		$thread['_rtime_hide'] = rgmdate($thread['t_remindtime'], 'Y-m-d H:i:s');

		// 查询权限人信息列表
		$serv_tpu = &service::factory('voa_s_oa_thread_permit_user', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$permit_list = $serv_tpu->fetch_by_t_id($t_id);

		if ($this->_is_post()) {
			$this->_submit();
			$this->_error_message('undefined_action');
			return false;
		}

		// 获取有权限查看的用户信息
		$uids = array();
		foreach ($permit_list as $v) {
			$uids[] = $v['m_uid'];
		}

		$uids_str = implode(',', $uids);
		$serv_m = &service::factory('voa_s_oa_member', array(
			'pluginid' => 0
		));
		$users = $serv_m->fetch_all_by_ids($uids);

		$this->view->set('form_action', '/thread/edit/' . $t_id . '?handlekey=post');
		$this->view->set('permit_list', $permit_list);
		$this->view->set('navtitle', '主题编辑');

		// 编辑内容页
		$this->_output('thread/post');
	}

	protected function _submit()
	{
		// 主题和内容不能为空
		$subject = trim($this->request->get('subject'));
		$message = trim($this->request->get('message'));
		if (! $subject) {
			$this->_error_message('标题不能为空');
		}

		if (! $message) {
			$this->_error_message('内容不能为空');
		}

		// 提醒时间
		$remindtime = rstrtotime($this->request->get('remindtime'));

		// 获取分享用户 uid
		$count = 0;
		$uids = $old_uids = $add_uids = $delIds = array();
		foreach (explode(',', $this->request->get('uids')) as $k => $v) {
			$v = intval($v);
			if ($v && $v != startup_env::get('wbs_uid')) {
				$uids[$v] = $v;
				$count ++;
			}
		}

		// 如果有 uid 则说明分享给指定用户
		if ($count > 0) {
			$friend = voa_d_oa_thread::FRIEND_SOME;
		} else {
			$friend = voa_d_oa_thread::FRIEND_ME;
		}

		foreach ($permit_list as $k => $v) {
			$uid_tmp = $v['m_uid'];
			if (! $uids[$uid_tmp] && $uid_tmp != startup_env::get('wbs_uid')) {
				$delIds[] = $v['tpu_id'];
			}

			$old_uids[] = $uid_tmp;
		}

		$uids = array_values($uids);
		$add_uids = array_diff($uids, $old_uids);

		// 根据 uids 查出所有的用户
		$serv_m = &service::factory('voa_s_oa_member', array(
			'pluginid' => 0
		));
		$members = $serv_m->fetch_all_by_ids($add_uids);

		$serv_t = &service::factory('voa_s_oa_thread', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$serv_tp = &service::factory('voa_s_oa_thread_post', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$serv_tpu = &service::factory('voa_s_oa_thread_permit_user', array(
			'pluginid' => startup_env::get('pluginid')
		));
		try {
			$serv_m->begin();

			// 更新主题
			$threadData = array(
				't_subject' => $subject,
				't_friend' => $friend
			);
			// 如果用户修改了提醒时间, 则
			if (0 < $remindtime && $remindtime > startup_env::get('timestamp')) {
				$threadData['t_remindtime'] = $remindtime;
			}

			$serv_t->update($threadData, array(
				't_id' => $t_id
			));

			// 更新内容
			$post_data = array(
				'tp_subject' => $subject,
				'tp_message' => $message
			);
			$serv_tp->update($post_data, array(
				'tp_id' => $thread['tp_id']
			));

			// 更新权限, 删除多余的
			$serv_tpu->delete_by_ids($delIds);
			// 增加
			foreach ($members as $m) {
				$tpu = array(
					't_id' => $t_id,
					'm_uid' => $m['m_uid'],
					'm_username' => $m['m_username']
				);
				$serv_tpu->insert($tpu);
			}

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			$this->_error_message('编辑失败');
		}

		// 发送微信模板消息

		$this->_success_message('操作成功', '/thread/viewthread/' . $t_id);
	}
}
