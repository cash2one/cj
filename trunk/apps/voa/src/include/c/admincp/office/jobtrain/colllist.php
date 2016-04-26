<?php
/**
* 收藏人数
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_colllist extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$aid = $this->request->get('aid');
		
		$limit = 20;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$page_option = array($start, $limit);

		try {
			// 载入搜索uda类
			$uda_list = &uda::factory('voa_uda_frontend_jobtrain_coll');
			// 数据结果
			$result = array();
			$uda_list->list_coll($result, array('aid'=>$aid), $page_option);
			// 获取内容
			$uda = &uda::factory('voa_uda_frontend_jobtrain_article');
			$article = $uda->get_article($aid);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 分页链接信息
		$multi = '';
		if ($result['total'] > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $result['total'],
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			));
		}
		// 注入模板变量
		$this->view->set('total', $result['total']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('article', $article);
		// 导出链接
		$this->view->set('coll_export_url', $this->cpurl($this->_module, $this->_operation, 'collexport', $this->_module_plugin_id, array('aid' => $aid)));
		$this->output('office/jobtrain/colllist');
	}

}