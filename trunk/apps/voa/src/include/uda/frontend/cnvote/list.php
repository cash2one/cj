<?php
/**
 * voa_uda_frontend_cnvote_list
 * 投票调研-uda投票列表
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:18
 */

class voa_uda_frontend_cnvote_list extends voa_uda_frontend_cnvote_abstract {

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

        // 投票调研主题
        if (!empty($condi['subject'])) {
            $this->_condi['subject LIKE ?'] = '%' . $condi['subject'] . '%';
        }

        // 投票发起人
        if (!empty($condi['m_username'])) {
        	$this->_condi['m_username LIKE ?'] = '%' . $condi['m_username'] . '%';
        }

        // 分类
        if (!empty($condi['category'])) {
        	$this->_condi['category'] = $condi['category'];
        }

        // 投票开始时间
        $start_date = rstrtotime($condi['start_date']);

        // 投票结束时间
        $end_date = rstrtotime($condi['end_date']);

        // 发布开始时间
        if (!empty($condi['c_start_date'])) {
        	$c_start_date = rstrtotime($condi['c_start_date']);
        }

        // 发布结束时间
        if (!empty($condi['c_end_date'])) {
        	$c_end_date = rstrtotime($condi['c_end_date']) + 86400;
        }

        //投票状态
        if (!empty($condi['vote_status']) &&
                ($condi['vote_status'] == 1 ||
                $condi['vote_status'] == 2)) {
            $end_date = startup_env::get('timestamp');
        }

        if (!empty($c_start_date)) {
        	$this->_condi['created >=?'] = $c_start_date;
        }

        if (!empty($c_end_date)) {
        	$this->_condi['created <=?'] = $c_end_date;
        }

        //结束时间范围起始日期
        if ($start_date > 0) {
            $this->_condi['end_time >=?'] = $start_date;
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

        //分页信息
        $perpage = empty($this->_sets['perpage']) ? 15 : $this->_sets['perpage'];
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

        $serv_mem = &service::factory('voa_s_oa_cnvote_mem');
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
            $serv_nd = &service::factory('voa_s_oa_cnvote_department');
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
        }
    }

}
