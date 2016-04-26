<?php
/**
 * voa_s_oa_nvote
 * 投票调研
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:39
 */

class voa_s_oa_nvote extends voa_s_abstract {

    public function __construct() {
        parent::__construct();
    }


    /**
     * 判断用户是否可以投票
     * @param $nv_id 投票id
     * @param $m_uid 用户id
     * @return int $is_can_vote 1=可投票，2=不可投票，3=已投票
     */
    public function is_can_vote($nv_id, $m_uid) {

        //受邀投票用户service
        $serv_vote_mem = &service::factory('voa_s_oa_nvote_mem');
        $vote_mem = $serv_vote_mem->get_by_conds(array('nvote_id' => $nv_id, 'm_uid' => $m_uid));

        //判断投票是否已结束或用户已投票
        $is_can_vote = 1;

        if (empty($vote_mem)) {
            $is_can_vote = 2; //不可投票

        } elseif ($vote_mem['is_nvote'] == voa_d_oa_nvote_mem::NVOTE_YES) {
            $is_can_vote = 3; //已经投过票
        }
        //用户不可投票判断部门
        if ($is_can_vote == 2) {
            $uda_mem_option = &uda::factory('voa_uda_frontend_nvote_mem_option_get');
            $mem_option = array();
            //检查是否有用户的投票记录
            if ($uda_mem_option->mem_option_list_by_uid($nv_id, $m_uid, $mem_option) == true && empty($mem_option)) {

                //获取用户部门
                $serv_depart = &service::factory('voa_s_oa_member_department');
                $cd_ids = $serv_depart->fetch_all_by_uid($m_uid);

                //判断用户所在的部门是否在受邀部门中
                if (!empty($cd_ids)) {

                    //获取用户部门对应的投票id
                    $serv_nd = &service::factory('voa_s_oa_nvote_department');
                    $nvote_dps = $serv_nd->list_by_conds(array('cd_id IN (?)' => $cd_ids, 'nvote_id =?' => $nv_id));

                    if (!empty($nvote_dps)) {
                        $is_can_vote = 1;
                    }
                }
            } else {
                //有投票记录则返回已投票
                $is_can_vote = 3;
            }
        }
        return $is_can_vote;
    }
}
