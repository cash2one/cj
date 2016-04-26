<?php
/**
 * 活动/产品打分
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_edscore extends voa_c_frontend_productive_base {
	/** 记录列表 */
	protected $_scores = array();
	/** 打分记录 */
	protected $_item_score = array();
	/** 活动/产品信息 */
	protected $_productive = array();
	/** 打分项id */
	protected $_pti_id = 0;
	/** 下一个未完成的打分项 */
	protected $_next_pti_id = 0;
	/** 所有未完成 */
	protected $_wait_pti_ids = array();

	public function execute() {

		$pt_id = (int)$this->request->get('pt_id');
		$this->_pti_id = (int)$this->request->get('pti_id');

		/** 读取活动/产品信息 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$this->_productive = $serv_pt->fetch_by_id($pt_id);

		/** 检查是否有编辑权限 */
		if (!$this->_chk_edit_permit($this->_productive)) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 判断打分项是否存在 */
		if (0 >= $this->_pti_id || !isset($this->_items[$this->_pti_id])) {
			$this->_error_message('productive_item_is_not_exist');
			return false;
		}

		$c_item = $this->_items[$this->_pti_id];
		if (0 >= $c_item['pti_parent_id']) {
			$this->_error_message('productive_item_error');
			return false;
		}

		$p_item = $this->_items[$c_item['pti_parent_id']];

		/** 读取评分信息 */
		$this->_item_score = array();
		$serv_ptsr = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$this->_scores = $serv_ptsr->fetch_by_pt_id($pt_id);
		if (isset($this->_scores[$this->_pti_id])) {
			$this->_item_score = $this->_scores[$this->_pti_id];
		}

		/** 检查评分信息编辑权限 */
		if (!empty($this->_item_score) && $this->_item_score['pt_id'] != $pt_id) {
			$this->_error_message('no_privilege');
			return false;
		}

		if (!$this->_get_next_id()) {
			$this->_error_message('undefined_action');
			return false;
		}

		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		/** 读取附件 */
		$serv_at = &service::factory('voa_s_oa_productive_attachment', array('pluginid' => startup_env::get('pluginid')));
		$attach_list = $serv_at->fetch_by_pt_id_pti_id($pt_id, $this->_pti_id);

		if (!isset($this->_item_score['ptsr_score'])) {
			$this->_item_score['ptsr_score'] = 0;
		}

		/** 附件id */
		$at_ids = array();
		foreach ($attach_list as $_at) {
			$at_ids[] = $_at['at_id'];
		}

		$this->view->set('item_score', $this->_item_score);
		$this->view->set('p_item', $p_item);
		$this->view->set('c_item', $c_item);
		$this->view->set('productive', $this->_productive);
		$this->view->set('attachs', $attach_list);
		$this->view->set('attach_total', count($attach_list));
		$this->view->set('at_ids', implode(',', $at_ids));
		$this->view->set('shop', $this->_shops[$this->_productive['csp_id']]);
		$this->view->set('is_last', 0 < count($this->_wait_pti_ids) ? false : true);
		$this->view->set('form_action', '/frontend/productive/edscore/pt_id/'.$pt_id.'/pti_id/'.$this->_pti_id.'/?handlekey=post');

		$this->_output('productive/edscore');
	}

	protected function _add() {

		$params = $this->request->getx();
		$params['pt_id'] = $this->_productive['pt_id'];
		$params['csp_id'] = $this->_productive['csp_id'];
		$params['pti_id'] = $this->_pti_id;
		$params['cr_id'] = $this->_shops[$this->_productive['csp_id']]['cr_id'];
		$params['wbs_user'] = $this->_user;

		$uda = &uda::factory('voa_uda_frontend_productive_update');
		/** 打分信息 */
		$item_score = array();
		if (!$uda->productive_score_edit($params, $item_score, $this->_item_score)) {
			$this->_error_message($uda->error);
			return false;
		}

		$ac = (string)$this->request->get('ac', '');
		/** 取下一个打分项id */
		if ('next' == $ac && 0 < $this->_next_pti_id) {
			$this->_success_message('item_score_done', "/frontend/productive/edscore/pt_id/".$this->_productive['pt_id'].'/pti_id/'.$this->_next_pti_id);
			return true;
		}

		if (0 < $this->_next_pti_id && 0 < count($this->_wait_pti_ids)) {
			$this->_error_message('all_item_score_is_require');
			return true;
		}

		$this->_success_message('item_score_all_done', "/frontend/productive/edit/pt_id/".$this->_productive['pt_id']);
	}

	/**
	 * 获取下一个打分项
	 * @return boolean
	 */
	protected function _get_next_id() {

		$ready = false;
		foreach ($this->_items['p2c'][0] as $_pid) {
			if (empty($this->_items['p2c'][$_pid])) {
				continue;
			}

			foreach ($this->_items['p2c'][$_pid] as $_cid) {
				/** 已经找到当前的打分项 */
				if (true == $ready && 0 >= $this->_next_pti_id) {
					$this->_next_pti_id = $_cid;
				}

				/** 判断是否为当前的打分项 */
				if ($this->_pti_id == $_cid) {
					$ready = true;
					continue;
				}

				/** 如果还未找到未完成的打分项, 则 */
				if (!isset($this->_scores[$_cid]) || 0 >= $this->_scores[$_cid]['ptsr_score']) {
					$this->_wait_pti_ids[] = $_cid;
				}
			}
		}

		/** 如果该项之后已全部完成, 则取前面第一个未完成的打分项 */
		if (!empty($this->_wait_pti_ids) && !in_array($this->_next_pti_id, $this->_wait_pti_ids)) {
			reset($this->_wait_pti_ids);
			$this->_next_pti_id = current($this->_wait_pti_ids);
		}

		return true;
	}
}
