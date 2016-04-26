<?php
/**
 * voa_c_admincp_office_sale_setting
 * 企业后台 - 销售管理 - 设置
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_sale_setting extends voa_c_admincp_office_sale_base {

	public function execute() {

		//获取数据
		$type = $this->request->get('type') ? $this->request->get('type') : 1;

		$serv = &service::factory('voa_s_oa_sale_type');
		//删除
		$dstid = $this->request->get('dstid');
		if(!empty($dstid)) {
			$serv->delete(array($this->request->get('dstid')));
		}
		//修改或者新增
		$data = array(
			'name' => $this->request->get('field'),
			'required' => $this->request->get('required'),
			'type' => $this->request->get('types'),
			'color' => $this->request->get('color')
		);
		if ($data['type'] == 1) {
			if (empty($data['required'])) {
				$data['required'] = 0;
			}
		} else {
			unset($data['required']);
		}
		if (empty($data['color']) || ! preg_match('/^#[0-9a-f]{6}$/is', $data['color'])) {
			$data['color'] = '';
		}
		//修改
		$stid = $this->request->get('stid');
		$name = $this->request->get('field');
		if(!empty($stid)) {
			$serv->update_by_conds(array('stid' => $stid), $data);
		} else if(!empty($name)) {//新增
			$serv->insert($data);
		}
		//获取
		list($total, $multi, $list) = $this->_type_search($type);
		foreach($list as $k=>$v){
			$list[$k]['updated'] = date("Y-m-d",$list[$k]['updated']);
		}
		//展示数据
		$this->view->set('list', $list);
		$this->view->set('type', $type);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->output('office/sale/setting');
	}

	/**
	 *查询用户自定义的信息
	 *@param int $type
	 *@param int $perpage
	 *return array($total, $multi, $list)
	 */
	 protected function _type_search($type, $perpage = 12) {
			$list = array();
			$multi = null;
			$conds = array();
			//查询条件
			$conds = array('type = ?' => $type);
			//获取数据
			$serv = &service::factory('voa_s_oa_sale_type');
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
