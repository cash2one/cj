<?php

/**
 * voa_uda_frontend_cnvote_mem_option_get
 * 获取用户投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 下午4:55
 */

class voa_uda_frontend_cnvote_mem_option_get extends voa_uda_frontend_cnvote_mem_option_abstract {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取用户投票
     * @param $nvote_id 投票id
     * @param $result 输出结果
     * @return bool
     */
    public function mem_option_list($conds, &$result) {
        $nvote_id = rintval($nvote_id);
        if ($nvote_id < 1) {
            return false;
        }

        $result = $this->_serv->list_by_conds($conds);
        if (!$result) {
            $result = array();
        }
        return true;
    }

    /**
     * 分页查询用户选项
     * @param $nvote_id 投票id
     * @param $result  结果集&引用
     * @param array $limit 限制数
     * @return bool
     */
    public function limit_mem_options($nvote_id, &$result, $limit) {
        $nvote_id = rintval($nvote_id);
        if ($nvote_id < 1) {
            return false;
        }

        $result = $this->_serv->get_uid_list($nvote_id, $limit);
        if ($result['list']) {

            $uids = array_column($result['list'], 'm_uid');


            $result['list'] = $this->_serv->list_by_conds(array('m_uid IN (?)' => $uids, 'nvote_id =?' => $nvote_id));
        }

        return true;
    }


    /**
     * 获取投票用户
     * @param $list
     * @return array
     */
    private function __get_usernames($list) {

        $uids = array_column($list, 'm_uid');
        $servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
        $users = $servm->fetch_all_by_ids($uids);
        return array_column($users, 'm_username','m_uid');
    }

    /**
     * 获取用户选项根据用户id
     * @param $nvote_id 投票id
     * @param $m_uid  用户id
     * @param $result &结果
     * @return bool
     */
    public function mem_option_list_by_uid($nvote_id, $m_uid, &$result) {
        $nvote_id = rintval($nvote_id);
        $m_uid = rintval($m_uid);

        if ($nvote_id < 1 || $m_uid < 1){
            return false;
        }

        $result = $this->_serv->list_by_conds(array('nvote_id =?' => $nvote_id, 'm_uid =?' => $m_uid));
        if (!$result) {
            $result = array();
        }
        return true;
    }
}
