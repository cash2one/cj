<?php
/**
 * voa_uda_frontend_sale_coustmer_list
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_coustmer_list extends voa_uda_frontend_base {

	//客户表
	protected $coustmer;
	
	public function __construct() {
		parent::__construct();
		$this->coustmer = &service::factory('voa_s_oa_sale_coustmer');
	}
	
	/*
	 *查询
	 *@param array request
	 *@param array result
	 */
	public function doit(array $request, &$result) {

		$page_option[0] = $request['start'];
		$page_option[1] = $request['limit'];
		$orderby['updated'] = 'DESC';
		
		$conds = array();
		
		if(!empty($request['source'])) {
			$conds['source_stid'] = $request['source'];
		}
		
		if(!empty($request['status'])) {
			$conds['type_stid'] = $request['status'];
		}
		if(!empty($request['cm_uid'])) {
			$conds['cm_uid'] = $request['cm_uid'];
		}

		$result = $this->coustmer->list_by_conds($conds, $page_option, $orderby);
		
		return true;
	}
	
	/*
	 *整理数据
	 *@param array list
	 *@return array data
	 */
	 public function listformat ($list, &$data) {
		if(empty($list)) {
			return false;
		}
		foreach ($list as $v) {
			//销售人员名字
			$user = voa_h_user::get($v['cm_uid']);
			//取出有用的数据
			$data[] = array(
							'scid' => $v['scid'],
							'company' => rhtmlspecialchars($v['companyshortname']),
							'color' => $v['color'],
							'ctype' => rhtmlspecialchars($v['type']),
							'name' => !empty($user['m_username']) ? rhtmlspecialchars($user['m_username']) : '',
							'updated' => rgmdate($v['updated'], "Y-m-d H:i")
						);
		}
		return true;
	 }


}
