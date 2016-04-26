<?php
/**
 * 会议纪要列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_minutes_search extends voa_c_frontend_minutes_base {
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

		$this->_sotext = (string)$this->request->get('sotext');
		$this->_sotext = trim($this->_sotext);

		/** 搜索条件 */
		$conditions = array('updated' => $this->_updated);
		$this->_so_conditions($conditions);

		/** 读取内容 */
		$serv = &service::factory('voa_s_oa_minutes_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_search(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_perpage);

		/** 取出 mi_id */
		$mi_ids = array();
		foreach ($list as $v) {
			$mi_ids[] = $v['mi_id'];
		}

		$fmt = &uda::factory('voa_uda_frontend_minutes_format');
		/** 读取报告内容 */
		$serv = &service::factory('voa_s_oa_minutes', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($mi_ids);
		/** 整理输出 */
		foreach ($list as &$v) {
			$fmt->minutes($v);
			$v['_created_fmt'] = voa_h_func::date_fmt('y m d w', $v['mi_created']);
			list($v['_ymd'], $v['_hi']) = explode(' ', $v['_created']);
			$this->_updated = $v['mi_updated'];
		}

		unset($v);

		$this->view->set('list', $list);
		$this->view->set('updated', $this->_updated);
		$this->view->set('sotext', $this->_sotext);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('perpage', $this->_perpage);

		/** 模板 */
		$tpl = 'minutes/search';
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
			$conditions['reporttime'] = floor($report_time / 86400) * 86400;
			return false;
		}

		$conditions['username'] = '%'.$this->_sotext.'%';
		return true;
	}
}
