<?php
/**
 * voa_d_oa_nvote_mem_option
 * 投票调研-用户选项
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午10:30
 */

class voa_d_oa_nvote_mem_option extends voa_d_abstruct {

    // 初始化
    public function __construct() {

        // 表名
        $this->_table = 'orm_oa.nvote_mem_option';
        // 允许的字段
        $this->_allowed_fields = array();
        // 必须的字段
        $this->_required_fields = array();
        // 主键
        $this->_pk = 'id';
        // 字段前缀
        $this->_prefield = '';

        parent::__construct();
    }

    /**
     * 获取投票的用户
     * @param $nvote_id
     * @return array
     */
    public function get_vote_mems($nvote_id) {
        $nvote_id = rintval($nvote_id);
        if ($nvote_id < 1) {
            return array();
        }

        $sql = 'SELECT `m_uid`, GROUP_CONCAT(nvote_option_id) option_ids, created FROM ' . $this->_table .
            ' WHERE `nvote_id` = ' . $nvote_id . ' AND `status`<' . self::STATUS_DELETE .
            ' GROUP BY `m_uid` ORDER BY null' ;

        $result = $this->_getAll($sql);

        return $result;
    }

    /**
     * 获取用户投票选项分页
     * @param $nvote_id 投票id
     * @param int $limit
     * @return Ambigous|array
     */
    public function get_uid_list($nvote_id, $limit = 5){

        $nvote_id = rintval($nvote_id);
        $result = array('list' => array(), 'count' => 0);
        if ($nvote_id < 1) {
            return $result;
        }

        $sql = 'SELECT `m_uid` FROM ' . $this->_table .
                ' WHERE `nvote_id` = ' . $nvote_id . ' AND `status`<' . self::STATUS_DELETE .
                ' GROUP BY `m_uid` ORDER BY `id` LIMIT ' . $limit[0] . ',' . $limit[1];
        $result['list'] = $this->_getAll($sql);

        if ($result['list']) {
            $count_sql = 'SELECT count(*) c FROM ' . $this->_table .
                ' WHERE `nvote_id` = ' . $nvote_id . ' AND `status`<' . self::STATUS_DELETE .
                ' GROUP BY `m_uid` ';
            $result['count'] = $this->_getOne($count_sql);
        }

        return $result;
    }
}
