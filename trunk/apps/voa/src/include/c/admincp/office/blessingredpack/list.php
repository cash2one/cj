<?php
/**
 * list.php
 * 企业祝福红包 列表
 * @author: anything
 * @createTime: 2015/11/17 15:39
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

class voa_c_admincp_office_blessingredpack_list extends voa_c_admincp_office_blessingredpack_base {

    public function execute(){

        $this->view->set('pluginId', $this->_module_plugin_id);
        $viewUrl = $this->cpurl($this->_module, $this->_operation, 'view', $this->_module_plugin_id, array('id'=>''));
        $delUrl = $this->cpurl($this->_module, $this->_operation, 'delete', $this->_module_plugin_id, array('id'=>''));
        $editUrl = $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('id'=>''));
        $this->view->set('view_url_base', empty($viewUrl) ? '0' : $viewUrl);
        $this->view->set('del_url_base', empty($delUrl) ? '0' : $delUrl);
        $this->view->set('edit_url_base', empty($editUrl) ? '0' : $editUrl);
        $this->output('office/blessingredpack/list');
    }
}
