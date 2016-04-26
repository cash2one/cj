<?php
/**
 * voa_uda_frontend_interface_sadd
 * 接口测试－－添加接口步骤
 * User: gaosong
 */

class voa_uda_frontend_interface_sadd extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 新增
     * @param  $conds
     * @return boolean
     */
    public function add($conds) {
    	// 新增
    	$t = new voa_d_oa_interface_step();
    	$t->insert($conds);
    	return true;
    }

}
