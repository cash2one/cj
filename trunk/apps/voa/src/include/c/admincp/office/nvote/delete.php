<?php
/**
 * voa_c_admincp_office_nvote_delete
 * 投票调研-删除
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:46
 */

class voa_c_admincp_office_nvote_delete extends voa_c_admincp_office_nvote_base {

    public function execute() {

        $id = $this->request->get('nv_id');
        $id = rintval($id, false);
        $ids = $this->request->post('delete');
        if ($id) {
            $ids = array($id);
        }

        if (empty($ids)) {
            $this->message('error', '请指定要删除的投票调研');
        }

        $uda = &uda::factory('voa_uda_frontend_nvote_delete');
        if (!$uda->delete($ids)) {
            $this->message('error', $uda->error);
        }

        $this->message('success', '指定投票调研删除操作完毕', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
    }
}
