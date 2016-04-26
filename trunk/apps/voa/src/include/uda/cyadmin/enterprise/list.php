<?php

/**


 * Created Time: 2015/5/14  0:20
 */
class voa_uda_cyadmin_enterprise_list extends voa_uda_cyadmin_base {

	public function gettotal($in,&$out) {
		$service = &service::factory('voa_s_cyadmin_enterprise_account');
		$ser_com = &service::factory('voa_s_cyadmin_enterprise_company');
		$acids = array();
		foreach($in as $_val){
			$acids[] = $_val['acid'];
		}
		//$conds['acid IN ?'] = $acids;
		//$service->fetch_by_conds($conds);
		
		//$colist = $ser_com->list_all();
		//var_dump($ser_com->list_all());
		//exit;
		return true;
	}


}
