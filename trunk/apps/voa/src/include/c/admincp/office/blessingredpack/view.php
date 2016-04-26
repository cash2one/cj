<?php
/**
 * view.php
 * 企业祝福红包详情
 * @author: anything
 * @createTime: 2015/11/21 19:13
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_c_admincp_office_blessingredpack_view extends voa_c_admincp_office_blessingredpack_base{

    public function execute()
    {
        $id = $this->request->get('id');

        $this->view->set('id', $id);
        $this->output('office/blessingredpack/view');
    }
}
