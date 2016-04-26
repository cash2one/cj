<?php
/**
 * voa_uda_frontend_sale_coustmer_list
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_trajectory_list extends voa_uda_frontend_base {

	//客户表
	protected $trajectory;
	protected $coustmer;
	public function __construct() {
		parent::__construct();
		$this->trajectory = &service::factory('voa_s_oa_sale_trajectory');
		$this->coustmer = &service::factory('voa_s_oa_sale_coustmer');
	}
	public function doit(array $request, &$result) {

		$page_option[0] = $request['start'];
		$page_option[1] = $request['limit'];
		$orderby['updated'] = 'DESC';
		
		$conds = array();
		
		if(!empty($request['scid'])) {
			$conds['scid'] = $request['scid'];
		}
		if(!empty($request['source'])) {
			$conds['source'] = $request['source'];
		}
		if(!empty($request['stid'])) {
			$conds['stid'] = $request['stid'];
		}

		if(!empty($request['m_uid']) && $request['m_uid'] != -1) {	
			//判断传过来的uid是否在权限下属里
			if(!in_array($request['m_uid'],$request['uids'])){
					return $this->set_errmsg(voa_errcode_api_sale::NO_PERMISSIONS);
			}	
			$conds['m_uid'] = $request['m_uid'];
		}else{
			$conds['m_uid IN (?)'] = $request['uids'];
			}
		$result = $this->trajectory->list_by_conds($conds, $page_option, $orderby);
	
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
			$coustmer = $this->coustmer->get($v['scid']);
			$user = voa_h_user::get($v['m_uid']);
			//获取图片
			$at_ids = explode(',',$v['at_ids']);
			$img = array();
			if(!empty($at_ids)) {
				foreach($at_ids as $val) {
					if($val) {
						$img[] = array(
							'aid' => $val,
							'_thumb' => voa_h_attach::attachment_url($val, 45),
							'_big' => voa_h_attach::attachment_url($val, 0)
						);
						
						
					}
				}
			}

			//取出有用的数据
			$data[] = array(
							'companyshortname' => rhtmlspecialchars($coustmer['companyshortname']),
							'company' => rhtmlspecialchars($coustmer['company']),
							'name' => !empty($user['m_username']) ? rhtmlspecialchars($user['m_username']) : '',
							'source' => rhtmlspecialchars($v['type']),
							'content' => rhtmlspecialchars($v['content']),
							'address' => rhtmlspecialchars($v['present_address']),
							'strid' => $v['strid'],
							'scid' => $v['scid'],
							'm_uid' => $v['m_uid'],
							'color' => $v['color'],
							'image' => $img,
                            'time' => rgmdate($v['created'])
						);
		}
		return true;
	 }


}
