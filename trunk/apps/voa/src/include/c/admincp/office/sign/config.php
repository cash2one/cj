<?php
/**
 * config.php
 * 考勤 设置
 * @author: anything
 * @createTime: 2016/2/22 15:36
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_c_admincp_office_sign_config extends voa_c_admincp_office_sign_base {

    public function execute() {
        $this->view->set('pluginId', $this->_module_plugin_id);
        $this->output('office/sign/config');
    }
}