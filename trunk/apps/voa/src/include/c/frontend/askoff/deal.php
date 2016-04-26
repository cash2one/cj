<?php
/**
 * 请假列表 - 带审批
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_deal extends voa_c_frontend_askoff_base {
	/** 分页查询相关 */
	protected $_start;
	protected $_perpage;
	protected $_page;
	/** 更新时间 */
	protected $_updated;

	public function execute() {

		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		/**
		 * 状态
		 * doing: 待我审批
		 * done: 已审批
		 */
		$sts = array('doing', 'done');
		$status = (string)$this->request->get('status');
		$status = in_array($status, $sts) ? $status : 'doing';

		/** 调用处理方法 */
		$list = array();
		$serv = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->list_by_uid(startup_env::get('wbs_uid'), array(
			'updated' => $this->_updated,
			'status' => $status
		), $this->_start, $this->_perpage);

		/** 整理输出 */
		$uda = &uda::factory('voa_uda_frontend_askoff_format');
		if (!$uda->askoff_list($list)) {
			$this->_error_message($uda->error, get_referer());
			return false;
		}

		$this->view->set('list', $list);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('status', $status);

		$tpl = 'askoff/deal';
		if (startup_env::get('inajax')) {
			$tpl = 'askoff/list_li';
		}

		$this->_output($tpl);
	}
}
