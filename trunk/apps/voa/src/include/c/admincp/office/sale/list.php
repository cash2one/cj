<?php
/**
 * voa_c_admincp_office_sale_list
 * 企业后台 - 销售管理 - 列表
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sale_list extends voa_c_admincp_office_sale_base {

	public function execute() {

		/** 搜索默认值 */
		$searchDefaults = array(
				'created_time_after' => '',
				'created_time_before' =>'',
				'updated_time_after' => '',
				'updated_time_before' => '',
				'short_name' => '',
				'slae_name' => '',
				'type' => '999',//全部类型
				'source' => '999',//全部来源
		);
		$issearch = $this->request->get('issearch') ? 1 : 0;
		
		$perpage = 15;
		
		if ($this->request->get('is_dump')) {
			$perpage = 10000;
		}
		list($total, $multi, $list, $searchBy) = $this->_sale_search($issearch, $searchDefaults, $perpage);
		// 请求的是导出操作
		if ($this->request->get('is_dump')) {
			$this->__dump_list($list);
			return true;
		}
		
		
		
		$this->_format_data($list);

	    $source = $this->_get_source();
		$souce_num = count($source);
	    $type = $this->_get_type();
		$type_num = count($type);

		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('search_by',  $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('souce_num', $souce_num);
		$this->view->set('type_num', $type_num);
		$this->view->set('source', $this->_get_source());
		$this->view->set('type', $this->_get_type());
		
		$this->view->set('form_search_action_url', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->view->set('form_delete_url', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id));
		$this->view->set('delete_url_base', $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('scid' => '')));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('scid' => '')));

		$this->output('office/sale/list');

	}
	private function _get_source() {
		$source = &service::factory('voa_s_oa_sale_type');
		return $source->list_by_conds(array("type" => 3));
	}
	
	private function _get_type() {
		$type = &service::factory('voa_s_oa_sale_type');
		return $type->list_by_conds(array("type" => 2));
	}
	
	/**
	 * 搜索活动报名
	 * @param number $issearch
	 * @param array $searchDefaults
	 * @param array $searchBy
	 * @param number $perpage
	 * @return array(total, multi, list)
	 */
	protected function _sale_search($issearch = 0, $searchDefaults = array(),$perpage = 12) {
			/** 搜索条件 */
			$searchBy = array();
			$conds = array();
			if ( $issearch ) {
				//查询条件
				foreach ( $searchDefaults AS $_k => $_v ) {
					if ( isset($_GET[$_k]) && $this->request->get($_k) != $_v ) {
						if($this->request->get($_k) != null){
							$searchBy[$_k] = $this->request->get($_k);
						}else{
							$searchBy[$_k] = $_v;
						}
					}
				}
				$searchBy = array_merge($searchDefaults, $searchBy);
			}else{
				$searchBy = $searchDefaults;
			}

			//组合搜索条件
			if(!empty($searchBy)){
				
				$this->_add_condi($conds,$searchBy);
				
			}

			$list = array();
			$multi = null;
			//获取数据
			$serv = &service::factory('voa_s_oa_sale_coustmer');
			$total = $serv->count_by_conds($conds);
			if($total > 0){
				$pagerOptions = array(
							'total_items' => $total,
							'per_page' => $perpage,
							'current_page' => $this->request->get('page'),
							'show_total_items' => true,
				);
				$multi = pager::make_links($pagerOptions);
				pager::resolve_options($pagerOptions);
				
				$page_option[0] = $pagerOptions['start'];
				$page_option[1] = $perpage;
				$orderby['updated'] = 'DESC';
				
				$list = $serv->list_by_conds($conds,$page_option,$orderby);
			}
			return array($total, $multi, $list, $searchBy);

	}
	
	/**
	 * 导出CSV文件
	 * @param array $list
	 */
	private function __dump_list($list) {

		// 待输出的数据，数组格式
		$data = array();
		// 标题栏 - 字段名称
		$data[] = array(
			'全称',
			'简称',
			'客户来源',
			'销售阶段',
			'跟进人',
			'更新时间'
		);
		foreach($list as $v) {
			$data[] = array(
				$v['company'],
				$v['companyshortname'],
				$v['source'],
				$v['type'],
				$v['sale_name'],
				date("Y-m-d H:i", $v['updated']) 
			);
		}

		// 转换为csv字符串
		$csv_data = array2csv($data);

		$filename = 'sale_'.rgmdate(startup_env::get('timestamp'), 'YmdHis').'.csv';

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: text/csv");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Coentent_Length: '.strlen($csv_data));
		echo $csv_data;

		exit;
	}
	
	/**
	 * 展示数据格式化
	 * @param array $list
	 */
	 protected function _format_data(&$list) {
			 foreach($list as $key => $value) {
					$list[$key]['_created'] = date("Y-m-d", $value['created']) ;
					$list[$key]['_updated'] = date("Y-m-d", $value['updated']) ;
			 }
		 
	 }
	 
	 /**
	  *状态判断
	  *@param int conds
	  *@param array searchBy
	  */
	 protected function _add_condi(&$conds,$searchBy) {
			if(!empty($searchBy['created_time_after'])) { //创建时间
				$conds['created >= ?'] = strtotime($searchBy['created_time_after']);
			}
			if(!empty($searchBy['created_time_before'])) {//创建时间
				$conds['created <= ?'] = strtotime($searchBy['created_time_before']);
			}
			if(!empty($searchBy['updated_time_after'])) { //更新时间
				$conds['updated >= ?'] = strtotime($searchBy['updated_time_after']);
			}
			if(!empty($searchBy['updated_time_before'])) {//更新时间
				$conds['updated <= ?'] = strtotime($searchBy['updated_time_before']);
			}
			if(!empty($searchBy['short_name'])) {//公司简称
				$conds["companyshortname like ?"]  = "%".$searchBy['short_name']."%";
			}
			if(!empty($searchBy['slae_name'])) {//跟踪人
				$conds['slae_name like ?'] = "%".$searchBy['slae_name']."%";
			}
			if(!empty($searchBy['type'])) {//销售状态
				if($searchBy['type'] != 999) {
					$conds['type_stid'] = $searchBy['type'];
				}
			}
			if(!empty($searchBy['source'])) {//客户来源
				if($searchBy['source'] != 999) {
					$conds['source_stid'] = $searchBy['source'];
				}
			}
	 }	 
}
