<?php
/**
 * voa_c_admincp_office_sale_view
 * 企业后台 - 销售管理 - 详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sale_view extends voa_c_admincp_office_sale_base {

	public function execute() {
	
		//获取数据
		$serv = &service::factory('voa_s_oa_sale_coustmer');
		$scid = $this->request->get('scid');
		if (empty($scid)) {
			$this->message('error', '没有获取到 '.$this->_module_plugin['cp_name'].' 详情数据');
		}
		$sale = array();
		$sale = $serv->get($scid);
		if(empty($sale)){
			$this->message('error', '没有获取到 '.$this->_module_plugin['cp_name'].' 详情数据');
		}
		//获取
		list($total, $multi, $list) = $this->_trajectory_search($scid);
		foreach($list as $k=>$v){
			$list[$k]['created'] = date("Y-m-d",$list[$k]['created']);
			$list[$k]['updated'] = date("Y-m-d",$list[$k]['updated']);
		}
	

	   $sale['created']=date('Y-m-d',$sale['created']);
	
		//展示数据
		$this->view->set('sale', $sale);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->output('office/sale/view');
	}
	
	/**
	 *查询一个客户的回访记录
	 *@param int $scid
	 *@param int $perpage
	 *return array($total, $multi, $list)
	 */
	 protected function _trajectory_search($scid, $perpage = 12) {
			$list = array();
			$multi = null;
			$conds = array();
			//查询条件
			$conds = array('scid = ?' => $scid);
			//获取数据
			$serv = &service::factory('voa_s_oa_sale_trajectory');
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
			return array($total, $multi, $list);
	 }
}
