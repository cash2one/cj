<?php
/**
 * Created by PhpStorm.
 * User: zhonglei
 * Date: 2016/3/21
 * Time: 16:45
 */

class voa_c_admincp_office_questionnaire_addedit extends voa_c_admincp_office_questionnaire_base {

    protected function execute() {
        $qu_id = 0;
        $copy = 0;
        $setting = voa_h_cache::get_instance()->get("setting", "oa");
        
        $getx = $this->request->getx();
        if (isset($getx['qu_id']) && is_numeric($getx['qu_id'])) {
            $qu_id = $getx['qu_id'];
            
            if (isset($getx['c']) && $getx['c'] == 1) {
                $copy = 1;
            }
        }
        
        $this->view->set('qu_id', $qu_id);
        $this->view->set('copy', $copy);
        $this->view->set('share_id', md5(uniqid()));
        $this->view->set('share_url', config::get('voa.oa_http_scheme') . $setting['domain'] . '/newh5/questionnaire/index.html?#/app/page/questionnaire/questionnaire-form?share_id=');
        $this->view->set('list_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
        $this->output('office/questionnaire/addedit');
    }
}
