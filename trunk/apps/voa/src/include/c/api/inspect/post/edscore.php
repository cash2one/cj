<?php
/**
 * 巡店打分
 * voa_c_api_inspect_post_edscore
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_post_edscore extends voa_c_api_inspect_base {
	/** 记录列表 */
	protected $_scores = array();
	/** 打分记录 */
	protected $_item_score = array();
	/** 巡店信息 */
	protected $_inspect = array();
	/** 打分项id */
	protected $_insi_id = 0;
	/** 下一个未完成的打分项 */
	protected $_next_insi_id = 0;
	/** 所有未完成 */
	protected $_wait_insi_ids = array();

	public function execute() {

		// 请求参数
		$fields = array(
			// 巡店ID
			'ins_id' => array('type' => 'int', 'required' => true),
			//　打分项ID
			'insi_id' => array('type' => 'int', 'required' => true),
			//　打分项
			'score' => array('type' => 'int', 'required' => true),
			//　打分项留言
			'message' => array('type' => 'string_trim', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		// 请假内容检查
		if (empty($this->_params['score'])) {
			return $this->_set_errcode(voa_errcode_api_inspect::NEW_SCORE_NULL);
		}

		$ins_id = $this->_params['ins_id'];
		$this->_insi_id = $this->_params['insi_id'];

		/** 读取巡店信息 */
		$serv_ins = &service::factory('voa_s_oa_inspect', array('pluginid' => startup_env::get('pluginid')));
		$this->_inspect = $serv_ins->fetch_by_id($ins_id);

		/** 检查是否有编辑权限 */
		if (!$this->_chk_edit_permit($this->_inspect)) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_inspect::NO_PRIVILEGE);
		}

		/** 判断打分项是否存在 */
		if (0 >= $this->_insi_id || !isset($this->_items[$this->_insi_id])) {
			//$this->_error_message('inspect_item_is_not_exist');
			return $this->_set_errcode(voa_errcode_api_inspect::INSPECT_ITEM_IS_NOT_EXIST);
		}

		$c_item = $this->_items[$this->_insi_id];
		if (0 >= $c_item['insi_parent_id']) {
			//$this->_error_message('inspect_item_error');
			return $this->_set_errcode(voa_errcode_api_inspect::INSPECT_ITEM_IS_NOT_EXIST);
		}

		$p_item = $this->_items[$c_item['insi_parent_id']];

		/** 读取评分信息 */
		$this->_item_score = array();
		$serv_isr = &service::factory('voa_s_oa_inspect_score', array('pluginid' => startup_env::get('pluginid')));
		$this->_scores = $serv_isr->fetch_by_ins_id($ins_id);
		if (isset($this->_scores[$this->_insi_id])) {
			$this->_item_score = $this->_scores[$this->_insi_id];
		}

		/** 检查评分信息编辑权限 */
		if (!empty($this->_item_score) && $this->_item_score['ins_id'] != $ins_id) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_inspect::NO_PRIVILEGE);
		}

		if (!$this->_get_next_id()) {
			//$this->_error_message('undefined_action');
			return $this->_set_errcode(voa_errcode_api_inspect::UNDEFINED_ACTION);
		}

		//入库操作
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_return['ins_id']
		);
		return true;

	}

	protected function _add() {

		$params = $this->request->getx();
		$params['ins_id'] = $this->_inspect['ins_id'];
		$params['csp_id'] = $this->_inspect['csp_id'];
		$params['insi_id'] = $this->_insi_id;
		$params['cr_id'] = $this->_shops[$this->_inspect['csp_id']]['cr_id'];
		$params['wbs_user'] = $this->_user;

		$uda = &uda::factory('voa_uda_frontend_inspect_update');
		/** 打分信息 */
		$item_score = array();
		if (!$uda->inspect_score_edit($params, $item_score, $this->_item_score)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		$scheme = config::get('voa.oa_http_scheme');
		$this->_ref = $scheme.$sets['domain']."/api/inspect/post/edit/ins_id/".$this->_inspect['ins_id'];
		/** 取下一个打分项id */
		if (0 < $this->_next_insi_id) {
			$this->_ref = $scheme.$sets['domain']."/api/inspect/post/edscore/ins_id/".$this->_inspect['ins_id'].'/insi_id/'.$this->_next_insi_id;
			return true;
		}

		if (0 < $this->_next_insi_id && 0 < count($this->_wait_insi_ids)) {
			//$this->_error_message('all_item_score_is_require');
			return $this->_set_errcode(voa_errcode_api_inspect::ALL_ITEM_SCORE_IS_REQUIRE);
		}

		return true;
	}

	/**
	 * 获取下一个打分项
	 * @return boolean
	 */
	protected function _get_next_id() {

		$ready = false;
		foreach ($this->_items['p2c'][0] as $_pid) {
			foreach ($this->_items['p2c'][$_pid] as $_cid) {
				/** 已经找到当前的打分项 */
				if (true == $ready && 0 >= $this->_next_insi_id) {
					$this->_next_insi_id = $_cid;
				}

				/** 判断是否为当前的打分项 */
				if ($this->_insi_id == $_cid) {
					$ready = true;
					continue;
				}

				/** 如果还未找到未完成的打分项, 则 */
				if (!isset($this->_scores[$_cid]) || 0 >= $this->_scores[$_cid]['isr_score']) {
					$this->_wait_insi_ids[] = $_cid;
				}
			}
		}

		/** 如果该项之后已全部完成, 则取前面第一个未完成的打分项 */
		if (!empty($this->_wait_insi_ids) && !in_array($this->_next_insi_id, $this->_wait_insi_ids)) {
			reset($this->_wait_insi_ids);
			$this->_next_insi_id = current($this->_wait_insi_ids);
		}

		return true;
	}
}
