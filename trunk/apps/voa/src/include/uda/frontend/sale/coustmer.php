<?php
/**
 * voa_uda_frontend_sale_coustmer_list
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_coustmer extends voa_uda_frontend_base {

	//客户表
	protected $coustmer;
	
	public function __construct() {
		parent::__construct();
		$this->coustmer = &service::factory('voa_s_oa_sale_coustmer');
	}
	
	/*
	 *客户详情
	 *@param array request
	 *@param array result
	 */
	public function doit(array $request, &$result) {
		
		$results = $this->coustmer->get($request['scid']);
		if(!empty($results)) {
			$result = array(
							'scid' => $results['scid'],
							'companyshortname' => ($results['companyshortname']),
							'company' => ($results['company']),
							'address' => ($results['address']),
							'source' => ($results['source']),
							'name' => ($results['name']),
							'phone' => $results['phone'],
							'm_uid' => $results['cm_uid'],
							'sfields' => json_decode($results['sfields'], true),
						);
			return true;
		} else {
			return false;
		}
	}

}
