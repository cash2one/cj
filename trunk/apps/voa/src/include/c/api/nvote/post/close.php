<?php
/**
 * voa_c_api_nvote_post_close
 * 关闭投票
 * User: luckwang
 * Date: 15/3/11
 * Time: 下午3:09
 */

class voa_c_api_nvote_post_close extends voa_c_api_nvote_base {

    public function execute() {

        /*需要的参数*/
        $fields = array(
            'nv_id' => array('type' => 'int', 'required' => true),	//投票主题
        );
        /*基本验证检查*/
        if (!$this->_check_params($fields)) {
            return false;
        }
        /*投票选项检查*/
        if (empty($this->_params['nv_id'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::NV_ID_NULL);
        }

        $uda = &uda::factory('voa_uda_frontend_nvote_close');
        /*入库操作*/
        if (!$this->close(startup_env::get('wbs_uid'), $this->_params['nv_id'], true)) {
            $this->_errcode = $uda->errcode;
            $this->_errmsg = $uda->errmsg;
            return false;
        }

        return true;
    }
}
