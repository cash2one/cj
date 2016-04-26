<?php
/**
 * setting.php
 * 红包配置
 * @author: anything
 * @createTime: 2015/11/23 20:44
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_c_admincp_office_blessingredpack_setting extends voa_c_admincp_office_blessingredpack_base{

    public function execute() {

        $this->view->set('pluginId', $this->_module_plugin_id);
        $this->output('office/blessingredpack/setting');
    }
}
