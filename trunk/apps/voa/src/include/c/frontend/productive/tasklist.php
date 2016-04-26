<?php
/**
 * 活动/产品计划列表信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_tasklist extends voa_c_frontend_productive_base {
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

		/** 读取计划列表 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_pt->list_by_uid($this->_user['m_uid'], array(
			voa_d_oa_productive::STATUS_WAITING,
			voa_d_oa_productive::STATUS_DOING
		), $this->_start, $this->_perpage);

		$next_page = $this->_page;
		if (!empty($list)) {
			$next_page = $this->_page + 1;
		}

		$this->view->set('perpage', $this->_perpage);
		$this->view->set('page', $next_page);
		$this->view->set('shops', $this->_shops);
		$this->view->set('list', $list);

		/** 模板 */
		$tpl = 'productive/tasklist';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

}
