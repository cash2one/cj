<?php
/**
 * 项目列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_secret_list extends voa_c_frontend_secret_base {
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

		/** 更新时间 */
		$updated = intval($this->request->get('updated'));
		$updated = empty($updated) ? startup_env::get('timestamp') + 1 : $updated;

		/** 整理输出 */
		foreach ($list as &$v) {
			$v['_created'] = rgmdate($v['p_created'], 'u');
			$v['_updated'] = rgmdate($v['p_updated']);
			$updated = $v['p_updated'];
		}

		unset($v);

		$this->view->set('list', rhtmlspecialchars($list));
		$this->view->set('updated', $updated);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('navtitle', '秘密列表');

		$inajax = startup_env::get('inajax');
		if (empty($inajax)) {
			$this->_output('secret/list');
		} else {
			$this->_output('secret/list_li');
		}
	}
}
