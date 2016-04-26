<?php
/**
 * 查看投票信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_vote_view extends voa_c_frontend_vote_base {

	public function execute() {
		/** 获取投票信息 */
		$v_id = intval($this->request->get('v_id'));
		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => startup_env::get('pluginid')));
		$vote = $serv_v->fetch_by_id($v_id);
		if (empty($vote)) {
			$this->_error_message('当前投票信息不存在');
		}

		$vote['v_subject'] = rhtmlspecialchars($vote['v_subject']);
		$vote['v_message'] = rhtmlspecialchars($vote['v_message']);

		/** 读取选项信息 */
		$serv_vo = &service::factory('voa_s_oa_vote_option', array('pluginid' => startup_env::get('pluginid')));
		$options = $serv_vo->fetch_by_v_id($v_id);
		$ord_a = 65;
		foreach ($options as &$v) {
			$v['vo_option'] = rhtmlspecialchars($v['vo_option']);
			$v['_order_num'] = chr($ord_a);
			++ $ord_a;
		}
		unset($v);

		/** 判断活动是否开始 */
		$is_active = false;
		$is_voted = false;
		if ($vote['v_status'] == self::STATUS_APPROVE && $vote['v_isopen'] == self::IS_OPEN
				&& $vote['v_endtime'] > startup_env::get('timestamp')) {
			$is_active = true;

			/** 每个人只能投1票 */
			$serv_vm = &service::factory('voa_s_oa_vote_mem', array('pluginid' => startup_env::get('pluginid')));
			$rcd = $serv_vm->fetch_by_v_id_uid($v_id, startup_env::get('wbs_uid'));
			if (!empty($rcd)) {
				$is_voted = true;
			}
		}

		$this->view->set('v_id', $v_id);
		$this->view->set('vote', $vote);
		$this->view->set('is_active', $is_active);
		$this->view->set('is_voted', $is_voted);
		$this->view->set('options', $options);

		$this->_output('vote/view');
	}
}

