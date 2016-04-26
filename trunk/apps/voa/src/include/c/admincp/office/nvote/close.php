<?php
/**
 * voa_c_admincp_office_nvote_close
 * 投票调研-关闭
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:46
 */

class voa_c_admincp_office_nvote_close extends voa_c_admincp_office_nvote_base {

    public function execute() {

        $id = $this->request->get('nv_id');
        $id = rintval($id, false);

        if (empty($id)) {
            $this->message('error', '请指定要关闭的投票调研');
        }

        $uda = &uda::factory('voa_uda_frontend_nvote_close');

        if (!$uda->close($this->_user['ca_id'], $id, false, $this->session)) {
            $this->message('error', $uda->error);
        }

        $this->message('success', '指定投票调研关闭成功', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id), false);
    }
}
