<?php
/**
 * voa_uda_frontend_interface_sdelete
 * 流程步骤－－删除
 * User: gaosong
 */

class voa_uda_frontend_interface_sdelete extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 删除
     * @param  $conds
     * @return boolean
     */
    public function delete($step) {
    	// 删除
    	$t = new voa_d_oa_interface_step();
    	$t->delete($step);
    	return true;
    }

}
