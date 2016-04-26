<?php
/**
 * 备忘列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_search extends voa_c_frontend_vnote_base {
	protected $_start;
	protected $_perpage;
	protected $_page;
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

		/** 读取备忘内容 */
		$serv = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_search(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_perpage);

		/** 取出 vn_id */
		$vn_ids = array();
		foreach ($list as $v) {
			$vn_ids[] = $v['vn_id'];
		}

		$uda = &uda::factory('voa_uda_frontend_vnote_format');
		/** 读取备忘内容 */
		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($vn_ids);
		/** 整理输出 */
		foreach ($list as &$v) {
			$uda->format($v);
			$this->_updated = $v['vn_updated'];
		}

		unset($v);

		$this->view->set('list', $list);
		$this->view->set('sotext', $this->_sotext);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);

		/** 模板 */
		$tpl = 'vnote/search';
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
		$vn_created = rstrtotime($this->_sotext);
		if (0 < $vn_created) {
			$conditions['vn_created'] = $vn_created;
			return true;
		}

		$conditions['username'] = '%'.$this->_sotext.'%';
		return true;
	}
}
