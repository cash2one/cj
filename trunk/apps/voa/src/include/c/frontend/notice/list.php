<?php
/**
 * 公告列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_notice_list extends voa_c_frontend_notice_base {
	/** 分页查询相关 */
	protected $_start;
	protected $_perpage;
	protected $_page;
	/** 更新时间 */
	protected $_updated;
	protected $_sotext;

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		$this->_sotext = (string)$this->request->get('sotext');
		$this->_sotext = trim($this->_sotext);
		/** 搜索条件 */
		$conditions = array('updated' => $this->_updated);
		$this->_so_conditions($conditions);
		/** 读取公告内容 */
		/*$serv = &service::factory('voa_s_oa_notice', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_all($this->_start, $this->_perpage);*/
		/** 读取报告内容 */
		$serv = &service::factory('voa_s_oa_notice', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_search(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_perpage);
		/** 整理输出 */
		$fmt = &uda::factory('voa_uda_frontend_notice_format');
		$fmt->format_list($list);

		$this->view->set('list', $list);
		$this->view->set('sotext', $this->_sotext);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);

		/** 模板 */
		$tpl = 'notice/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _so_conditions(&$conditions) {
		/** 判断是否为时间格式 */
		$report_time = rstrtotime($this->_sotext);
		if (0 < $report_time) {
			$datetime = explode(' ',$this->_sotext);
			$conditions['nt_created'] = rstrtotime($datetime[0]);
			return false;
		}

		$conditions['nt_subject'] = '%'.$this->_sotext.'%';
		return true;
	}
}
