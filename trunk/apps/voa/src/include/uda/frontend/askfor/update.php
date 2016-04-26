<?php
/**
 * 审批相关的更新操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_update extends voa_uda_frontend_askfor_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 拒绝操作
	 * @param array $askfor 审批详情信息
	 * @param array $proc 审批进度信息
	 * @return boolean
	 */
	public function askfor_refuse($askfor, $proc) {
		$message = (string)$this->_request->get('message', '');
		if (!$this->val_message($message)) {
			//return false;
		}

		$serv = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
		try {
			$serv->begin();
			$this->_update_status($askfor['af_id'], array('af_status' => voa_d_oa_askfor::STATUS_REFUSE),
				$proc['afp_id'], array('afp_status' => voa_d_oa_askfor_proc::STATUS_REFUSE, 'afp_note' => $message)
			);

			$serv_pt->insert(array(
				'af_id' => $askfor['af_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'afc_subject' => $message,
				'afc_message' => $message
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
	 * @param array $askfor 审批详情信息
	 * @param array $proc 审批进度信息
	 * @return boolean
	 */
	public function askfor_approve($askfor, $proc) {
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

		$serv = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
		
		try {
			$serv->begin();
			$this->_update_status($askfor['af_id'], array('af_status' => voa_d_oa_askfor::STATUS_APPROVE),
				$proc['afp_id'], array('afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE, 'afp_note' => $message)
			);

			$serv_pt->insert(array(
				'af_id' => $askfor['af_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'afc_subject' => $message,
				'afc_message' => $message,
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
	 * @param array $askfor 审批详情信息
	 * @param array $proc 审批进度信息
	 * @param array &$approve_proc 转审批进度信息
	 * @param array &$cculit 抄送人信息
	 * @return boolean
	 */
	public function askfor_transmit($askfor, $proc, &$approve_proc, &$cculist) {
		/** 审核人 uid */
		$approveuid = (string)$this->_request->get('approveuid');
		if (!$this->val_approveuid($approveuid)) {
			$this->errmsg(150, 'not_approveuid');
			return false;
		}

		$message = (string)$this->_request->get('message');
		
		if (!$this->val_message($message)) {
			return false;
		}
		

		/** 自己不能审批自己的申请 */
		if ($approveuid == $askfor['m_uid']) {
			$this->errmsg(150, 'askfor_verify_self');
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
		if (!$this->_get_proc_and_uids($askfor['af_id'], $all_uids, $approveuid, $approve_proc, $new_uids)) {
			$this->errmsg(150, 'askfor_verify_self');
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

		$serv_pc = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$serv_pt = &service::factory('voa_s_oa_askfor_comment', array('pluginid' => startup_env::get('pluginid')));
	
		try {
			$serv_m->begin();

			/** 转审批信息入库 */
			if (!empty($approve_proc)) {
				$afp_id = $approve_proc['afp_id'];
				$serv_pc->update(array('afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL), array('afp_id' => $afp_id));
			} else {
				$approve_proc = array(
					'af_id' => $askfor['af_id'],
					'm_uid' => $mem['m_uid'],
					'm_username' => $mem['m_username'],
					'afp_status' => voa_d_oa_askfor_proc::STATUS_NORMAL
				);
				$afp_id = $serv_pc->insert($approve_proc, true);
			}

			/** 更新审批以及审批进度状态 */
			$this->_update_status($askfor['af_id'], array('afp_id' => $afp_id, 'af_status' => voa_d_oa_askfor::STATUS_APPROVE_APPLY),
				$proc['afp_id'], array('afp_status' => voa_d_oa_askfor_proc::STATUS_APPROVE_APPLY, 'afp_note' => $message)
			);

			//获取旧抄送人
			$old = $serv_pc->fetch_by_af_id($askfor['af_id']);
			foreach ($old as $a) {
				if($a['afp_status'] == voa_d_oa_askfor_proc::STATUS_CARBON_COPY) {
					$oids[] = $a['m_uid'];
				}
			}
			/** 抄送人信息入库 */
			foreach ($cculist as $v) {
				/** 如果是自己 */
				if ($v['m_uid'] == startup_env::get('wbs_uid') || $approveuid == $v['m_uid']) {
					continue;
				}
				//如果已存在抄送人,忽略
				if(in_array($v['m_uid'], $oids)) {
					continue;
				}
				
				$serv_pc->insert(array(
					'af_id' => $askfor['af_id'],
					'm_uid' => $v['m_uid'],
					'm_username' => $v['m_username'],
					'afp_status' => voa_d_oa_askfor_proc::STATUS_CARBON_COPY
				));
			}
			
			$serv_pt->insert(array(
				'af_id' => $askfor['af_id'],
				'm_uid' => startup_env::get('wbs_uid'),
				'm_username' => startup_env::get('wbs_username'),
				'afc_subject' => $message,
				'afc_message' => $message
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
	 * @param int $af_id 当前申请id
	 * @param int $data 待更新的审核申请信息
	 * @param int $proc_id 进度id
	 * @param int $proc_status 待更新的审核进度信息
	 */
	protected function _update_status($af_id, $data, $proc_id, $proc_data) {
		/** 更新审批状态 */
		$serv_ao = &service::factory('voa_s_oa_askfor', array('pluginid' => startup_env::get('pluginid')));
		$serv_ao->update($data, array('af_id' => $af_id));

		/** 更新审批进度状态 */
		$serv_pc = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$serv_pc->update($proc_data, array('afp_id' => $proc_id));
		return true;
	}

	/**
	 * 读取审核人进度
	 * @param int $af_id 审批id
	 * @param array $uids uid 数组
	 * @param int $approveuid 审核人 uid
	 * @param array $proc_cc 审批人的进度信息
	 * @param array $new_uids 新的用户uid数组
	 * @return 返回审核人进度和新抄送人 uid 数组
	 */
	protected function _get_proc_and_uids($af_id, $uids, $approveuid, &$proc_cc, &$new_uids) {
		/** 审核人进度信息 */
		$uids[] = $approveuid;
		$serv_pc = &service::factory('voa_s_oa_askfor_proc', array('pluginid' => startup_env::get('pluginid')));
		$procs = $serv_pc->fetch_by_af_id($af_id);
		/** 已经存在于进度的用户 uid */
		$exist_uids = array();
		foreach ($procs as $k => $v) {
			$exist_uids[] = $v['m_uid'];
			/** 如果当前审核人已经在进度中有记录, 则 */
			if ($v['m_uid'] == $approveuid) {
				$proc_cc = $v;
				if ($v['afp_status'] != voa_d_oa_askfor_proc::STATUS_CARBON_COPY) {
					unset($procs[$k]);
				}
			}
		}
		$new_uids = array_diff($uids, $exist_uids);
		return true;
	}
}
