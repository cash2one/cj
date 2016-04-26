<?php
/**
 * 巡店打分项信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_editem extends voa_c_frontend_inspect_base {

	public function execute() {

		$ins_id = (int)$this->request->get('ins_id');

		// 读取巡店记录
		$uda_inspect = new voa_uda_frontend_inspect_get();
		$uda_inspect->execute(array('ins_id' => $ins_id), $inspect);

		// 检查是否有编辑权限
		if (!$this->_chk_edit_permit($inspect)) {
			$this->_error_message('no_privilege');
			return false;
		}

		// 读取打分记录
		$uda_score = new voa_uda_frontend_inspect_score_list();
		if (!$uda_score->execute(array('ins_id' => $ins_id), $list)) {
			return false;
		}

		$list = empty($list) ? array() : $list;

		// 计算主评分项分数
		$total = 0;
		$item2score = array();
		$uda_score->calc_score($total, $item2score, $list);

		$this->view->set('list', $list);
		$this->view->set('items', $this->_items);
		$this->view->set('inspect', $inspect);
		$this->view->set('item2score', $item2score);
		$this->view->set('shop', $this->_shops[$inspect['csp_id']]);

		$this->_output('inspect/editem');
	}

}
