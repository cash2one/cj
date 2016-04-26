<?php
/**
 * voa_c_api_nvote_get_view
 * 浏览投票
 * User: luckwang
 * Date: 15/3/11
 * Time: 下午3:08
 */


class voa_c_api_nvote_get_view extends voa_c_api_nvote_base {

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

        return $this->__get_detail();
    }

    /**
     * @return bool|void
     */
    private function __get_detail() {

        $uda = &uda::factory('voa_uda_frontend_nvote_get');
        $nvote = array();
        if (!$uda->get_vote($this->_params['nv_id'], $nvote)) {
            return $this->_set_errcode(voa_errcode_api_nvote::NV_ID_NULL);
        }
        unset($uda);
        $this->_result = $this->__format($nvote);

        return true;
    }

    /**
     * 组织投票信息输出
     * @param $nvote
     */
    private function __format($nvote) {

        if ($nvote['submit_id'] < 1){
            $user['m_username'] = '后台管理员';
        } else {
            //用户service
            $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
            $user = $servm->fetch($nvote['submit_id']);
        }

        //受邀投票用户service
        $serv_vote_mem = &service::factory('voa_s_oa_nvote_mem');
        $vote_mem = $serv_vote_mem->get_by_conds(array('nvote_id' => $nvote['id'], 'm_uid' => startup_env::get('wbs_uid')));

        $data['nv_id'] = $nvote['id'];
        $data['subject'] = $nvote['subject'];
        $data['m_username'] = $user['m_username'];
        $data['is_show_name'] = $nvote['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES ? '实名投票' : '匿名投票';
        $data['is_show_result'] = $nvote['is_show_result'];
        $data['is_single'] = $nvote['is_single'];
        $data['start_time'] = rgmdate($nvote['created'], 'Y-m-d H:i');
        $data['end_time'] = rgmdate($nvote['end_time'], 'Y-m-d H:i');


        //是否可以关闭投票
        $data['is_can_close'] = $nvote['submit_id'] == startup_env::get('wbs_uid') && $nvote['end_time'] > startup_env::get('timestamp') ? 1 : 2;

        //是否可以投票
        $serv_nvote = &service::factory('voa_s_oa_nvote');
        $data['is_can_vote'] = $serv_nvote->is_can_vote($nvote['id'], startup_env::get('wbs_uid'));
        $data['is_can_vote'] = $nvote['end_time'] > startup_env::get('timestamp')  && $data['is_can_vote'] != 2 ? $data['is_can_vote'] : 2;

        if ($data['is_can_vote'] == 3) {
            if ($nvote['is_repeat'] == voa_d_oa_nvote::REPEAT_YES) {
                $data['is_can_vote'] = 4;
            } else {
                $data['is_can_vote'] = 2;
            }
        }

        $data['attachment'] = $nvote['attachment'];
        $data['at_id'] = $nvote['at_id'];
        //已投票的人数
        $data['count_mem'] = $nvote['voted_mem_count'];
        $data['count_nvotes'] = 0;
        //投票选项
        foreach ($nvote['options'] as $option) {
            $op = array();

            $op['nvo_id'] = $option['id'];
            $op['option'] = $option['option'];
            $op['nvotes'] = $nvote['is_show_result'] == voa_d_oa_nvote::SHOW_RESULT_YES ? $option['nvotes'] : 0;
            $op['attachment'] = $option['attachment'];
            $data['count_nvotes'] += $option['nvotes'];
            $data['options'][] = $op;
        }

        //不等于未投票
        if ($data['is_can_vote'] != 1) {

            $uda = &uda::factory('voa_uda_frontend_nvote_mem_option_get');
            $mem_options = array();
            if ($uda->mem_option_list_by_uid($nvote['id'], startup_env::get('wbs_uid'), $mem_options)) {
                $mem_options = array_column($mem_options, 'm_uid', 'nvote_option_id');
            }
            $data['mem_options'] = $mem_options;
            unset($uda);
        }

        unset($nvote, $servm, $serv_vote_mem, $vote_mem);
        return $data;
        //$this->view->set('data', $data);
    }
}
