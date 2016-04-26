<?php
/**
 * voa_c_admincp_office_project_advanced
 * 企业后台/微办公管理/工作台/项目推进
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_advanced extends voa_c_admincp_office_project_base {

	/** 项目id */
	protected $_p_id = 0;
	/** 项目信息 */
	protected $_project = array();
	/** 项目参与人 uid */
	protected $_p_uids = array();
	/** 项目参与人信息 */
	protected $_p_users = array();
	/** 退出项目的人员 */
	protected $_quit_uids = array();

	public function execute() {

		$this->_p_id = rintval($this->request->get('p_id'), false);
		/** 读取项目信息 */
		$this->_project = $this->_service_single('project', $this->_module_plugin_id, 'fetch_by_id', $this->_p_id);
		if (empty($this->_project)) {
			$this->message('error', '该项目不存在或已删除');
		}

		/** 读取项目所有用户 */
		$tmp = $this->_service_single('project_mem', $this->_module_plugin_id, 'fetch_by_p_id', $this->_p_id);
		foreach ($tmp as $m) {

			/** 如果是抄送 */
			if (voa_d_oa_project_mem::STATUS_CC == $m['pm_status']) {
				continue;
			}

			/** 如果是退出状态 */
			if (voa_d_oa_project_mem::STATUS_QUIT == $m['pm_status']) {
				$this->_quit_uids[$m['m_uid']] = $m['m_uid'];
				continue;
			}

			$this->_p_uids[$m['m_uid']] = $m['m_uid'];
		}

		/** 读取用户信息 */
		$this->_p_users = $this->_service_single('member', $this->_module_plugin_id, 'fetch_all_by_ids', $this->_p_uids);
		voa_h_user::push($this->_p_users);

		/** 处理提交 */
		if ($this->_is_post()) {
			$this->_submit();
		}

		/** 所有项目人员uid */
		$this->view->set('proj_uids', implode(',', $this->_p_uids));
		$this->view->set('ac', $this->action_name);
		$this->view->set('users', $this->_p_users);
		$this->view->set('refer', get_referer());
		$this->view->set('p_id', $this->_p_id);

		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id, array('p_id' => $this->_p_id)));

		$this->output('office/project/advanced');

	}

	/** 处理提交 */
	protected function _submit() {
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$serv_pm = &service::factory('voa_s_oa_project_mem', array('pluginid' => $this->_module_plugin_id));
		$serv_pp = &service::factory('voa_s_oa_project_proc', array('pluginid' => $this->_module_plugin_id));

		$message = trim($this->request->post('message'));
		if (empty($message)) {
			$this->message('error', '推进消息不能为空');
		}

		$p_uids = rintval($this->request->post('project_uids'), true);
		if (empty($p_uids)) {
			$this->message('error', '项目人员不能为空');
		}

		/** 读取项目人员信息 */
		$new_uids = array_diff($p_uids, $this->_p_uids);
		$new_users = array();
		if (!empty($new_uids)) {
			$new_users = $serv_m->fetch_all_by_ids($new_uids);
			if (empty($new_users)) {
				$this->_error_message('项目人员不能为空');
			}
		}

		/** 数据入库 */
		try {
			$serv_m->begin();

			/** 新增项目人员 */
			foreach ($new_uids as $uid) {
				/** 如果是新加入的用户 */
				if (empty($this->_quit_uids[$uid])) {
					$serv_pm->insert(array(
						'p_id' => $this->_p_id,
						'm_uid' => $uid,
						'm_username' => $new_users[$uid]['m_username'],
						'pm_status' => voa_d_oa_project_mem::STATUS_NORMAL
					));
				} else {
					/** 如果该用户是之前退出的 */
					$serv_pm->update(array(
						'pm_progress' => 0,
						'pm_status' => voa_d_oa_project_mem::STATUS_NORMAL
					), array('p_id' => $this->_p_id, 'm_uid' => $uid));
				}

				/** 非项目初始人员, 增加一条进度信息 */
				$serv_pp->insert(array(
					'p_id' => $this->_p_id,
					'm_uid' => $uid,
					'm_username' => $new_users[$uid]['m_username'],
					'pp_message' => '加入'
				));
			}

			$quit_uids = array();
/*
			$quit_uids = array_diff($this->_p_uids, $p_uids);
			foreach ($quit_uids as $uid) {
				$serv_pm->update(array(
					'p_id' => $this->_p_id,
					'm_uid' => $uid,
					'm_username' => $this->_p_users[$uid]['m_username'],
					'pm_status' => voa_d_oa_project_mem::STATUS_QUIT
				), array('p_id' => $this->_p_id, 'm_uid' => $uid));

				//退出时, 增加一条退出记录(进度)
				$serv_pp->insert(array(
					'p_id' => $this->_p_id,
					'm_uid' => $uid,
					'm_username' => $this->_p_users[$uid]['m_username'],
					'pp_message' => '退出'
				));
			}
*/
			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			/** 如果 $id 值为空, 则说明入库操作失败 */
			$this->_error_message('项目新增失败');
		}

		/** 给抄送人发送模板消息 */
		$allusers = array_merge($this->_p_users, $new_users);
		$openids = array();
		foreach ($allusers as $_u) {
			if (array_key_exists($_u['m_uid'], $quit_uids)) {
				continue;
			}

			$openids[] = $_u['m_openid'];
		}

		/** 发微信消息 */
		$serv_qy = voa_wxqy_service::instance();
		$serv_qy->post_text($message, $this->_module_plugin['cp_agentid'], $openids);

		$this->message('error', '项目推进操作成功', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('p_id' => $this->_p_id)));
	}

}
