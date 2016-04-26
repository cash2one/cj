<?php
class voa_c_admincp_office_travel_main extends voa_c_admincp_office_travel_base {

	// 最大 limit 值
	protected $_max_limit = 100;
	public function execute() {
		// 搜索条件
		$conds = $this->request->getx();
		$ac = $this->request->get('ac');
		if (! empty($ac)) { // 根据操作类型，跳转不同页面
			$this->$ac();
			exit();
		}

		$issearch = $this->request->get('issearch');
		list($total, $multi, $list) = $this->_search_goods($conds, $issearch);

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);
		$this->view->set('classes', $this->_ptname['classes']); // 分类
		                                                        // 话题详情url
		$this->view->set('viewUrlBase', $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
			'ac' => 'style#/view/'
		)));

		// 删除话题url
		$this->view->set('deleteUrlBase', $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
			'ac' => 'goods_delete',
			'dataid' => ''
		)));

		$this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/customize/goods');
	}

	/**
	 * 搜索话题记录
	 *
	 * @param number $cp_pluginid
	 * @param boolean $issearch
	 * @param array $searchDefault
	 * @param number $perpage
	 */
	protected function _search_goods($conds, $issearch) {
		$uda_list = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);

		// 获取分页参数
		$page = (int)$this->request->get('page');
		$limit = 15;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, $this->_max_limit));

		$params = array();
		$params['is_admin'] = 1;

		// 条件查询
		if ($issearch) {
			$p_subject = $this->request->get('p_subject');
			if (! empty($p_subject)) {
				$params['query'] = $p_subject;
			}
		}

		// 读取话题列表 及总数
		$total = 0;
		$list = array();
		$uda_list->list_all($params, array(
				$start,
				$perpage
			), $list, $total);

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $perpage,
			'current_page' => $page,
			'show_total_items' => true
		);

		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);
		return array(
			$total,
			$multi,
			$list
		);
	}

	/**
	 * 设置插件/表格名称
	 *
	 * @return boolean
	 */
	protected function _init_ptname() {
		parent::_init_ptname();
		$this->_ptname['classes'] = voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa');
		$this->_ptname['columns'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecol', 'oa');
		$this->_ptname['options'] = voa_h_cache::get_instance()->get('plugin.travel.goodstablecolopt', 'oa');
	}

	/**
	 * 添加产品、产品详情
	 */
	private function style() {
		$p_sets = voa_h_cache::get_instance()->get('plugin.travel.setting', 'oa');
		$this->view->set('pluginid', $this->_module_plugin_id);
		$this->view->set('style', $p_sets['goods_tpl_style']);

		$this->output('office/customize/main');
	}

	/**
	 * 删除产品
	 */
	private function goods_delete() {
		$delete = $this->request->post('delete');
		$tid = rintval($this->request->get('dataid'));

		$ids = 0;
		if ($delete) {
			$ids = rintval($delete, true);
		} elseif ($tid) {
			$ids = rintval($tid, false);
			if (! empty($ids)) {
				$ids = array(
					$ids
				);
			}
		}
		if (empty($ids)) {
			$this->message('error', '请指定要删除的' . $this->_module_plugin['cp_name']);
		}

		$uda = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);
		$result = array();
		if (! $uda->delete(implode(',', $ids), $result)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array()));
	}

	/**
	 * 产品分类列表
	 */
	private function cate() {

		// 搜索条件
		$conds = $this->request->getx();
		$act = $this->request->get('act');
		$issearch = $this->request->get('issearch');

		if (! empty($act)) { // 根据操作类型，跳转不同页面
			$this->$act();
			exit();
		}

		list($total, $multi, $list) = $this->_search_cate($conds, $issearch);

		$this->view->set('multi', $multi);
		$this->view->set('total', $total);
		$this->view->set('list', $list); // 产品分类
		                                 // 删除话题url
		$this->view->set('searchUrl', $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
			'ac' => 'cate'
		)));

		$this->output('office/customize/cate');
	}

	/**
	 *
	 * @param 查询产品分类 $conds
	 * @return boolean
	 */
	protected function _search_cate($conds, $issearch) {

		// 获取分页参数
		$page = $this->request->get('page');
		$limit = 10;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, $this->_max_limit));

		// 读取数据
		$total = 0;
		$list = array();
		$params = array();

		if ($issearch) {
			$classname = $this->request->get('classname');
			if (! empty($classname)) {
				$params['query'] = $classname;
			}
		}

		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		if (! $uda->list_all($params, $list, array(
				$start,
				$perpage
			), $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 分页配置
		$pager_options = array(
			'total_items' => $total,
			'per_page' => $perpage,
			'current_page' => $page,
			'show_total_items' => true
		);

		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		return array(
			$total,
			$multi,
			$list
		);
	}

	/**
	 * 删除产品分类
	 */
	private function cate_delete() {
		// 获取分类id
		$classid = (int)$this->request->get('classid');
		if (empty($classid)) {
			$this->_set_errcode(voa_errcode_oa_travel::CLASSID_IS_EMPTY);
			return true;
		}

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		if (! $uda->delete($classid)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.travel.goodsclass', 'oa', true);

		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
			'ac' => 'cate'
		)));
	}

	/**
	 * 产品分类(添加，编辑)
	 */
	private function cate_edit() {
		$classid = (int)$this->request->get('classid'); // 产品分类id

		$classes = array();
		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		if (! $uda->get_one($classid, $classes)) {
			$this->_error_message($uda->errmsg);
			return true;
		}
		$this->view->set('classes', $classes);
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/customize/cate_edit');
	}

	/**
	 * 产品分类(新增、修改)
	 */
	private function cate_save() {
		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		if ($this->_is_post()) {
			$post = array();
			$classid = $this->request->post('classid');
			$classname = $this->request->post('classname');

			$post['classname'] = $classname;
			if ($classid) { // 修改
				if (! $uda->update($post, $classid)) {
					$this->_error_message($uda->errmsg);
					return true;
				}
				$describ = '更新产品分类信息成功';
			} else { // 新增
				$classes = array();
				if (! $uda->add($post, $classes)) {
					$this->_error_message($uda->errmsg);
					return true;
				}
				$describ = '新增产品分类信息成功';
			}
			$this->_success_message('指定' . $this->_module_plugin['cp_name'] . $describ, null, null, true, $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
				'ac' => 'cate'
			)));
		}
	}

	/**
	 * 快递列表
	 */
	private function express(){
		// 获取分页参数
		$page = $this->request->get('page') ? $this->request->get('page') : 1;
		$limit = 10;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, min($limit, $this->_max_limit));

		// 读取数据
		$total = 0;
		$list = array();
		$params = array();
		$uda = &uda::factory('voa_uda_frontend_goods_express', $this->_ptname);
		if (!$uda->list_all($params, $list, array(
				$start,
				$perpage
		), $total)) {
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}

		// 分页配置
		$pager_options = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $page,
				'show_total_items' => true
		);

		$multi = pager::make_links($pager_options);
		// 引用结果，分页配置
		pager::resolve_options($pager_options);

		//赋值
		$this->view->set('multi', $multi);
		$this->view->set('total', $total);
		$this->view->set('list', $list); // 产品快递


		$this->output('office/customize/express');

	}

	/**
	 * 快递 ( 新增   编辑 )
	 *
	 */
	private function express_operate(){

		if($this->_is_post()){
			$post = array();
			$post['expid'] = $this->request->post('expid');
			$post['exptype'] = $this->request->post('exptype');
			$post['expcost'] = $this->request->post('expcost');
			$uda = &uda::factory('voa_uda_frontend_goods_express', $this->_ptname);
			if($post['expid']){
				if(! $uda->update($post, $post['expid'])) {
					$this->_error_message($uda->errmsg);
					return true;
				}
				$describ = '更新产品快递信息成功';
			}else{
				unset($post['expid']);
				if (! $uda->add($post, $express)) {
					$this->_error_message($uda->errmsg);
					return true;
				}
				$describ = '新增产品快递信息成功';
			}

			// 更新缓存
			voa_h_cache::get_instance()->get('plugin.goods.goodsexpress', 'oa', true);

			$this->_success_message('指定' . $this->_module_plugin['cp_name'] . $describ, null, null, true, $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
					'ac' => 'express'
			)));
		}

		$expid = (int)$this->request->get('expid');
		if($expid){
			$uda = &uda::factory('voa_uda_frontend_goods_express', $this->_ptname);
			if (! $uda->get_one($expid, $express)) {
				$this->_error_message($uda->errmsg);
				return true;
			}
			$this->view->set('express', $express);
		}
		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/customize/express_operate');

	}

	/**
	 * 快递删除
	 */
	private function express_delete(){

		$expid = (int)$this->request->get('expid');
		if (empty($expid)) {
			$this->_set_errcode(voa_errcode_oa_travel::EXPRESSID_IS_EMPTY);
			return true;
		}

		// 删除表格信息
		$uda = &uda::factory('voa_uda_frontend_goods_express', $this->_ptname);
		if (!$uda->delete($expid)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('plugin.goods.goodsexpress', 'oa', true);


		$this->_success_message('指定' . $this->_module_plugin['cp_name'] . '信息删除完毕', null, null, true, $this->cpurl($this->_module, $this->_operation, 'main', $this->_module_plugin_id, array(
				'ac' => 'express'
		)));

	}

	/**
	 * 产品导出
	 */
	private function putout(){
		$limit = 1000;
		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir().'excel/';
		$zipname= $path.'product'.date('YmdHis',time());
		list($list,$total,$page) = $this->read_data('voa_uda_frontend_goods_data',1,$limit);
		if(ceil($total/$limit) == 1){$this->putout_excel($list);exit;}
		if (!file_exists($zipname)){
			$zip->open($zipname.'.zip',ZipArchive::OVERWRITE);
			for($i=1; $i<=ceil($total/$limit); $i++){
				if($i != 1)list($list,$total,$page) = $this->read_data('voa_uda_frontend_goods_data',$i,$limit);
				//生成excel文件
				$result = $this->create_excel($list,$i,$path);
				//将生成的excel文件写入zip文件
				if($result)$zip->addFile($result,$i.'.xls');
			}
			$zip->close();
			//输出至浏览器
			$this->put_header($zipname.'.zip');
			//清理
			$this->clear($path);
		}
	}

	/**
	 * 读取数据
	 * @param string $table
	 * @return array
	 */
	private function read_data($table,$page = 1,$limit = 1000){
		$total = 0;
		$list = array();
		$params = array();
		$params['is_admin'] = 1;
		list($start, $perpage, $page) = voa_h_func::get_limit($page, $limit);
		$uda = &uda::factory($table, $this->_ptname);
		if(!$uda->list_all($params, array($start,$perpage), $list, $total)){
			$this->_errcode = $uda->errno;
			$this->_errmsg = $uda->error;
			return true;
		}
		//赋值分类
		$uda = &uda::factory('voa_uda_frontend_goods_class', $this->_ptname);
		foreach($list as $k=>$v){
			$classes = array();
			if(!$uda->get_one($v['classid'],$classes)){
				$this->_error_message($uda->errmsg);
				return true;
			}
			$list[$k]['classname'] = $classes['classname'];
		}
		unset($classes);
		return array($list,$total,$page);
	}

	/**
	 * 生成excel
	 * @param array $list
	 */
	private function create_excel($list,$i,$tmppath){
		if(!is_dir($tmppath)) mkdir($tmppath,'0777');
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data($list);
		excel::make_tmp_excel_download('产品列表', $tmppath.$i.'.xls', $title_string, $title_width, $row_data, $options, $attrs);
		return $tmppath.$i.'.xls';
	}

	/**
	 * 导出excel
	 * @param array $list
	 */
	private function putout_excel($list){
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data($list);
		excel::make_excel_download('产品列表', $title_string, $title_width, $row_data, $options, $attrs);
	}
}
