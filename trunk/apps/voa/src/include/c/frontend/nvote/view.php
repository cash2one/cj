<?php
/**
 * voa_c_frontend_nvote_view
 * User: luckwang
 * Date: 15/3/17
 * Time: 下午7:26
 */

class voa_c_frontend_nvote_view extends voa_c_frontend_nvote_base {

    public function execute()
    {
        /** 投票调研id */
        $nv_id = rintval($this->request->get('nv_id'));

        $this->__get_detail($nv_id);

        $this->_output('mobile/nvote/view');
    }

    /**
     * @return bool|void
     */
    private function __get_detail($nv_id) {

        $uda = &uda::factory('voa_uda_frontend_nvote_get');
        $nvote = array();
        if (!$uda->get_vote($nv_id, $nvote)) {
            $this->_error_message('nvote_empty', get_referer('/frontend/nvote/list'));
        }
        unset($uda);
        $this->__format($nvote);

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
        //$serv_vote_mem = &service::factory('voa_s_oa_nvote_mem');
        //$vote_mem = $serv_vote_mem->get_by_conds(array('nvote_id' => $nvote['id'], 'm_uid' => startup_env::get('wbs_uid')));

        $data['nv_id'] = $nvote['id'];
        $data['subject'] = $nvote['subject'];
        $data['m_username'] = $user['m_username'];
        $data['is_show_name'] = $nvote['is_show_name'];
        $data['_is_show_name'] = $nvote['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES ? '实名投票' : '匿名投票';
        $data['is_show_result'] = $nvote['is_show_result'];
        $data['is_single'] = $nvote['is_single'];
        $data['start_time'] = rgmdate($nvote['created'], 'Y-m-d H:i');
        $data['end_time'] = rgmdate($nvote['end_time'], 'Y-m-d H:i');
	    $data['close_status'] = $nvote['close_status'];

        //是否可以关闭投票
        $data['is_can_close'] = $nvote['submit_id'] == startup_env::get('wbs_uid') && $nvote['end_time'] > startup_env::get('timestamp') ? 1 : 2;

        //是否可以投票
        $serv_nvote = &service::factory('voa_s_oa_nvote');
        $data['is_can_vote'] = $serv_nvote->is_can_vote($nvote['id'], startup_env::get('wbs_uid'));
        $data['is_can_vote'] = $nvote['end_time'] > startup_env::get('timestamp')  && $data['is_can_vote'] != 2 ? $data['is_can_vote'] : 2;

	    // 是否结束 1-未结束 0-已结束
	    $data['is_end'] = $nvote['end_time'] > startup_env::get('timestamp') ? 1 : 0;

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
            $op['at_id'] = $option['at_id'];
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

            $this->view->set('mem_options', $mem_options);
            unset($uda);
        }

	    /* 如果存在封面图片 则优先使用封面图片 */
	    if (!empty($data['attachment'])) {
		    $icon_url = $data['attachment'];
	    } else {
		    $icon_url = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		    $icon_url .= $this->_setting['domain'] . '/admincp/static/images/application/nvote.png';
	    }

	    // 分享数据
	    $share_data = array(
		    'title' => '投票调研', // 分享标题
		    'desc' => rsubstr(strip_tags($data['subject']), 70, ' ...'), // 分享描述
		    'imgUrl' => $icon_url, // 分享图标
	    );

		unset($nvote, $servm, $serv_vote_mem);
	    $this->view->set('share_data',  $share_data);
		$this->view->set('data', $data);
    }
}
