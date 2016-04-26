<?php
/**
 * 日程相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_plan_insert extends voa_uda_frontend_plan_base {

	public function __construct() {
		parent::__construct();

		$this->plan =& service::factory('voa_s_oa_plan', array(
			'agent_id' => startup_env::get('agent_id')
		));
		$this->plan_member =& service::factory('voa_s_oa_plan_mem', array(
			'agent_id' => startup_env::get('agent_id')
		));
		$this->member =& service::factory('voa_s_oa_member', array(
			'agent_id' => startup_env::get('agent_id')
		));
	}

	/**
	 * 新日程入库
	 * @param array $plan
	 * @throws Exception
	 * @return boolean
	 */
	public function plan_new(&$plan) {
		$_user = array(
			'm_uid' => startup_env::get('wbs_uid'),
			'm_username' => startup_env::get('wbs_username')
		);

		$_temp = array(
			'pl_alarm_at' => $this->v_alarm_at($this->_request->get('alarm_at')),    #提醒
			'pl_begin_at' => $this->v_begin_at($this->_request->get('begin_at')),    #开始
			'pl_finish_at' => $this->v_finish_at($this->_request->get('finish_at')), #结束
			'pl_subject' => $this->v_subject($this->_request->get('subject')),       #内容
			'pl_address' => $this->v_address($this->_request->get('address')),       #地址
			'pl_type' => $this->v_type($this->_request->get('type')),                #类型
			'pl_status' => voa_d_oa_plan::STATUS_NORMAL
		);

		$uidstr = (string) $this->_request->get('carboncopyuids');

		try {
			$this->plan->begin();

			$plan['pl_id'] = $this->plan->insert(array_merge($_user, $_temp), true);

			if (empty($plan['pl_id'])) {
				throw new Exception('plan_new_failed');
			}

			// 验证分享人列表
			if (!empty($uidstr)) {
				$ccuids = array();

				if (!$this->v_shares($uidstr, $ccuids)) {
					throw new Exception('plan_new_shares_failed');
				}

				$cculist = $this->member->fetch_all_by_ids($ccuids);

				foreach ($cculist as $key) {
					/** 分享人信息入库 */
					$this->plan_member->insert(array(
						'pl_id' => $plan['pl_id'],
						'm_uid' => $key['m_uid'],
						'm_username' => $key['m_username'],
						'plm_status' => voa_d_oa_plan_mem::STATUS_CARBON_COPY
					));
				}
			}

			$this->plan->commit();
		} catch (Exception $e) {
			$this->plan->rollback();
			$this->errmsg(150, $e->getMessage());

			return false;
		}

		return true;
	}
}
