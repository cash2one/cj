<?php
/**
 * add.php
 * 添加活动红包
 * @author: anything
 * @createTime: 2015/11/18 17:47
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_c_admincp_office_blessingredpack_add extends voa_c_admincp_office_blessingredpack_base{

    public function execute() {



        $this->view->set('pluginId', $this->_module_plugin_id);
        $this->view->set('actionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));

        $this->output('office/blessingredpack/add');
    }
}
