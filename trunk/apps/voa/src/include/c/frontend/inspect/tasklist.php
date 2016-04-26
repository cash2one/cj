<?php
/**
 * 巡店计划列表信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_tasklist extends voa_c_frontend_inspect_base {
	// 起始位置
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {

		// 读取计划列表
		$uda_inspect = new voa_uda_frontend_inspect_list();
		$in = array(
			'm_uid' => $this->_user['m_uid'],
			'ins_type' => array(voa_d_oa_inspect::TYPE_WAITING, voa_d_oa_inspect::TYPE_DOING)
		);
		$list = array();
		$uda_inspect->execute($in, $list);

		// 解出数据
		$page = $uda_inspect->get_page();
		$perpage = $uda_inspect->get_perpage();

		$next_page = $page;
		if (!empty($list)) {
			$next_page = $page + 1;
		}

		$this->view->set('perpage', $perpage);
		$this->view->set('page', $next_page);
		$this->view->set('shops', $this->_shops);
		$this->view->set('list', $list);

		// 模板
		$tpl = 'inspect/tasklist';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

}
