<?php
/**
 * 投票操作信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_vote_choice extends voa_c_frontend_vote_base {
	protected $_vote = array();
	protected $_options = array();

	public function execute() {
		/** 获取投票信息 */
		$v_id = intval($this->request->get('v_id'));
		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => startup_env::get('pluginid')));
		$this->_vote = $serv_v->fetch_by_id($v_id);
		if (empty($this->_vote)) {
			$this->_error_message('当前投票信息不存在');
		}

		/** 获取当前选项 */
		$vo_ids = rintval((array)$this->request->get('options'), true);
		$serv_vo = &service::factory('voa_s_oa_vote_option', array('pluginid' => startup_env::get('pluginid')));
		$this->_options = $serv_vo->fetch_by_v_id_vo_ids($v_id, $vo_ids);
		if (count($vo_ids) != count($this->_options)) {
			$this->_error_message('投票选项不存在或已删除');
		}

		/** 检查选项 */
		$this->_chk_option();

		/** 检查投票信息是否可用, 以及判断用户是否有权限 */
		$this->_chk_vote();

		$serv_vm = &service::factory('voa_s_oa_vote_mem', array('pluginid' => startup_env::get('pluginid')));
		/** 投票信息入库 */
		try {
			$serv_v->begin();

			/** 投票选项 +1 */
			$serv_vo->choices($vo_ids);

			/** 投票用户记录入库 */
			foreach ($this->_options as $v) {
				$serv_vm->insert(array(
					'v_id' => $v_id,
					'vo_id' => $v['vo_id'],
					'vm_ip' => controller_request::get_instance()->get_client_ip(),
					'm_uid' => startup_env::get('wbs_uid'),
					'm_username' => startup_env::get('wbs_username')
				));
			}

			/** 更新次数 */
			$serv_v->update_voters($v_id);

			$serv_v->commit();
		} catch (Exception $e) {
			$serv_v->rollback();
			$this->_error_message('投票操作失败');
		}

		$this->_success_message('投票成功', "/vote/view/{$v_id}");
	}

	/** 检查投票信息是否可用, 以及判断用户是否有权限 */
	protected function _chk_vote() {
		if (voa_d_oa_vote::IS_CLOSE == $this->_vote['v_isopen']) {
			$this->_error_message('该投票已经关闭');
		}

		if (voa_d_oa_vote::STATUS_NORMAL == $this->_vote['v_status']) {
			$this->_error_message('该投票正在审核中');
		}

		if (voa_d_oa_vote::STATUS_REFUSE == $this->_vote['v_status']) {
			$this->_error_message('该投票审核未通过');
		}

		/** 每个人只能投1票 */
		$serv_vm = &service::factory('voa_s_oa_vote_mem', array('pluginid' => startup_env::get('pluginid')));
		$rcd = $serv_vm->fetch_by_v_id_uid($this->_vote['v_id'], startup_env::get('wbs_uid'));
		if (!empty($rcd)) {
			$this->_error_message('您已经投过票了, 不能重复投票');
		}

		/** 所有可投票 */
		if (voa_d_oa_vote::FRIEND_ALL == $this->_vote['v_friend']) {
			return true;
		}

		/** 读取允许的用户 */
		$serv_vpu = &service::factory('voa_s_oa_vote_permit_user', array('pluginid' => startup_env::get('pluginid')));
		$user = $serv_vpu->fetch_by_v_id_uid($this->_vote['v_id'], startup_env::get('wbs_uid'));
		if (empty($user)) {
			$this->_error_message('您没有权限进行投票');
			return false;
		}

		return true;
	}

	/** 检查用户提交的选项 */
	protected function _chk_option() {
		/** 单选 */
		if (voa_d_oa_vote::IS_SINGLE == $this->_vote['v_ismulti']) {
			if (1 < count($this->_options)) {
				$this->_error_message('不能多选');
				return false;
			}

			return true;
		}

		/** 少于最小值 */
		if (count($this->_options) < $this->_vote['v_minchoices']) {
			$this->_error_message('最少选'.$this->_vote['v_minchoices'].'项');
		}

		/** 多于最大值 */
		if (count($this->_options) > $this->_vote['v_maxchoices']) {
			$this->_error_message('最多选'.$this->_vote['v_maxchoices'].'项');
		}

		return true;
	}
}
