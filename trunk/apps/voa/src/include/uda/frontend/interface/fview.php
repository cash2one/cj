<?php
/**
 * voa_uda_frontend_interface_fview
 * 接口测试－－流程详情
 * User: gaosong
 */

class voa_uda_frontend_interface_fview extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 流程详情
     * @param 接口id $f_id
     * @param 返回详情 $data
     * @return boolean
     */
    public function get_info($f_id, &$data) {

    	$t = new voa_d_oa_interface_flow();
    	// 如果数据不存在
    	if (!$data = $t->get_by_conds(array('f_id' => $f_id))) {
    		$this->set_errmsg(voa_errcode_oa_interface::INTERFACE_DATA_IS_NOT_EXIST);
    		return false;
    	}
    	return true;
    }

}
