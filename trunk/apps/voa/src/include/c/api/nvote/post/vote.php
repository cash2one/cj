<?php
/**
 * voa_c_api_nvote_post_vote
 * User: luckwang
 * Date: 15/3/12
 * Time: 下午3:11
 */

class voa_c_api_nvote_post_vote extends voa_c_api_nvote_base {

    public function execute() {

        /*需要的参数*/
        $fields = array(
            'nvo_ids' => array('type' => 'string_trim', 'required' => true),	//投票主题
        );
        /*基本验证检查*/
        if (!$this->_check_params($fields)) {
            return false;
        }
        /*投票选项检查*/
        if (empty($this->_params['nvo_ids'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::OPTION_ID_NULL);
        }

        $uda = &uda::factory('voa_uda_frontend_nvote_mem_option_add');

        /*入库操作*/
        if (!$this->vote(explode(',',  $this->_params['nvo_ids']), startup_env::get('wbs_uid'))) {
            $this->_errcode = $uda->errcode;
            $this->_errmsg = $uda->errmsg;
            return false;
        }

        return true;
    }
}
