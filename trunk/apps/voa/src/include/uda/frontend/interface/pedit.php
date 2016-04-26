<?php
/**
 * voa_uda_frontend_interface_pedit
 * 接口参数－－编辑接口
 * User: gaosong
 */

class voa_uda_frontend_interface_pedit extends voa_uda_frontend_interface_abstract {

    public function __construct() {
        parent::__construct();

    }

    /**
     * 编辑
     * @param  $conds
     * @return boolean
     */
    public function edit($parameter) {
    	// 编辑
    	$t = new voa_d_oa_interface_paramter();
    	$t->update($parameter['p_id'],$parameter);
    	return true;
    }

}
