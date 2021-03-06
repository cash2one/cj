<?php
/**
 * 查看报销信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_view extends voa_c_frontend_reimburse_base {

	public function execute() {
		/** 获取报销信息 */
		$reimburse = array();
		$rb_id = intval($this->request->get('rb_id'));
		$this->_get_reimburse($reimburse, $rb_id);

		/** 读取清单信息 */
		$bills = array();
		$this->_get_bill_by_rb_id($bills, $rb_id);

		/** 获取清单id */
		$at_ids = array();
		foreach ($bills as $b) {
			$at_ids[$b['at_id']] = $b['at_id'];
		}

		/** 读取附件 */
		$attachs = array();
		$this->_get_attach_by_at_id($attachs, $at_ids);
		/** 根据清单id整理附件 */
		$tmp_at = array();
		foreach ($attachs as $at) {
			$tmp_at[$at['rbb_id']] = $at;
		}

		$attachs = $tmp_at;

		/** 获取回复信息 */
		$posts = array();
		$this->_get_post_by_rb_id($posts, $rb_id);

		/** 获取进度信息 */
		$procs = array();
		$this->_get_proc_by_rb_id($procs, $rb_id);
		/** 检查用户权限 */
		if (!$this->_is_permit($procs, $reimburse)) {
			$this->_error_message('no_privilege');
		}

		/** 如果进度数为 0 时 */
		if (empty($procs)) {
			$this->_error_message('reimburse_proc_error');
			return false;
		}

		/** 排序 */
		ksort($procs, SORT_NUMERIC);
		/** 取最后一个值 */
		$cur_proc = $procs[$reimburse['rbpc_id']];
		$this->_fill_proc($procs, $cur_proc);

		$this->view->set('rb_id', $rb_id);
		$this->view->set('reimburse', $reimburse);
		$this->view->set('bills', $bills);
		$this->view->set('attachs', $attachs);
		$this->view->set('posts', $posts);
		$this->view->set('procs', $procs);
		$this->view->set('cur_proc', $cur_proc);
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('navtitle', '报销详情');

		$this->_output('reimburse/view');
	}

	/**
	 * 判断是否有权限查看该报销信息
	 * @param array $procs
	 * @param array $reimburse
	 * @return boolean
	 */
	protected function _is_permit(&$procs, $reimburse) {
		$is_permit = false;
		foreach ($procs as $k => $v) {
			if (startup_env::get('wbs_uid') == $v['m_uid']) {
				$is_permit = true;
			}

			if ($reimburse['m_uid'] == $v['m_uid'] || voa_d_oa_reimburse_proc::STATUS_CC == $v['rbpc_status']) {
				unset($procs[$k]);
				continue;
			}
		}

		if (startup_env::get('wbs_uid') == $reimburse['m_uid']) {
			$is_permit = true;
		}

		return $is_permit;
	}

	/**
	 * 补齐进度数
	 * @param array $procs
	 * @param array $cur_proc
	 * @param int $num
	 */
	protected function _fill_proc(&$procs, $cur_proc, $num = 3) {
		$count = count($procs);
		switch ($count) {
			case 1:$procs[] = array('_status_class' => '', 'm_username' => ''); ++ $count; break;
			default:break;
		}

		if (voa_d_oa_reimburse_proc::STATUS_APPROVE == $cur_proc['rbpc_status'] || voa_d_oa_reimburse_proc::STATUS_REFUSE == $cur_proc['rbpc_status']) {
			$procs[] = array('_status_class' => 'succ', 'm_username' => '结束');
		} else {
			$procs[] = array('_status_class' => 'null', 'm_username' => '');
		}

		return true;
	}
}

