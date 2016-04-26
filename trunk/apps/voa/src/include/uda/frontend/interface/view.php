<?php
/**
 * voa_uda_frontend_interface_view
 * 接口测试－－详情接口
 * User: gaosong
 */

class voa_uda_frontend_interface_view extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 接口详情
     * @param 接口id $n_id
     * @param 返回详情 $data
     * @return boolean
     */
    public function get_info($n_id, &$data) {

    	$t = new voa_d_oa_interface();
    	// 如果数据不存在
    	if (!$data = $t->get_by_conds(array('n_id' => $n_id))) {
    		$this->set_errmsg(voa_errcode_oa_goods::INTERFACE_DATA_IS_NOT_EXIST);
    		return false;
    	}
    	return true;
    }

}
