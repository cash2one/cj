<?php
/**
 * 专题库
 *
 */

class voa_c_admincp_office_travel_material extends voa_c_admincp_office_base {
	protected $_cur_url = '';

	public function execute() {

		$this->_cur_url = $this->cpurl($this->_module, $this->_operation, 'material', $this->_module_plugin_id);
		$this->view->set('material_url', $this->_cur_url);

		$act = $this->request->get('act');
		$acts = array('list', 'update', 'del', 'goodslist');
		$act = empty($act) || !in_array($act, $acts) ? 'list' : $act;
		// 加载子动作
		$func = '_'.$act;

		$this->$func();
	}

	// 新增专题
	protected function _update() {

		// 如果是 post 操作
		if ($this->_is_post()) {
			$uda = &uda::factory('voa_uda_frontend_travel_material_update');
			$result = null;
			$params = $this->request->getx();
			if (!$uda->execute($params, $result)) {
				$this->_error_message($uda->errmsg);
				return true;
			}

			$this->_success_message('操作成功', null, null, false, $this->__get_refer());
			return true;
		}

		// 如果有mtid
		$mtid = (int)$this->request->get('mtid');
		$material = array();
		if (!empty($mtid)) {
			$uda_get = &uda::factory('voa_uda_frontend_travel_material_get');
			$params = $this->request->getx();
			if (!$uda_get->execute($params, $material)) {
				$this->_error_message('读取专题记录失败', null, null, false, $this->__get_refer());
			}
		}

		// 初始化编辑器
		$ueditor = new ueditor();
		$content_key = 'message';
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name').'.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array(
			'toolbars' => '_mobile',
			'textarea' => $content_key,
			'initialFrameHeight' => 300,
			'initialContent' => isset($material['message']) ? $material['message'] : '',
			'elementPathEnabled' => false
		);
		if (!$ueditor->create_editor($content_key, '', array('goodslink'))) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		$this->view->set('ueditor_output', $ueditor_output);
		$this->view->set('material', $material);
		$this->view->set('refer', $this->__get_refer());

		$this->output('office/customize/material_update');
	}

	// 获取商品列表
	protected function _goodslist() {

		$ptname = array(
			'plugin' => 'travel',
			'table' => 'goods',
			'classes' => voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa'),
			'columns' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa'),
			'options' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa')
		);
		// 获取分页参数
		$page = (int)$this->request->get('page');
		$limit = (int)$this->request->get('limit');
		$limit = 0 >= $limit ? 20 : $limit;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, 50));

		$uda = new voa_uda_frontend_goods_data($ptname);
		$list = array();
		$total = 0;
		$params = $this->request->getx();
		if (!$uda->list_all($params, array($start, $perpage), $list, $total)) {
			exit($uda->errmsg);
			return true;
		}

		$this->view->set('list', $list);

		$this->output('office/customize/material_goodslist');
	}

	// 删除专题
	protected function _del() {

		$uda = &uda::factory('voa_uda_frontend_travel_material_del');
		$result = null;
		$params = $this->request->getx();
		if (!$uda->execute($params, $result)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		$this->_success_message('专题信息删除成功', null, null, false, $this->__get_refer());
	}

	// 获取专题列表
	protected function _list() {

		$uda = &uda::factory('voa_uda_frontend_travel_material_list');
		$list = array();
		$params = $this->request->getx();
		$params['perpage'] = 20;
		if (!$uda->execute($params, $list)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		// 输出分页信息
		$multi = pager::make_links(array(
			'total_items' => $uda->get_total(),
			'per_page' => 20,
			'current_page' => $uda->get_page(),
			'show_total_items' => true,
		));
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('subject', $this->request->get('subject'));
		$this->view->set('start_date', $this->request->get('start_date'));
		$this->view->set('end_date', $this->request->get('end_date'));

		$this->output('office/customize/material');
	}

	// 获取refer
	private function __get_refer() {

		$refer = (string)$this->request->get('refer');
		if (empty($refer)) {
			$refer = isset($_SERVER['HTTP_REFERER']) ? rhtmlspecialchars($_SERVER['HTTP_REFERER']) : '';
		}

		if (empty($refer)) {
			$refer = $this->_cur_url;
		}

		return $refer;
	}
}
