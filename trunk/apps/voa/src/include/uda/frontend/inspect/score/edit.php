<?php
/**
 * 巡店打分记录编辑操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_score_edit extends voa_uda_frontend_inspect_abstract {

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
		$user = $this->get('wbs_user');

		// 分数检查
		$score = (int)$this->get('score', 0);
		// 问题信息
		$message = (string)$this->get('message', '');

		// 数据入库
		$serv_score = new voa_s_oa_inspect_score();
		// 打分信息入库
		$item_score = array(
			'isr_score' => $score,
			'isr_message' => $message,
			'm_uid' => $user['m_uid'],
			'cr_id' => (int)$this->_params['cr_id'],
			'csp_id' => (int)$this->_params['csp_id'],
			'ins_id' => (int)$this->_params['ins_id'],
			'insi_id' => (int)$this->_params['insi_id'],
			'isr_option' => (int)$this->_params['isr_option'],
			'isr_date' => rgmdate(startup_env::get('timestamp'), 'Ymd'),
			'isr_type' => voa_d_oa_inspect_score::TYPE_DATE,
			'isr_status' => 0 < $score ? voa_d_oa_inspect_score::STATE_DONE : voa_d_oa_inspect_score::STATE_DOING
		);

		if (isset($out['isr_id'])) {
			$item_score['isr_id'] = $out['isr_id'];
		}

		if (!empty($item_score['isr_id'])) {
			$serv_score->update($item_score['isr_id'], $item_score);
		} else {
			$item_score = $serv_score->insert($item_score);
		}

		// 更新附件信息
		$this->_update_attach($item_score);
		// 返回值
		$out = $item_score;

		return true;
	}

	/**
	 * 更新附件
	 * @param array $item_score 打分信息
	 * @return boolean
	 */
	protected function _update_attach($item_score) {

		// 验证附件id
		$at_idstr = (string)$this->get('at_ids');
		$at_ids = array();
		if (!$this->chk_at_ids($at_idstr, $at_ids)) {
			return false;
		}

		// 读取附件信息
		$serv_at = &service::factory('voa_s_oa_common_attachment', array('pluginid' => 0));
		$attachs = array();
		$attachs = $serv_at->fetch_by_conditions(array(
			'at_id' => array($at_ids, '='),
			'm_uid' => $item_score['m_uid']
		));

		// 读取附件信息
		$serv_insat = new voa_s_oa_inspect_attachment();
		$attach_list = $serv_insat->list_by_conds(array('ins_id' => $this->_params['ins_id'], 'insi_id' => $this->_params['insi_id']));
		if (empty($attach_list)) {
			$attach_list = array();
		}

		$del_insat_ids = array();
		foreach ($attach_list as $_at) {
			if (array_key_exists($_at['at_id'], $attachs)) {
				unset($attachs[$_at['at_id']]);
				continue;
			}

			$del_insat_ids[] = $_at['insat_id'];
		}

		// 清除已存在附件
		if (!empty($del_insat_ids)) {
			$serv_insat->delete($del_insat_ids);
		}

		// 附件信息入库
		$left_ats = $this->_attach_max - (count($attach_list) - count($del_insat_ids));
		$ins_ats = array();
		foreach ($attachs as $_at) {
			if (0 >= $left_ats --) {
				break;
			}

			$ins_ats[] = array(
				'm_uid' => $_at['m_uid'],
				'ins_id' => $item_score['ins_id'],
				'insi_id' => $item_score['insi_id'],
				'isr_id' => $item_score['isr_id'],
				'at_id' => $_at['at_id']
			);
		}

		// 如果附件不为空
		if (!empty($ins_ats)) {
			$serv_insat->insert_multi($ins_ats);
		}

		return true;
	}
}
