<?php
/**
 * voa_uda_frontend_interface_edit
 * 接口测试－－编辑接口
 * User: gaosong
 */

class voa_uda_frontend_interface_edit extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 编辑
     * @param  $conds
     * @return boolean
     */
    public function edit($conds) {
    	// 编辑
    	$t = new voa_d_oa_interface();
    	$t->update($conds['n_id'],$conds);
    	return true;
    }

}
