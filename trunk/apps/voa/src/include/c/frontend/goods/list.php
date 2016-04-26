<?php
/**
 * 商品列表信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_goods_list extends voa_c_frontend_goods_base {

	public function execute() {

		// 获取分页参数
		$page = (int)$this->request->get('page');
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		// 读取数据
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_pluginname);
		// 如果列表获取失败
		if (!$uda->list_all($this->request->getx(), array($start, $perpage), $list)) {
			$this->_error_message($uda->error.'(errno:'.$uda->errno.')');
			return true;
		}

		$this->view->set('list', $list);
		$this->view->set('tablecol', voa_h_cache::get_instance()->get('plugin.'.$this->_pluginname.'.tablecol', 'oa'));
		$this->_output('goods/list');
	}

}
