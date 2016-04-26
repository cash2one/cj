<?php
/**
 * voa_uda_frontend_nvote_list
 * 投票调研-uda投票列表
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:18
 */

class voa_uda_frontend_nvote_list extends voa_uda_frontend_nvote_abstract {

    /**
     * @var array 检索条件
     */
    protected $_condi = array();

    public function __construct() {
        parent::__construct();
    }

    /**
     * 列表搜索
     * @param array $condi array(
     *                  'subject' => '投票主题',
     *                  'start_date' => '2015-03-15', 检索结束时间的开始日期
     *                  'end_date' => '2015-03-20',   检索结束时间的结束日期
     *                  'show_name' => 2匿名或者1实名,
     *                  'submit_uids' => array(用户id1, 用户id2, 用户id3),
     *                  'vote_status' => 1(进行中) 2已结束,
     *                  'vote_uids => array(用户id1, 用户id2))
     * @param array $result
     * @param int $page 页码
     * @return bool
     */
    public function search($condi, &$result, $page = 1) {

        //是否匿名
        if (!empty($condi['show_name']) &&
                ($condi['show_name'] == voa_d_oa_nvote::SHOW_NAME_YES ||
                $condi['show_name'] == voa_d_oa_nvote::SHOW_NAME_NO)) {
            $this->_condi['is_show_name =?'] = $condi['show_name'];
        }

        //发起人用户
        if (!empty($condi['submit_uids']) &&
                is_array($condi['submit_uids'])) {
            $this->_condi['submit_id IN (?)'] = $condi['submit_uids'];
        }

        //投票调研主题
        if (!empty($condi['subject'])) {
            $this->_condi['subject LIKE ?'] = '%' . $condi['subject'] . '%';
        }

        //结束时间范围起始日期
        $start_date = rstrtotime($condi['start_date']);

        //结束时间范围结束日期
        $end_date = rstrtotime($condi['end_date']);

        //投票状态
        if (!empty($condi['vote_status']) &&
                ($condi['vote_status'] == 1 ||
                $condi['vote_status'] == 2)) {
            $end_date = startup_env::get('timestamp');
        }

        //结束时间范围起始日期
        if ($start_date > 0) {
            $this->_condi['created >=?'] = $start_date;
        }
        //结束时间范围结束日期
        if ($end_date > 0) {
            if ($condi['vote_status'] == 1) {
                $this->_condi['end_time >?'] = $end_date;

            } elseif ($condi['vote_status'] == 2) {
                $this->_condi['end_time <=?'] = $end_date;
            } else {
                $this->_condi['end_time <=?'] = $end_date + 86399;
            }
        }
        //根据参与用户检索
        if (!empty($condi['vote_uids']) &&
                is_array($condi['vote_uids'])) {

            $vote_ids = $this->_get_vote_by_uids($condi['vote_uids']);
            if (!empty($vote_ids)) {
                $this->_condi['id IN (?)'] = $vote_ids;
            } else {
                $result['list'] = array();
                $result['count'] = 0;
                return true;
            }
        }

        //分页信息
        $perpage = 20;
        $start = ($page - 1) * $perpage;
        $orderby = array('id' => 'DESC');

        $result['list'] = $this->_serv->list_by_conds($this->_condi, array($start, $perpage), $orderby);
        if ($result['list']) {
            $result['count'] = $this->_serv->count_by_conds($this->_condi);
            $this->__format_list($result['list']);
        } else {
            $result['list'] = array();
            $result['count'] = 0;
        }

        return true;
    }

    /**
     * 得到投票id根据参与人id
     * @param $m_uids
     * @return array
     */
    protected function _get_vote_by_uids($m_uids) {

        $serv_mem = &service::factory('voa_s_oa_nvote_mem');
        $nvote_mems =  $serv_mem->list_by_conds(array('m_uid IN (?)' => $m_uids));
        $nvotes = array();
        if (!empty($nvote_mems)) {
            //二维数组提取投票id
            $nvotes = array_column($nvote_mems, 'nvote_id');
            $nvotes = array_merge($nvotes, $this->_get_vote_by_cdid($m_uids));
        } else {
            $nvotes = $this->_get_vote_by_cdid($m_uids);
        }
        return $nvotes;
    }

    /**
     * 得到投票id根据参与人部门
     * @param $m_uids
     * @return array
     */
    protected function _get_vote_by_cdid($m_uids) {
        //获取用户部门
        $serv_depart = &service::factory('voa_s_oa_member_department');
        $cd_ids = $serv_depart->fetch_all_by_uid($m_uids);
        if (!empty($cd_ids)) {
            //获取用户部门对应的投票id
            $serv_nd = &service::factory('voa_s_oa_nvote_department');
            $nvote_dps = $serv_nd->list_by_conds(array('cd_id IN (?)' => $cd_ids));

            if (!empty($nvote_dps)) {
                $nv_ids = array_column($nvote_dps, 'nvote_id');
                return $nv_ids;
            }
        }

        return array();
    }

    /**
     * 格式化数据
     * @param array $nvotes 投票信息
     */
    private function __format_list(&$nvotes) {
        foreach ($nvotes as &$vote) {
            $vote['_start_time'] = rgmdate($vote['created'], 'Y-m-d H:i');
            $vote['_end_time'] = rgmdate($vote['end_time'], 'Y-m-d H:i');
            if ($vote['end_time'] > startup_env::get('timestamp')) {
                $vote['_status'] = '进行中';
            } else {
                $vote['_status'] = '已结束';
            }

            $vote['_is_show_name'] = $vote['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES ? '实名投票' : '匿名投票';
        }
    }

}
