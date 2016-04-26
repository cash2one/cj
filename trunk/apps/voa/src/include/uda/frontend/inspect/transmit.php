<?php
/**
 * 巡店信息转发
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_transmit extends voa_uda_frontend_inspect_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;

		$inspect = $this->get('_inspect');

		// 验证抄送人
		$cc_uidstr = (string)$this->get('cc_uids');
		$cc_uids = array();
		if (!$this->chk_uids($cc_uidstr, $cc_uids)) {
			return false;
		}

		// 没有抄送人
		if (empty($cc_uids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		// 读取抄送人信息
		$user_list = $servm->fetch_all_by_ids($cc_uids);
		if (empty($user_list)) {
			return false;
		}

		// 读取巡店相关人
		$serv_ins_mem = &service::factory('voa_s_oa_inspect_mem');
		$exist_users = $serv_ins_mem->list_by_conds(array(
			'ins_id' => $this->get('ins_id'),
			'm_uid' => $cc_uids
		));

		$exist_users = empty($exist_users) ? array() : $exist_users;
		foreach ($exist_users as $_u) {
			unset($user_list[$_u['m_uid']]);
		}

		// 如果用户列表为空, 说明所有人都已经在抄送人列表中
		if (empty($user_list)) {
			return true;
		}

		// 抄送人信息入库
		$ins_mems = array();
		foreach ($user_list as $_uid => $_user) {
			$ins_mems[] = array(
				'ins_id' => $inspect['ins_id'],
				'insm_src_uid' => $inspect['m_uid'],
				'm_uid' => $_uid,
				'm_username' => $_user['m_username'],
				'insm_type' => voa_d_oa_inspect_mem::TYPE_CC,
				'insm_status' => voa_d_oa_inspect_mem::STATUS_NORMAL
			);
		}

		$serv_ins_mem->insert_multi($ins_mems);

		return true;
	}

}
