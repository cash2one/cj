<?php
/**
 * 巡店信息发送
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_chksend extends voa_c_frontend_inspect_base {

	public function execute() {

		$ins_id = (int)$this->request->get('ins_id');
		$inspect = array();

		try {
			// 读取巡店信息
			$uda_inspect = &uda::factory('voa_uda_frontend_inspect_get');
			$uda_inspect->execute(array('ins_id' => $ins_id), $inspect);

			// 检查是否有编辑权限
			if (empty($inspect) || !$this->_chk_edit_permit($inspect)) {
				$this->_error_message('no_privilege');
				return false;
			}

			$scores = array();
			// 读取打分项
			$uda_score = new voa_uda_frontend_inspect_score_list();
			$uda_score->execute(array('ins_id' => $ins_id), $scores);

			// 计算总分
			$total = 0;
			$item2score = array();
			$uda_score->calc_score($total, $item2score, $scores);
			if (0 >= $total) {
				$this->_error_message('all_item_score_is_require');
				return false;
			}
		} catch (help_exception $e) {
			$this->_error_message($e->getMessage());
		}

		$this->_success_message('check_success', "/frontend/inspect/edit/ins_id/".$ins_id);
	}
}
