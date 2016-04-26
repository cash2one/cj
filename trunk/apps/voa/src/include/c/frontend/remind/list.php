<?php
/**
 * 定时提醒列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_remind_list extends voa_c_frontend_remind_base {
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

		/** 读取定时提醒内容 */
		$serv = &service::factory('voa_s_oa_remind', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_all($this->_start, $this->_perpage);
		/** 整理输出 */
		foreach ($list as &$v) {
			$v['rm_subject'] = rhtmlspecialchars($v['rm_subject']);
			$v['_created'] = rgmdate($v['rm_created'], 'u');
			$this->_updated = $v['rm_updated'];
		}

		unset($v);

		$this->view->set('list', $list);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('navtitle', '定时提醒列表');

		/** 模板 */
		$tpl = 'remind/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}
}
