<?php
/**
 * 活动/产品列表信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_list extends voa_c_frontend_productive_base {
	/** 时间戳 */
	protected $_updated = 0;
	/** 起始位置 */
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {

		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/**
		 * 动作
		 * mine: 我的活动/产品记录
		 * recv: 我收到的
		 */
		$acs = array('mine', 'recv');
		/** 获取操作 */
		$ac = (string)$this->request->get('ac');

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		/** 读取记录列表 */
		$ac = in_array($ac, $acs) ? $ac : 'mine';
		$func = '_fetch_'.$ac;
		if (!method_exists($this, $func)) {
			$this->_error_message('undefined_action');
			return false;
		}

		$list = $this->$func();

		/** 过滤 */
		$fmt = &uda::factory('voa_uda_frontend_productive_format');
		if (!$fmt->productive_list($list)) {
			$this->_error_message($fmt->error);
			return false;
		}

		$next_page = $this->_page;
		if (!empty($list)) {
			$next_page = $this->_page + 1;
		}

		$this->view->set('list', $list);
		$this->view->set('ac', $ac);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('page', $next_page);
		$this->view->set('shops', $this->_shops);

		/** 模板 */
		$tpl = 'productive/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	/** 获取我的活动/产品列表 */
	protected function _fetch_mine() {
		$serv = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		return $serv->list_by_uid(startup_env::get('wbs_uid'), voa_d_oa_productive::STATUS_DONE, $this->_start, $this->_perpage);
	}

	/** 读取我收到的 */
	protected function _fetch_recv() {
		$serv = &service::factory('voa_s_oa_productive_mem', array('pluginid' => startup_env::get('pluginid')));
		return $serv->list_recv_by_uid(startup_env::get('wbs_uid'), $this->_start, $this->_perpage);
	}

}
