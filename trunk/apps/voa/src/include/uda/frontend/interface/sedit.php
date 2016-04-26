<?php
/**
 * voa_uda_frontend_interface_sedit
 * 接口测试－－编辑流程步骤
 * User: gaosong
 */

class voa_uda_frontend_interface_sedit extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 编辑
     * @param  $conds
     * @return boolean
     */
    public function edit($step) {
    	// 编辑
    	$t = new voa_d_oa_interface_step();
    	$t->update($step['s_id'],$step);
    	return true;
    }

}
