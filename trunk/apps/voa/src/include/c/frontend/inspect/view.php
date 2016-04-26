<?php
/**
 * 巡店信息展示
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_view extends voa_c_frontend_inspect_base {

	public function execute() {

		$ins_id = (int)$this->request->get('ins_id');

		// 读取巡店信息
		$uda_inspect = new voa_uda_frontend_inspect_get();
		$inspect = array();
		$uda_inspect->execute(array('ins_id' => $ins_id), $inspect);

		if (empty($inspect)) {
			$this->_error_message('inspect_is_not_exist');
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

		// 读取打分项
		$uda_score = new voa_uda_frontend_inspect_score_list();
		$list = array();
		$uda_score->execute($this->request->getx(), $list);

		// 补齐缺失的打分项
		$this->_get_ext_items($this->_items, $list);
		$uda_score->set_items($this->_items);

		// 计算主评分项分数
		$total = 0;
		$item2score = array();
		$uda_score->calc_score($total, $item2score, $list);

		$this->view->set('shop', $this->_shops[$inspect['csp_id']]);
		$this->view->set('inspect', $inspect);
		$this->view->set('total_score', $total);
		$this->view->set('item2score', $item2score);
		$this->view->set('items', $this->_items);

		$this->_output('inspect/view');
	}

}
