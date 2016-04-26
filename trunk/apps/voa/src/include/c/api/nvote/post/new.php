<?php
/**
 * voa_c_api_nvote_post_new
 * 添加投票
 * User: luckwang
 * Date: 15/3/11
 * Time: 下午3:08
 */


class voa_c_api_nvote_post_new extends voa_c_api_nvote_base {

    public function execute() {

        /*需要的参数*/
        $fields = array(
            'subject' => array('type' => 'string_trim', 'required' => true),	//投票主题
            'endtime' => array('type' => 'string_trim', 'required' => true),	//结束时间
            'is_single' => array('type' => 'int', 'required' => true),			//是否单选
            'is_show_name' => array('type' => 'int', 'required' => true),	//是否实名
            'is_show_result' => array('type' => 'int', 'required' => true),	//是否显示结果
            'at_id' => array('type' => 'int', 'required' => false),	//附件
            'receive_uids' => array('type' => 'string_trim', 'required' => false),// 接收投票人id
            'cd_ids' => array('type' => 'string_trim', 'required' => false),// 接收投票人id
            'options' => array('type' => 'array', 'required' => true),// 投票选项
        );
        /*基本验证检查*/
        if (!$this->_check_params($fields)) {
            return false;
        }
        //检查参数是否为空
        if (!$this->__check_params()) {
            return false;
        }

        /*入库操作*/
        if (!$this->_add()) {
            return false;
        }

        $this->_result = array(
            'nv_id' => $this->_return['id']
        );
        return true;
    }

    /*
     * 入库
     * @return boolen 新增成功
    */
    protected function _add()
    {

        $uda = &uda::factory('voa_uda_frontend_nvote_add');
        //投票信息
        $vote = array(
            'subject' => $this->_params['subject'],
            'endtime' => $this->_params['endtime'],
            'is_single' => $this->_params['is_single'],
            'is_show_name' => $this->_params['is_show_name'],
            'is_show_result' => $this->_params['is_show_result'],
            'at_id' => $this->_params['at_id'],
            'submit_id' => startup_env::get('wbs_uid'),
            'submit_ca_id' => 0
        );

        //投票参与人
        $m_uids = array();
        if (!empty($this->_params['receive_uids'])) {
            $m_uids = explode(',', $this->_params['receive_uids']);
        }

        $cd_ids = array();
        if (!empty($this->_params['cd_ids'])) {
            $cd_ids = explode(',', $this->_params['cd_ids']);
        }
        //投票选项
        $options = $this->_params['options'];
        //uda添加投票
        if (!$uda->add($vote, $m_uids, $cd_ids, $options)) {
            $this->_errcode = $uda->errcode;
            $this->_errmsg = $uda->errmsg;
            return false;
        }

        $this->_return = $vote;

        return true;
    }

    /**
     * 检查请求参数
     * @return bool|void
     */
    private function __check_params() {
        /*投票主题检查*/
        if (empty($this->_params['subject'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::SUBJECT_NULL);
        }
        /*投票结束时间检查*/
        if (empty($this->_params['endtime'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::ENDTIME_NULL);
        }
        /*投票类型检查*/
        if (empty($this->_params['is_single'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::IS_SINGLE_NULL);
        }
        /*投票方式检查*/
        if (empty($this->_params['is_show_name'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::IS_SHOW_NAME_NULL);
        }
        /*投票显示结果检查*/
        if (empty($this->_params['is_show_result'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::IS_SHOW_RESULT_NULL);
        }
        /*邀请投票人员检查*/
        if (empty($this->_params['receive_uids'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::RECEIVE_UID_NULL);
        }
        /*投票选项检查*/
        if (empty($this->_params['options'])) {
            return $this->_set_errcode(voa_errcode_api_nvote::OPTIONS_NULL);
        }
        return true;
    }
}
