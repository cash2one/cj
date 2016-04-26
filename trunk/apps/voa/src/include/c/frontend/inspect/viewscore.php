<?php
/**
 * 巡店详情信息展示
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_viewscore extends voa_c_frontend_inspect_base {

	public function execute() {

		$ins_id = (int)$this->request->get('ins_id', 0);
		$insi_id = (int)$this->request->get('insi_id', 0);

		// 读取巡店信息
		$uda_inspect = new voa_uda_frontend_inspect_get();
		$inspect = array();
		$uda_inspect->execute(array('ins_id' => $ins_id), $inspect);

		if (empty($inspect)) {
			$this->_error_message('inspect_is_not_exist');
			return false;
		}

		// 读取打分项
		$uda_score = new voa_uda_frontend_inspect_score_list();
		$list = array();
		$uda_score->execute(array('ins_id' => $ins_id, 'insi_id' => $this->_items['p2c'][$insi_id]), $list);

		// 补齐缺失的打分项
		$this->_get_ext_items($this->_items, $list);
		$uda_score->set_items($this->_items);

		// 判断打分项是否存在
		if (!in_array($insi_id, $this->_items['p2c'][0])) {
			$this->_error_message('inspect_item_is_not_exist');
			return false;
		}

		// 读取用户信息
		$uda_mem = new voa_uda_frontend_inspect_mem_list();
		$uda_mem->set_limit(false);
		$mlist = array();
		$uda_mem->execute($this->request->getx(), $mlist);

		// 判断权限
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

		// 读取巡店附件信息
		$uda_att = new voa_uda_frontend_inspect_attachment_list();
		$uda_att->set_limit(false);
		$attachs = array();
		$uda_att->execute(array('ins_id' => $ins_id), $attachs);
		// 按 insi_id 整理 attachs
		$insi_id2at = array();
		foreach ($attachs as $_at) {
			if (!array_key_exists($_at['insi_id'], $insi_id2at)) {
				$insi_id2at[$_at['insi_id']] = array();
			}

			$insi_id2at[$_at['insi_id']][] = $_at;
		}

		// 计算主评分项分数
		$total = 0;
		$item2score = array();
		$uda_score->calc_score($total, $item2score, $list);

		$this->view->set('options', $this->_options);
		$this->view->set('shop', $this->_shops[$inspect['csp_id']]);
		$this->view->set('inspect', $inspect);
		$this->view->set('total_score', $total);
		$this->view->set('item2score', $item2score);
		$this->view->set('insi_id2at', $insi_id2at);
		$this->view->set('items', $this->_items);
		$this->view->set('insi_id', $insi_id);
		$this->view->set('score_list', $list);

		$this->_output('inspect/viewscore');
	}

}
