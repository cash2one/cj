<?php
/**
 * 资料列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_datum_list extends voa_c_frontend_datum_base {
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

		/**  */

		/** 读取用户可以查看的资料列表 */
		$serv_m = &service::factory('voa_s_oa_datum_mem', array('pluginid' => startup_env::get('pluginid')));
		$mlist = $serv_m->fetch_by_uid(startup_env::get('wbs_uid'), $this->_start, $this->_perpage);

		/** 取出 dr_id */
		$dr_ids = array();
		foreach ($mlist as $v) {
			$dr_ids[] = $v['dr_id'];
		}

		/** 读取资料内容 */
		$serv = &service::factory('voa_s_oa_datum', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($dr_ids);
		/** 整理输出 */
		foreach ($list as &$v) {
			$v['dr_subject'] = rhtmlspecialchars($v['dr_subject']);
			$v['_created'] = rgmdate($v['dr_created'], 'u');
			$this->_updated = $v['dr_updated'];
		}

		unset($v);

		$this->view->set('list', $list);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('navtitle', '资料列表');

		/** 模板 */
		$tpl = 'datum/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}
}
