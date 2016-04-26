<?php
/**
 * voa_uda_frontend_sale_business_list
 * 应用uda
 * Create By tim_zhang
 * $Author$
 * $Id$
 */
class voa_uda_frontend_sale_business_list extends voa_uda_frontend_base {

	//客户表
	protected $business;
	protected $coustmer;
	
	public function __construct() {
		parent::__construct();
		$this->business = &service::factory('voa_s_oa_sale_business');
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
		
		if(!empty($request['time'])) {
			$year = date("Y");
			$month = date("m");
			switch ($request['time']) {
				case 1://本周
					$strat_time=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
					$end_time=mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
				break;
				case 2://本月
					$strat_time=mktime(0,0,0,date('m'),1,date('Y'));
					$end_time=mktime(23,59,59,date('m'),date('t'),date('Y'));
				break;
				case 3://本季度
					$Q = ceil($month/3);
					if($Q == 1) {
						$strat_time = strtotime($year."-01-01");
						$end_time = strtotime($year."-03-31");
					}
					if($Q == 2) {
						$strat_time = strtotime($year."-04-01");
						$end_time = strtotime($year."-06-30");
					}
					if($Q == 3) {
						$strat_time = strtotime($year."-07-01");
						$end_time = strtotime($year."-09-30");
					}
					if($Q == 4) {
						$strat_time = strtotime($year."-10-01");
						$end_time = strtotime($year."-12-31");
					}
				break;
				case 4://本年
					$strat_time = strtotime($year."-01-01");
					$end_time = strtotime($year."-12-31");
				break;
				
			}
			$conds['created <= ?'] = $end_time;
			$conds['created >= ?'] = $strat_time;
		}
		
		if(!empty($request['type'])) {
			$conds['type'] = $request['type'];
		}
		
		$result = $this->business->list_by_conds($conds, $page_option, $orderby);
		
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
			$coustmer = $this->coustmer->get($v['scid']);
			//取出有用的数据
			$user = voa_h_user::get($v['m_uid']);
			$data[] = array(
							'bid' => $v['bid'],
							'companyshortname' => rhtmlspecialchars($coustmer['companyshortname']),
							'title' => rhtmlspecialchars($v['title']),
							'name' => rhtmlspecialchars($user['m_username']),
							'amount' => $v['amount'],
							'updated' => rgmdate($v['updated'], 'Y-m-d H:i')
						);
		}
		return true;
	 }


}
