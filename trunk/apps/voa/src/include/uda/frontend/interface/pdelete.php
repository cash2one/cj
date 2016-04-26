<?php
/**
 * voa_uda_frontend_interface_pdelete
 * 接口参数－－删除
 * User: gaosong
 */

class voa_uda_frontend_interface_pdelete extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 删除
     * @param  $conds
     * @return boolean
     */
    public function delete($parameter) {
    	// 删除
    	$t = new voa_d_oa_interface_paramter();
    	$t->delete($parameter);
    	return true;
    }

}
