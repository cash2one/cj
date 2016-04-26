<?php
/**
 * voa_c_api_nvote_get_list
 * 投票列表
 * User: luckwang
 * Date: 15/3/11
 * Time: 下午3:08
 */

class voa_c_api_nvote_get_list extends voa_c_api_nvote_base {

    public function execute() {

        /*需要的参数*/
        $fields = array (
            'page' => array('type' => 'int', 'required' => false),	//投票主题
            'action' => array('type' => 'string_trim', 'required' => false),
            'vote_status' => array('type' => 'int', 'required' => false)
        );
        /*基本验证检查*/
        if (!$this->_check_params($fields)) {
            return false;
        }
        /*检查页码*/
        if (empty($this->_params['page'])) {
            $this->_params['page'] = 1;
        }

        /*检查动作*/
        if (empty($this->_params['action'])) {
            $this->_params['action'] = 'my';
        }

        return $this->__list();
    }

    private function __list() {

        $condi = array(
            'subject' => '', //活动名称
            'vote_status' => 0, //活动状态
            'start_date' => '', //开始时间
            'end_date' => '',  //结束时间
            'show_name' => 0, //是否实名投票
            'submit_uids' => array(), //发起人
        );

        //选择查询我创建的或我接受的投票列表
        switch ($this->_params['action']) {
            //我创建的
            case 'my':
                $condi['submit_uids'] = array(startup_env::get('wbs_uid'));
                break;
            //我接受的
            case 'receive':
                $condi['vote_uids'] = array(startup_env::get('wbs_uid'));
                $condi['vote_status'] = $this->_params['vote_status'];
                break;
            default:
                return $this->_set_errcode(voa_errcode_api_nvote::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
                break;
        }
        //查询投票列表
        $result = array();
        $uda = &uda::factory('voa_uda_frontend_nvote_list');
        if (!$uda->search($condi, $result, $this->_params['page'])) {
            $this->_errcode = $uda->errcode;
            $this->_errmsg = $uda->errmsg;
            return false;
        }
        if ($result['list']) {
            //获取发起人名称
            $usernames = $this->__get_usernames($result['list']);
            $usernames[0] = '后台管理员';
        }
        //组织输出列表数据
        $data = array();
        foreach ($result['list'] as $res) {
            $da['nv_id'] = $res['id'];
            $da['subject'] =rsubstr(rhtmlspecialchars($res['subject']), 26);
            $da['created'] = rgmdate($res['created']);
            $da['is_show_name'] = $res['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES ? '实名投票' : '匿名投票';

            $da['nv_status'] = $res['end_time'] < startup_env::get('timestamp') ? '已结束' : '进行中';

            $da['m_username'] = $usernames[$res['submit_id']];

            $data[] = $da;

        }
        //输出结果
        $this->_result = array (
            'total' => $result['count'],
            'limit' => $this->_vote_settings['perpage'],
            'page' => $this->_params['page'],
            'data' => $data
        );

        return true;
    }


    /**
     * 获取发起人名称
     * @param $list
     * @return array
     */
    private function __get_usernames($list) {
        //发起人是自己
        if ($this->_params['action'] == 'my') {
            return array(startup_env::get('wbs_uid') => startup_env::get('wbs_username'));
        } else {
            //获取发起人名称
            $uids = array_column($list, 'submit_id');
            $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
            $users = $servm->fetch_all_by_ids($uids);
            return array_column($users, 'm_username','m_uid');
        }
    }

}
