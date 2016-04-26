<?php
/**
 * 巡店相关的编辑操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_update extends voa_uda_frontend_inspect_abstract {

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
		$user = $this->get('_wbs_user');
		$inspect = $this->get('_inspect');
		$serv_inspect = new voa_s_oa_inspect();

		// 取商家信息
		$shops = voa_h_cache::get_instance()->get('shop', 'oa');
		$cur_shop = $shops[$inspect['csp_id']];

		// 验证目标人
		$mem_uidstr = (string)$this->get('mem_uids');
		$mem_uids = array();
		if (!$this->chk_uids($mem_uidstr, $mem_uids)) {
			return false;
		}

		// 验证抄送人
		$cc_uidstr = (string)$this->get('cc_uids');
		$cc_uids = array();
		if (!$this->chk_uids($cc_uidstr, $cc_uids)) {
			return false;
		}

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		// 读取用户信息, 包括接收人和抄送人信息
		$cc_uids[$user['m_uid']] = $user['m_uid'];
		unset($mem_uids[$user['m_uid']]);
		$all_uids = array_merge($mem_uids, $cc_uids);
		$user_list = $servm->fetch_all_by_ids($all_uids);

		// 数据入库
		$serv_insat = &service::factory('voa_s_oa_inspect_attachment');
		$serv_score = &service::factory('voa_s_oa_inspect_score');
		$serv_mem = &service::factory('voa_s_oa_inspect_mem');
		$serv_task = &service::factory('voa_s_oa_inspect_tasks');

		// 更新巡店信息
		$serv_inspect->update($inspect['ins_id'], array(
			'ins_score' => $this->get('_total'),
			'ins_type' => voa_d_oa_inspect::TYPE_DONE,
			'ins_status' => voa_d_oa_inspect::STATUS_UPDATE
		));

		// 总评分入库
		$ins_score = array(
			'm_uid' => $user['m_uid'],
			'cr_id' => $cur_shop['cr_id'],
			'ins_id' => $inspect['ins_id'],
			'insi_id' => 0,
			'isr_score' => $this->get('_total'),
			'isr_message' => '',
			'isr_date' => rgmdate(startup_env::get('timestamp'), 'Ymd'),
			'isr_type' => voa_d_oa_inspect_score::TYPE_DATE,
			'isr_state' => voa_d_oa_inspect_score::STATE_DONE,
			'csp_id' => $inspect['csp_id']
		);

		$serv_score->insert($ins_score);

		$ins_mems = array();
		// 目标人信息入库
		$tolist = array();
		foreach ($mem_uids as $_uid) {
			if (!array_key_exists($_uid, $user_list)) {
				continue;
			}

			// 剔除抄送人
			unset($cc_uids[$_uid]);

			$tolist[$_uid] = $user_list[$_uid];
			$ins_mems[] = array(
				'ins_id' => $inspect['ins_id'],
				'insm_src_uid' => $inspect['m_uid'],
				'm_uid' => $_uid,
				'm_username' => $user_list[$_uid]['m_username'],
				'insm_type' => voa_d_oa_inspect_mem::TYPE_TO,
				'insm_status' => voa_d_oa_inspect_mem::STATUS_NORMAL
			);
		}

		// 抄送人信息入库
		$cclist = array();
		foreach ($cc_uids as $_uid) {
			if (!array_key_exists($_uid, $user_list)) {
				continue;
			}

			$cclist[$_uid] = $user_list[$_uid];
			$ins_mems[] = array(
				'ins_id' => $inspect['ins_id'],
				'insm_src_uid' => $inspect['m_uid'],
				'm_uid' => $_uid,
				'm_username' => $user_list[$_uid]['m_username'],
				'insm_type' => voa_d_oa_inspect_mem::TYPE_CC,
				'insm_status' => voa_d_oa_inspect_mem::STATUS_NORMAL
			);
		}

		$serv_mem->insert_multi($ins_mems);

		// 更新任务完成数
		$serv_task->inspect_fin(array($inspect['it_id']));

		// 返回
		$out = array('to' => $tolist, 'cc' => $cclist);

		return true;
	}
}
