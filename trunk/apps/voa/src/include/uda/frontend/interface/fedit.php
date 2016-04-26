<?php
/**
 * voa_uda_frontend_interface_fedit
 * 接口测试－－编辑流程
 * User: gaosong
 */

class voa_uda_frontend_interface_fedit extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 编辑
     * @param  $conds
     * @return boolean
     */
    public function edit($flow) {
    	// 编辑
		$t = new voa_d_oa_interface_flow();
    	$t->update($flow['f_id'],$flow);
    	return true;
    }
}
