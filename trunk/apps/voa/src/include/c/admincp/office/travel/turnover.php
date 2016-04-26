<?php
/**
 * 业绩与提成
 *
 */

class voa_c_admincp_office_travel_turnover extends voa_c_admincp_office_travel_base {

	public function execute() {
		// 部门信息
		$this->view->set('departments', $this->_department_list());
		$this->view->set('turnover_url', $this->cpurl($this->_module, $this->_operation, 'turnover', $this->_module_plugin_id));

		$act = $this->request->get('act');
		$acts = array('list', 'view', 'putout');
		$act = empty($act) || !in_array($act, $acts) ? 'list' : $act;
		// 加载子动作
		$func = '_'.$act;

		$this->$func();
	}

	// 查看单个用户的销售信息
	protected function _view() {
		/**
		 * 搜索快捷方式
		 * day => 日
		 * week => 周
		 * month => 月
		 * march => 三月
		 */
		$so_dates = array(
			'day' => array(rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d'), '日'),
			'week' => array(rgmdate(startup_env::get('timestamp') - 86400 * 7, 'Y-m-d'), '周'),
			'month' => array(rgmdate(startup_env::get('timestamp') - 86400 * 30, 'Y-m-d'), '月')
		);

		// 获取销售uid
		$saleuid = (int)$this->request->get('saleuid');

		// 读取用户信息
		$serv_m = &service::factory('voa_s_oa_member');
		if (!$saleinfo = $serv_m->fetch_by_uid($saleuid)) {
			$this->_error_message('该销售人员不存在');
			return true;
		}

		$uda_to = new voa_uda_frontend_travel_turnover_get();
		// 取昨天的销售提成/业绩
		$params = array('saleuid' => array($saleuid),
				'start_date' => rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d'),
				'end_date' => rgmdate(startup_env::get('timestamp'), 'Y-m-d'));
		$to_yesterday = array();
		if (!$uda_to->execute($params, $to_yesterday)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		// 取最近一个月
		$params = array('saleuid' => array($saleuid), 'start_date' => rgmdate(startup_env::get('timestamp') - 86400 * 30, 'Y-m-d'));
		$to_month = array();
		if (!$uda_to->execute($params, $to_month)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		// 取所有的
		$params = array('saleuid' => array($saleuid));
		$to_total = array();
		if (!$uda_to->execute($params, $to_total)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		$params = $this->request->getx();
		$params['saleuid'] = array($saleuid);
		// 根据条件取销售业绩和提成
		$to_search = array();
		if (!$uda_to->execute($params, $to_search)) {
			$this->_error_message('销售信息读取失败');
			return true;
		}

		// 根据条件读取商品销售记录
		$uda_og = &uda::factory('voa_uda_frontend_travel_ordergoods_list');
		$list = array();
		if (!$uda_og->execute($params, $list)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		// 分页
		if (0 < $uda_og->get_total()) {
			$pagerOptions = array(
				'total_items' => $uda_og->get_total(),
				'per_page' => $uda_og->get_perpage(),
				'current_page' => $uda_og->get_page(),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$this->view->set('multi', $multi);
		}

		$this->view->set('saleinfo', $saleinfo);
		$this->view->set('to_yesterday', $to_yesterday);
		$this->view->set('to_month', $to_month);
		$this->view->set('to_total', $to_total);
		$this->view->set('start_date', $this->request->get('start_date'));
		$this->view->set('end_date', $this->request->get('end_date'));
		$this->view->set('so_dates', $so_dates);
		$this->view->set('so_date', $this->request->get('so_date'));
		$this->view->set('saleuid', $this->request->get('saleuid'));
		$this->view->set('list', $list);

		$this->output('office/customize/turnover_view');
	}

	// 取列表
	protected function _list() {

		/**
		 * 排序
		 * price => 销售业绩
		 * profit => 提成
		 */
		$orderbys = array('price' => '按业绩排名', 'profit' => '按提成排名');
		/**
		 * 搜索快捷方式
		 * day => 日
		 * week => 周
		 * month => 月
		 * march => 三月
		 */
		$so_dates = array(
			'day' => array(rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d'), '日'),
			'week' => array(rgmdate(startup_env::get('timestamp') - 86400 * 7, 'Y-m-d'), '周'),
			'month' => array(rgmdate(startup_env::get('timestamp') - 86400 * 30, 'Y-m-d'), '月'),
			'march' => array(rgmdate(startup_env::get('timestamp') - 86400 * 90, 'Y-m-d'), '三月')
		);

		$params = $this->request->getx();
		// 判断 orderby 是否合法
		if (isset($params['orderby']) && !empty($orderbys[$params['orderby']])) {
			unset($params['orderby']);
		}

		// 判断 cd_id 是否正确
		$departments = $this->_department_list();
		if (isset($params['cd_id'])
				&& (0 == $departments[$params['cd_id']]['cd_upid'] || empty($departments[$params['cd_id']]))) {
			unset($params['cd_id']);
		}

		try {
			// 调用 uda 读取列表
			$uda = &uda::factory('voa_uda_frontend_travel_turnover_list');
			$list = array();
			if (!$uda->execute($params, $list)) {
				$this->_error_message($uda->errmsg);
				return true;
			}

			// 分页
			if (0 < $uda->get_total()) {
				$pagerOptions = array(
					'total_items' => $uda->get_total(),
					'per_page' => $uda->get_perpage(),
					'current_page' => $uda->get_page(),
					'show_total_items' => true,
				);
				$multi = pager::make_links($pagerOptions);
				pager::resolve_options($pagerOptions);

				$this->view->set('multi', $multi);
			}

		} catch (help_exception $e) {
			$this->_error_message($e->getMessage());
			return true;
		}

		$this->view->set('username', $this->request->get('username'));
		$this->view->set('cd_id', $this->request->get('cd_id'));
		$this->view->set('start_date', $this->request->get('start_date'));
		$this->view->set('end_date', $this->request->get('end_date'));
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('orderby', $this->request->get('orderby'));
		$this->view->set('so_date', $this->request->get('so_date'));
		$this->view->set('so_dates', $so_dates);
		$this->view->set('orderbys', $orderbys);
		$this->view->set('list', $list);

		$this->output('office/customize/turnover');
	}

	/**
	 * 业绩与提成导出
	 */
	private function _putout(){
		$limit = 1000;
		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir().'excel/';
		$zipname= $path.'turnover'.date('YmdHis',time());
		list($list,$total,$page) = $this->read_data('voa_uda_frontend_travel_turnover_list',1,$limit);
		if(ceil($total/$limit) == 1){$this->putout_excel($list);exit;}
		if (!file_exists($zipname)){
			$zip->open($zipname.'.zip',ZipArchive::OVERWRITE);
			for($i=1; $i<=ceil($total/$limit); $i++){
				if($i != 1)list($list,$total,$page) = $this->read_data('voa_uda_frontend_travel_turnover_list',$i,$limit);
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

		/**
		 * 排序
		 * price => 销售业绩
		 * profit => 提成
		 */
		$orderbys = array('price' => '按业绩排名', 'profit' => '按提成排名');
		/**
		 * 搜索快捷方式
		 * day => 日
		 * week => 周
		 * month => 月
		 * march => 三月
		*/
		$so_dates = array(
				'day' => array(rgmdate(startup_env::get('timestamp') - 86400, 'Y-m-d'), '日'),
				'week' => array(rgmdate(startup_env::get('timestamp') - 86400 * 7, 'Y-m-d'), '周'),
				'month' => array(rgmdate(startup_env::get('timestamp') - 86400 * 30, 'Y-m-d'), '月'),
				'march' => array(rgmdate(startup_env::get('timestamp') - 86400 * 90, 'Y-m-d'), '三月')
		);

		$params = $this->request->getx();
		// 判断 orderby 是否合法
		if (isset($params['orderby']) && !empty($orderbys[$params['orderby']])) {
			unset($params['orderby']);
		}

		// 判断 cd_id 是否正确
		$departments = $this->_department_list();
		if (isset($params['cd_id'])
				&& (0 == $departments[$params['cd_id']]['cd_upid'] || empty($departments[$params['cd_id']]))) {
					unset($params['cd_id']);
		}

		list($start, $perpage, $page) = voa_h_func::get_limit($page, $limit);
		$params['page'] = $page;
		$params['perpage'] = $perpage;
		// 调用 uda 读取列表
		$uda = &uda::factory('voa_uda_frontend_travel_turnover_list');
		$list = array();
		if (!$uda->execute($params, $list)) {
			$this->_error_message($uda->errmsg);
			return true;
		}

		//重组数据
		foreach ($list as &$v) {
			$v['price'] = $v['price'] / 100;
			$v['profit'] =  sprintf("%.2f",substr(sprintf("%.3f", $v['profit'] / 100), 0, -1));
		}
		unset($v);

		$total = $uda->get_total();
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
		list($title_string, $title_width, $row_data) = $this->_excel_data_turnover($list);
		excel::make_tmp_excel_download('业绩与提成列表', $tmppath.$i.'.xls', $title_string, $title_width, $row_data, $options, $attrs);
		return $tmppath.$i.'.xls';
	}

	/**
	 * 导出excel
	 * @param array $list
	 */
	private function putout_excel($list){
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data_turnover($list);
		excel::make_excel_download('业绩与提成列表', $title_string, $title_width, $row_data, $options, $attrs);
	}
}
