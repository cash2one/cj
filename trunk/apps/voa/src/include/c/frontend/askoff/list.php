<?php
/**
 * 请假申请列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_list extends voa_c_frontend_askoff_base {
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

		/**
		 * 动作集合
		 * my: 我的请假申请列表
		 */
		$acs = array('my');

		/** 动作 */
		$ac = trim($this->request->get('ac'));
		if (!in_array($ac, $acs)) {
			$ac = 'my';
		}

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		/** 调用处理方法 */
		$list = array();
		$func = '_'.$ac;
		if (!method_exists($this, $func)) {
			$this->_error_message('undefine_action');
			return false;
		}

		$list = call_user_func(array($this, $func));
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

		$tpl = 'askoff/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	/** 读取我发起的请假 */
	protected function _my() {
		$serv = &service::factory('voa_s_oa_askoff', array('pluginid' => startup_env::get('pluginid')));
		return $serv->fetch_mine(startup_env::get('wbs_uid'), $this->_start, $this->_perpage);
	}

	/** 获取待批复的请假列表 */
	protected function _deal() {
		$serv = &service::factory('voa_s_oa_askoff_proc', array('pluginid' => startup_env::get('pluginid')));
		return $serv->fetch_by_conditions(array('m_uid' => startup_env::get('wbs_uid')), $this->_start, $this->_perpage);
	}
}

