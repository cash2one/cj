<?php
/**
 * voa_uda_frontend_interface_add
 * 接口测试－－添加接口
 * User: gaosong
 */

class voa_uda_frontend_interface_add extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 新增
     * @param  $conds
     * @return boolean
     */
    public function add($conds,&$data) {
    	// 新增
    	$t = new voa_d_oa_interface();
    	$data = $t->insert($conds);
    	return true;
    }

}
