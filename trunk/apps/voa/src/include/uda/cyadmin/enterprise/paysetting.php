<?php

class voa_uda_cyadmin_enterprise_paysetting extends voa_uda_cyadmin_enterprise_base {


	function list_by_conds( $conds ) {
		$data = $this->serv_enterprise_appsetting->list_by_conds( $conds );
		if( ! $data ) {
			$this->errmsg = '查询数据失败！';
		}

		return $data;
	}

}
