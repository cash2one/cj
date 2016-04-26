<?php
/**
 * 活动/产品详情信息展示
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_viewscore extends voa_c_frontend_productive_base {

	public function execute() {

		$pt_id = (int)$this->request->get('pt_id', 0);
		$pti_id = (int)$this->request->get('pti_id', 0);

		/** 读取活动/产品信息 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$productive = $serv_pt->fetch_by_id($pt_id);
		/** 格式化 */
		$fmt = &uda::factory('voa_uda_frontend_productive_format');
		if (!$fmt->productive($productive)) {
			$this->_error_message($fmt->error);
			return false;
		}

		if (empty($productive)) {
			$this->_error_message('productive_is_not_exist');
			return false;
		}

		/** 判断打分项是否存在 */
		if (!in_array($pti_id, $this->_items['p2c'][0])) {
			$this->_error_message('productive_item_is_not_exist');
			return false;
		}

		/** 读取用户信息 */
		$serv_mem = &service::factory('voa_s_oa_productive_mem', array('pluginid' => startup_env::get('pluginid')));
		$mlist = $serv_mem->fetch_by_pt_id($pt_id);
		/** 判断权限 */
		$is_permit = false;
		foreach ($mlist as $_m) {
			if ($_m['m_uid'] == startup_env::get('wbs_uid')) {
				$is_permit = true;
				break;
			}
		}

		if (false == $is_permit) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 读取活动/产品附件信息 */
		$serv_at = &service::factory('voa_s_oa_productive_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attachs = $serv_at->fetch_by_pt_id($productive['pt_id']);
		/** 按 pti_id 整理 attachs */
		$pti_id2at = array();
		foreach ($attachs as $_at) {
			if (!array_key_exists($_at['pti_id'], $pti_id2at)) {
				$pti_id2at[$_at['pti_id']] = array();
			}

			$pti_id2at[$_at['pti_id']][] = $_at;
		}

		/** 读取打分项 */
		$serv_score = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_score->fetch_by_pt_id($pt_id);
		/** 计算主评分项分数 */
		$total = 0;
		$item2score = array();
		$uda_base = &uda::factory('voa_uda_frontend_productive_base');
		$uda_base->calc_score($total, $item2score, $list);

		$this->view->set('shop', $this->_shops[$productive['csp_id']]);
		$this->view->set('productive', $productive);
		$this->view->set('total_score', $total);
		$this->view->set('item2score', $item2score);
		$this->view->set('pti_id2at', $pti_id2at);
		$this->view->set('items', $this->_items);
		$this->view->set('pti_id', $pti_id);
		$this->view->set('score_list', $list);

		$this->_output('productive/viewscore');
	}

}
