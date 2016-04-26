<?php
/**
 * 请假相关的更新操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askoff_update extends voa_uda_frontend_askoff_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 拒绝操作
	 * @param array $askoff 请假详情信息
	 * @param array $proc 请假进度信息
	 * @return boolean
	 */
	public function askoff_refuse($askoff, $proc) {
		$message = (string)$this->_request->get('message', '');
		if (!$this->val_message($message)) {
			return false;
		}

		$serv = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_askoff_post', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();
			$this->_update_status($askoff['ao_id'], array('ao_status' => voa_d_oa_askoff::STATUS_REFUSE),
				$proc['aopc_id'], array('aopc_status' => voa_d_oa_askoff_proc::STATUS_REFUSE, 'aopc_remark' => $message)
			);

			$serv_pt->insert(array(
				'ao_id' => $askoff['ao_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'aopt_subject' => '',
				'aopt_message' => $message,
				'aopt_first' => voa_d_oa_askoff_post::FIRST_NO
			));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 通过操作
	 * @param array $askoff 请假详情信息
	 * @param array $proc 请假进度信息
	 * @return boolean
	 */
	public function askoff_approve($askoff, $proc) {
		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		/** 判断是否有权限 */
		$adminids = empty($this->_sets['adminids']) || !is_array($this->_sets['adminids']) ? array() : $this->_sets['adminids'];
		if (!empty($adminids) && !in_array(startup_env::get('wbs_uid'), $adminids)) {
			$this->errmsg(100, 'no_privilege');
			return false;;
		}

		$serv = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_askoff_post', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();
			$this->_update_status($askoff['ao_id'], array('ao_status' => voa_d_oa_askoff::STATUS_APPROVE),
				$proc['aopc_id'], array('aopc_status' => voa_d_oa_askoff_proc::STATUS_APPROVE, 'aopc_remark' => $message)
			);

			$serv_pt->insert(array(
				'ao_id' => $askoff['ao_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'aopt_subject' => '',
				'aopt_message' => $message,
				'aopt_first' => voa_d_oa_askoff_post::FIRST_NO
			));

			$serv->commit();
		} catch (Exception $e) {
			$serv->rollback();
			/** 入库操作失败 */
			$this->errmsg(100, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 转审批操作
	 * @param array $askoff 请假详情信息
	 * @param array $proc 请假进度信息
	 * @param array &$approve_proc 转审批进度信息
	 * @param array &$cculit 抄送人信息
	 * @return boolean
	 */
	public function askoff_transmit($askoff, $proc, &$approve_proc, &$cculist) {
		/** 审核人 uid */
		$approveuid = (string)$this->_request->get('approveuid');
		if (!$this->val_approveuid($approveuid)) {
			return false;
		}

		$message = (string)$this->_request->get('message');
		if (!$this->val_message($message)) {
			return false;
		}

		/** 自己不能审批自己的申请 */
		if ($approveuid == $askoff['m_uid']) {
			$this->errmsg(150, 'askoff_verify_self');
			return false;
		}

		/** 抄送人 */
		$uidstr = (string)$this->_request->get('carboncopyuids');
		$ccuids = array();
		if (!$this->val_carboncopyuids($uidstr, $ccuids)) {
			return false;
		}

		/** 从进度中读取抄送人和审核人记录 */
		$all_uids = $ccuids;
		unset($all_uids[startup_env::get('wbs_uid')]);
		/** 获取审核人进度记录和已存在进度的用户 uid */
		$new_uids = array();
		if (!$this->_get_proc_and_uids($askoff['ao_id'], $all_uids, $approveuid, $approve_proc, $new_uids)) {
			return false;
		}

		/** 读取用户信息, 包括审批人和抄送人信息 */
		$ccuids[] = $approveuid;
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$cculist = $serv_m->fetch_all_by_ids($ccuids);

		/** 审批人 */
		$mem = array();
		/** 从用户列表中取出审批人信息 */
		foreach ($cculist as $k => $v) {
			if ($approveuid == $v['m_uid']) {
				$mem = $v;
				unset($cculist[$approveuid]);
				break;
			}
		}

		/** 如果用户不存在 */
		if (empty($mem)) {
			$this->errmsg(151, 'approveuser_not_exist');
			return false;
		}

		$serv_pc = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_askoff_post', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv_m->begin();

			/** 转审批信息入库 */
			if (!empty($approve_proc)) {
				$aopc_id = $approve_proc['aopc_id'];
				$serv_pc->update(array('aopc_status' => voa_d_oa_askoff_proc::STATUS_NORMAL), array('aopc_id' => $aopc_id));
			} else {
				$approve_proc = array(
					'ao_id' => $askoff['ao_id'],
					'm_uid' => $mem['m_uid'],
					'm_username' => $mem['m_username'],
					'aopc_status' => voa_d_oa_askoff_proc::STATUS_NORMAL
				);
				$aopc_id = $serv_pc->insert($approve_proc, true);
			}

			/** 更新审批以及审批进度状态 */
			$this->_update_status($askoff['ao_id'], array('aopc_id' => $aopc_id, 'ao_status' => voa_d_oa_askoff::STATUS_APPROVE_APPLY),
				$proc['aopc_id'], array('aopc_status' => voa_d_oa_askoff_proc::STATUS_APPROVE_APPLY, 'aopc_remark' => $message)
			);

			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				/** 如果是自己 */
				if ($v['m_uid'] == startup_env::get('wbs_uid') || $approveuid == $v['m_uid']) {
					continue;
				}

				$serv_pc->insert(array(
					'ao_id' => $askoff['ao_id'],
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'aopc_status' => voa_d_oa_askoff_proc::STATUS_CARBON_COPY
				));
			}

			$serv_pt->insert(array(
				'ao_id' => $askoff['ao_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'aopt_subject' => '',
				'aopt_message' => $message,
				'aopt_first' => voa_d_oa_askoff_post::FIRST_NO
			));

			$serv_m->commit();
		} catch (Exception $e) {
			$serv_m->rollback();
			/** 入库操作失败 */
			$this->errmsg(152, '操作失败');
			return false;
		}

		return true;
	}

	/**
	 * 更新审核状态
	 * @param int $ao_id 当前申请id
	 * @param int $data 待更新的审核申请信息
	 * @param int $proc_id 进度id
	 * @param int $proc_status 待更新的审核进度信息
	 */
	protected function _update_status($ao_id, $data, $proc_id, $proc_data) {
		/** 更新请假状态 */
		$serv_ao = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		$serv_ao->update($data, array('ao_id' => $ao_id));

		/** 更新请假进度状态 */
		$serv_pc = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$serv_pc->update($proc_data, array('aopc_id' => $proc_id));
		return true;
	}

	/**
	 * 读取审核人进度
	 * @param int $ao_id 请假id
	 * @param array $uids uid 数组
	 * @param int $approveuid 审核人 uid
	 * @param array $proc_cc 审批人的进度信息
	 * @param array $new_uids 新的用户uid数组
	 * @return 返回审核人进度和新抄送人 uid 数组
	 */
	protected function _get_proc_and_uids($ao_id, $uids, $approveuid, &$proc_cc, &$new_uids) {
		/** 审核人进度信息 */
		$uids[] = $approveuid;
		$serv_pc = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv_pc->fetch_by_ao_id($ao_id);
		/** 已经存在于进度的用户 uid */
		$exist_uids = array();
		foreach ($procs as $k => $v) {
			$exist_uids[] = $v['m_uid'];
			/** 如果当前审核人已经在进度中有记录, 则 */
			if ($v['m_uid'] == $approveuid) {
				$proc_cc = $v;
				if ($v['aopc_status'] != voa_d_oa_askoff_proc::STATUS_CARBON_COPY) {
					$this->errmsg(200, 'askoff_duplicte_user');
					return false;
				}
			}
		}

		$new_uids = array_diff($uids, $exist_uids);
		return true;
	}
}
