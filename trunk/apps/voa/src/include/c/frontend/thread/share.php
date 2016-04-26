<?php

/**
 * 共享工作台首页
 * $Author$
 * $Id$
 */
class voa_c_frontend_thread_share extends voa_c_frontend_thread_base
{

	public function execute()
	{
		// 获取 t_id
		$t_id = intval($this->request->get('t_id'));
		$serv_t = &service::factory('voa_s_oa_thread', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$thread = $serv_t->fetch_by_id($t_id);
		// 判断是否有分享权限
		if ($thread['m_uid'] != startup_env::get('wbs_uid')) {
			$this->_error_message('no_privilege');
		}

		// 读取当前主题的分享人列表
		$serv_tpu = &service::factory('voa_s_oa_thread_permit_user', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$tpus = $serv_tpu->fetch_by_t_id($t_id);

		// 取分享人的 uid 数组
		$uids = array();
		foreach ($tpus as $v) {
			$uids[] = $v['m_uid'];
		}

		// 处理 post
		if ($this->_is_post()) {
			$this->_submit();
			$this->_error_message('submit_error');
			return false;
		}

		$serv_m = &service::factory('voa_s_oa_member', array(
			'pluginid' => 0
		));
		$users = $serv_m->fetch_all_by_ids($uids);
		voa_h_user::push($users);

		$this->view->set('uidstr', implode(',', $uids));
		$this->view->set('refer', get_referer());
		$this->view->set('t_id', $t_id);

		// 构造返回给前端的数据
		$this->_output('workbench/share');
	}

	protected function _submit()
	{
		// 总分享人
		$share_uids = explode(',', $this->request->get('shareuids'));
		// 新分享人
		$newuids = array_diff($share_uids, $uids);
		// 需要剔除的分享人
		$deluids = array_diff($uids, $share_uids);
		// 如果没有任何变动, 则
		if (empty($newuids) && empty($deluids)) {
			$this->_error_message('分享操作成功', '/thread/viewthread/' . $t_id);
		}

		// 读取用户信息
		$serv_m = &service::factory('voa_s_oa_member', array(
			'pluginid' => 0
		));
		$users = $serv_m->fetch_all_by_ids(array_merge($newuids, $deluids));
		// 剔除不存在的用户
		$diff = array(
			startup_env::get('wbs_uid')
		);
		foreach ($newuids as $uid) {
			if (empty($users[$uid]) || empty($users[$uid])) {
				$diff[] = $uid;
			}
		}

		// 剔除不存在的用户
		$newuids = array_diff($newuids, $diff);
		$deluids = array_diff($deluids, $diff);

		// 新分享人入库
		$serv_tpu = &service::factory('voa_s_oa_thread_permit_user', array(
			'pluginid' => startup_env::get('pluginid')
		));
		$msg_users = array();
		foreach ($newuids as $uid) {
			if (empty($users[$uid])) {
				continue;
			}

			$msg_users[] = $users[$uid];
			$serv_tpu->insert(array(
				't_id' => $t_id,
				'm_uid' => $uid,
				'm_username' => $users[$uid]['m_username']
			));
		}

		// 需要剔除的分享人
		$serv_tpu->delete_by_t_id_uid($t_id, $deluids);

		// 发送微信模板消息

		$this->_success_message('分享操作成功', '/workbench/viewthread/' . $t_id);
	}
}

