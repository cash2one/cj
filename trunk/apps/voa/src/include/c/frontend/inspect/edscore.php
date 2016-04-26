<?php
/**
 * 巡店打分
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_edscore extends voa_c_frontend_inspect_base {
	// 记录列表
	protected $_scores = array();
	// 打分记录
	protected $_item_score = array();
	// 巡店信息
	protected $_inspect = array();
	// 打分项id
	protected $_insi_id = 0;
	// 下一个未完成的打分项
	protected $_next_insi_id = 0;
	// 所有未完成
	protected $_wait_insi_ids = array();

	public function execute() {

		$ins_id = (int)$this->request->get('ins_id');
		$this->_insi_id = (int)$this->request->get('insi_id');

		// 读取巡店信息
		$uda_inspect = new voa_uda_frontend_inspect_get();
		$uda_inspect->execute(array('ins_id' => $ins_id), $this->_inspect);

		// 检查是否有编辑权限
		if (!$this->_chk_edit_permit($this->_inspect)) {
			$this->_error_message('no_privilege');
			return false;
		}

		// 判断打分项是否存在
		if (0 >= $this->_insi_id || !isset($this->_items[$this->_insi_id])) {
			$this->_error_message('inspect_item_is_not_exist');
			return false;
		}

		$c_item = $this->_items[$this->_insi_id];
		if (0 >= $c_item['insi_parent_id']) {
			$this->_error_message('inspect_item_error');
			return false;
		}

		$p_item = $this->_items[$c_item['insi_parent_id']];

		// 读取评分信息
		$this->_item_score = array();
		$uda_score = new voa_uda_frontend_inspect_score_list();
		$uda_score->execute(array('ins_id' => $ins_id), $this->_scores);
		if (empty($this->_scores)) {
			$this->_scores;
		}

		// 读取打分记录
		if (isset($this->_scores[$this->_insi_id])) {
			$this->_item_score = $this->_scores[$this->_insi_id];
		}

		// 检查评分信息编辑权限
		if (!empty($this->_item_score) && $this->_item_score['ins_id'] != $ins_id) {
			$this->_error_message('no_privilege');
			return false;
		}

		if (!$this->_get_next_id()) {
			$this->_error_message('undefined_action');
			return false;
		}

		if ($this->_is_post()) {
			// 调用处理函数
			$this->_add();
			return false;
		}

		// 读取附件
		$uda_att = new voa_uda_frontend_inspect_attachment_list();
		$uda_att->set_limit(false);
		$attach_list = array();
		$uda_att->execute(array('ins_id' => $ins_id, 'insi_id' => $this->_insi_id), $attach_list);

		if (!isset($this->_item_score['isr_score'])) {
			$this->_item_score['isr_score'] = 0;
		}

		// 附件id
		$at_ids = array();
		foreach ($attach_list as $_at) {
			$at_ids[] = $_at['at_id'];
		}

		if (isset($this->_options['i2o'][$this->_insi_id])) {
			$this->view->set('options', $this->_options);
		}

		$this->view->set('item_score', $this->_item_score);
		$this->view->set('p_item', $p_item);
		$this->view->set('c_item', $c_item);
		$this->view->set('inspect', $this->_inspect);
		$this->view->set('attachs', $attach_list);
		$this->view->set('attach_total', count($attach_list));
		$this->view->set('at_ids', implode(',', $at_ids));
		$this->view->set('shop', $this->_shops[$this->_inspect['csp_id']]);
		$this->view->set('is_last', 0 < count($this->_wait_insi_ids) ? false : true);
		$this->view->set('form_action', '/frontend/inspect/edscore/ins_id/'.$ins_id.'/insi_id/'.$this->_insi_id.'/?handlekey=post');

		// 载入jsapi
		$this->_get_jsapi("['chooseImage', 'previewImage', 'uploadImage']");

		$this->_output('inspect/edscore');
	}

	protected function _add() {

		$params = $this->request->getx();
		$params['ins_id'] = $this->_inspect['ins_id'];
		$params['csp_id'] = $this->_inspect['csp_id'];
		$params['insi_id'] = $this->_insi_id;
		$params['cr_id'] = $this->_shops[$this->_inspect['csp_id']]['cr_id'];
		$params['wbs_user'] = $this->_user;

		$uda = &uda::factory('voa_uda_frontend_inspect_score_edit');
		// 打分信息
		$item_score = $this->_item_score;
		try {
			voa_uda_frontend_transaction_abstract::s_begin();

			if (!$uda->execute($params, $item_score)) {
				return false;
			}

			voa_uda_frontend_transaction_abstract::s_commit();
		} catch (help_exception $e) {
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_error_message($e->getMessage());
		}

		$ac = (string)$this->request->get('ac', '');
		// 取下一个打分项id
		if ('next' == $ac && 0 < $this->_next_insi_id) {
			$this->_success_message('item_score_done', "/frontend/inspect/edscore/ins_id/".$this->_inspect['ins_id'].'/insi_id/'.$this->_next_insi_id);
			return true;
		}

		if (0 < $this->_next_insi_id && 0 < count($this->_wait_insi_ids)) {
			$this->_error_message('all_item_score_is_require');
			return true;
		}

		$this->_success_message('item_score_all_done', "/frontend/inspect/edit/ins_id/".$this->_inspect['ins_id']);
	}

	/**
	 * 获取下一个打分项
	 * @return boolean
	 */
	protected function _get_next_id() {

		$ready = false;
		foreach ($this->_items['p2c'][0] as $_pid) {
			foreach ($this->_items['p2c'][$_pid] as $_cid) {
				// 已经找到当前的打分项
				if (true == $ready && 0 >= $this->_next_insi_id) {
					$this->_next_insi_id = $_cid;
				}

				// 判断是否为当前的打分项
				if ($this->_insi_id == $_cid) {
					$ready = true;
					continue;
				}

				// 如果还未找到未完成的打分项, 则
				if (!isset($this->_scores[$_cid]) || 0 >= $this->_scores[$_cid]['isr_score']) {
					$this->_wait_insi_ids[] = $_cid;
				}
			}
		}

		// 如果该项之后已全部完成, 则取前面第一个未完成的打分项
		if (!empty($this->_wait_insi_ids) && !in_array($this->_next_insi_id, $this->_wait_insi_ids)) {
			reset($this->_wait_insi_ids);
			$this->_next_insi_id = current($this->_wait_insi_ids);
		}

		return true;
	}
}
