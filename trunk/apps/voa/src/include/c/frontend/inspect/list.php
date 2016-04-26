<?php
/**
 * 巡店列表信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_list extends voa_c_frontend_inspect_base {
	// 时间戳
	protected $_updated = 0;
	// 起始位置
	protected $_start;
	protected $_perpage;
	protected $_page;

	public function execute() {

		/**
		 * 动作
		 * mine: 我的巡店记录
		 * recv: 我收到的
		 */
		$acs = array('mine', 'recv');
		// 获取操作
		$ac = (string)$this->request->get('ac');

		// 更新时间
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		// 读取记录列表
		$ac = in_array($ac, $acs) ? $ac : 'mine';
		$func = '_fetch_'.$ac;

		try {
			if (!method_exists($this, $func)) {
				$this->_error_message('undefined_action');
				return false;
			}

			$list = $this->$func();
		} catch (help_exception $e) {
			$this->_error_message($e->getMessage());
		}

		$next_page = $this->_page;
		if (!empty($list)) {
			$next_page = $this->_page + 1;
		}

		$this->view->set('list', $list);
		$this->view->set('ac', $ac);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('page', $next_page);
		$this->view->set('shops', $this->_shops);

		// 模板
		$tpl = 'inspect/list';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	// 获取我的巡店列表
	protected function _fetch_mine() {

		// 获取请求参数
		$params = $this->request->getx();
		$params['ins_type'] = voa_d_oa_inspect::TYPE_DONE;
		$params['m_uid'] = startup_env::get('wbs_uid');

		// 获取数据
		$uda = new voa_uda_frontend_inspect_list();
		$list = array();
		$uda->execute($params, $list);

		// 解出数据
		$this->_page = $uda->get_page();
		$this->_perpage = $uda->get_perpage();

		return $list;
	}

	// 读取我收到的
	protected function _fetch_recv() {

		// 获取请求参数
		$params = $this->request->getx();
		$params['m_uid'] = startup_env::get('wbs_uid');

		// 获取数据
		$uda_mem = new voa_uda_frontend_inspect_mem_listrecv();
		$mlist = array();
		$uda_mem->execute($params, $mlist);

		// 获取 ins_id
		$ins_ids = array();
		foreach ($mlist as $_v) {
			$ins_ids[] = $_v['ins_id'];
		}

		if (empty($ins_ids)) {
			return array();
		}

		// 获取巡店信息
		$uda_inspect = new voa_uda_frontend_inspect_list();
		$list = array();
		$uda_inspect->execute(array('ins_id' => $ins_ids), $list);

		// 解出数据
		$this->_page = $uda_inspect->get_page();
		$this->_perpage = $uda_inspect->get_perpage();

		return $list;
	}

}
