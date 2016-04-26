<?php
/**
 * voa_uda_frontend_sale_business
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_business extends voa_uda_frontend_base {

	//商机表
	protected $business;

	public function __construct() {
		parent::__construct();
		$this->business = &service::factory('voa_s_oa_sale_business');
	}

	/*
	 *商机详情
	 *@param array request
	 *@param array result
	 */
	public function doit(array $request, &$result) {

		$results = $this->business->get($request['bid']);
		if(!empty($results)) {
			$result = array(
				'title' => $results['title'],
				'type' => voa_d_oa_sale_business::$type[$results['type']],// $this->typename($results['type']),
				'amount' => $results['amount'],
				'content' => ($results['content']),
				'typeid' => ($results['type']),
				'scid' => $results['scid'],
				'updated' => rgmdate($results['updated'], 'Y-m-d'),
				'bid' => $results['bid']
			);
			return true;
		} else {
			return false;
		}
	}
	/*
	 *商机详情
	 *@param  type
	 *@return  typename
	 */
	private function typename($type){
		$typename = '';
		switch($type) {
			case 1:
				$typename = '初步沟通';
				break;
			case 2:
				$typename = '立项跟踪';
				break;
			case 3:
				$typename = '呈报方案';
				break;
			case 4:
				$typename = '商务谈判';
				break;
			case 5:
				$typename = '赢单';
				break;
			case 6:
				$typename = '输单';
				break;
		}
		return $typename;
	}
}
