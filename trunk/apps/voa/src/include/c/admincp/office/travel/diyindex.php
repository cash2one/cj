<?php
/**
 * 首页
 *
 */

class voa_c_admincp_office_travel_diyindex extends voa_c_admincp_office_base {

	protected $_cur_url = '';

	public function execute() {

		$this->_cur_url = $this->cpurl($this->_module, $this->_operation, 'index', $this->_module_plugin_id);
		$this->view->set('index_url', $this->_cur_url);

		$act = $this->request->get('act');
		$acts = array('list', 'update', 'del', 'goods', 'goodsclass', 'material');
		$act = empty($act) || !in_array($act, $acts) ? 'list' : $act;
		// 加载子动作
		$func = '_'.$act;

		$this->$func();
	}

	// 新增素材
	protected function _update() {

		// 如果是 post 操作
		if ($this->_is_post()) {
			try {
				$uda = &uda::factory('voa_uda_frontend_travel_diyindex_update');
				$result = null;
				$params = $this->request->getx();
				if (!$uda->execute($params, $result)) {
					$this->_ajax_message($uda->errcode, $uda->errmsg);
					return true;
				}
			} catch (help_exception $e) {
				$this->_ajax_message($e->getCode(), $e->getMessage());
				return true;
			}

			$this->_ajax_message(0, null);
			return true;
		}

		// 如果有tiid
		$tiid = (int)$this->request->get('tiid');
		$index = array();
		$default_users = array();
		if (!empty($tiid)) {
			$uda_get = &uda::factory('voa_uda_frontend_travel_diyindex_get');
			$params = $this->request->getx();
			if (!$uda_get->execute($params, $index)) {
				$this->_error_message('读取首页记录失败', null, null, false, $this->__get_refer());
			}

			if (!empty($index['uid'])) {
				$serv_m = &service::factory('voa_s_oa_member');
				if ($member = $serv_m->fetch_by_uid($index['uid'])) {
					$default_users[] = array(
						'id' => $member['m_uid'],
						'name' => $member['m_username'],
						'input_name' => 'uid'
					);
				}
			}
		}

		$indexes = array();
		if (!empty($index) && is_array($index['_message'])) {
			$indexes = $index['_message'];
		}

		$this->view->set('default_user', $default_users);
		$this->view->set('index', $index);
		$this->view->set('indexs', json_encode($indexes));
		$this->view->set('refer', $this->__get_refer());

		$this->output('office/customize/diyindex_update');
	}

	// 读取列表
	protected function _list() {

		$uda = &uda::factory('voa_uda_frontend_travel_diyindex_list');
		$list = array();
		$params = $this->request->getx();
		if (!$uda->execute($params, $list)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		$this->view->set('list', $list);

		$this->output('office/customize/diyindex');
	}

	// 删除首页
	protected function _del() {

		$uda = &uda::factory('voa_uda_frontend_travel_diyindex_del');
		$result = null;
		$params = $this->request->getx();
		if (!$uda->execute($params, $result)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		$this->_success_message('首页信息删除成功', null, null, false, $this->__get_refer());
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

	protected function _goods() {

		$ptname = array(
			'plugin' => 'travel',
			'table' => 'goods',
			'classes' => voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa'),
			'columns' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa'),
			'options' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa')
		);
		$limit = 20;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_data', $ptname);
		if (!$uda->list_all(array(), array(($page - 1) * $limit, $limit), $list, $total)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		// 输出分页信息
		$multi = pager::make_links(array(
			'total_items' => $total + 100,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		));

		$this->view->set('list', empty($list) ? array() : $list);
		$this->view->set('multi', $multi);
		$this->output('office/customize/diyindex_goods');
	}

	protected function _goodsclass() {

		$ptname = array(
			'plugin' => 'travel',
			'table' => 'goods',
			'classes' => voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa'),
			'columns' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa'),
			'options' => voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa')
		);
		// 读取数据
		$total = 0;
		$list = array();
		$uda = &uda::factory('voa_uda_frontend_goods_class', $ptname);
		if (!$uda->list_all(array(), $list, null, $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			$this->_error_message($uda->errno);
			return true;
		}

		// 输出分页信息
		$this->view->set('list', $list);
		$this->output('office/customize/diyindex_goodsclass');
	}

	// 获取素材页面
	protected function _material() {

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

		$this->output('office/customize/diyindex_material');
	}

}
