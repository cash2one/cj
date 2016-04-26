<?php
/**
 * 投票列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_vote_list extends voa_c_frontend_vote_base {
	/** 分页查询相关 */
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		$serv_v = &service::factory('voa_s_oa_vote', array('pluginid' => startup_env::get('pluginid')));

		/**
		 * status 状态值
		 * unclosed: 未结束的(包括未开始的)
		 * fin: 已结束的
		 */
		$sts = array('unclosed', 'fin');

		/** 获取状态 */
		$status = (string)$this->request->get('status');
		$status = in_array($status, $sts) ? $status : 'unclosed';

		/** 最后更新时间 */
		$updated = intval($this->request->get('updated'));
		$updated = empty($updated) ? startup_env::get('timestamp') : $updated;

		/** 获取数据的方法 */
		$uid = startup_env::get('wbs_uid');
		$list = array();
		if ('unclosed' == $status) {
			$list = $serv_v->fetch_unclosed_by_uid($uid);
		} else {
			$list = $serv_v->fetch_fin_by_uid_updated($uid, $updated, $start, $perpage);
		}
		/** 数据过滤 */
		$fmt = &uda::factory('voa_uda_frontend_vote_format');
		$fmt->vote_list($list);

		/** 取最后一个元素值 */
		$last_vote = end($list);
		$updated = $last_vote['v_updated'];
		reset($list);

		$tpl = 'vote/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_set_dept_job();
		$this->view->set('list', $list);
		$this->view->set('status', $status);
		$this->view->set('updated', $updated);
		$this->view->set('perpage', $this->_perpage);

		$this->_output($tpl);
	}
}

