<?php

/**
 * voa_uda_cyadmin_enterprise_setting
 * 应用设置UDA
 * Created by zhoutao.
 * Created Time: 2015/7/27  17:20
 */
class voa_uda_cyadmin_enterprise_appsetting extends voa_uda_cyadmin_enterprise_base {

	/**
	 * 获取
	 * @return mixed
	 */
	function get_all() {
		$all = $this->serv_enterprise_appsetting->list_all();

		return $all;
	}


	function list_by_conds( $conds ) {
		$data = $this->serv_enterprise_appsetting->list_by_conds( $conds );
		if( ! $data ) {
			$this->errmsg = '查询数据失败！';
		}

		return $data;
	}

}
