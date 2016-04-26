<?php
/**
 * BlessingRedpackLogModel.class.php
 * 企业祝福红包 红包明细表 Model
 * @author: anything
 * @createTime: 2015/11/16 17:14
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

namespace BlessingRedpack\Model;

class BlessingRedpackLogModel extends AbstractModel{

    // 待拆
    const REDPACK_OPEN = 1;

    // 支付失败
    const REDPACK_PAY_ERROR = 2;

    // 已领取
    const REDPACK_OK = 3;

    // 已支付
    const REDPACK_PAY_SUCCESS = 4;

    //待支付
    const RedPACK_PAY_WAIT = 5;

    // 待抢
    const REDPACK_WAIT = 9;

    //红包领取对象：全公司
    const ALL_COMPANY = 0;

    //红包领取对象：指定对象
    const SPECIFIED = 1;

    //构造方法
    public function __construct(){
        parent::__construct();
    }



    /**
     * 红包领取详情列表
     * @param $params
     * @param $page_option
     * @param $order_option
     * @return array
     */
    public function list_receive_page($params, $page_option, $order_option){

        $data = array();
        $count = 0;

        $count = $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE m_uid != 0 AND redpack_id=?",
                array($params['id'], $this->get_st_delete()));

        if($count){
            $sql = "SELECT id, redpack_time, m_username, dep_name, redpack_status, money, mch_billno, return_code,
                payment_no, result_code, payment_no, ranking, m_uid
				FROM __TABLE__";

            // 查询条件
            $where = array('status<?');
            $where_params = array($this->get_st_delete());


            $where[] = "m_uid != 0 ";

            $where[] = "redpack_id = ?";
            $where_params[] = $params['id'];

            // 分页参数
            $limit = '';
            $this->_limit($limit, $page_option);
            // 排序
            $orderby = '';
            $this->_order_by($orderby, $order_option);

            $data = $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);
        }

        return array($data, $count);

    }

    /**
     * 红包分页列表统计红包领取金额、个数
     * @param array $conds
     * @return array|number
     */
    public function count_redpacksum_by_conds($conds) {

        $params = array();
        $wheres = array();

        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $params[] = $this->get_st_delete();

        $wheres[] = "m_uid != 0 ";

        if (!empty($conds['id'])) {
            $wheres[] = "id = ?";
            $params[] = $conds['id'];
        }

        if (!empty($conds['redpack_id'])) {
            $wheres[] = "redpack_id = ?";
            $params[] = $conds['redpack_id'];
        }

        return $this->_m->fetch_row("SELECT count(*) AS times,sum(money) as timesmoney FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
    }


    /**
     * 领取详情导出excel使用
     * @param $conds
     * @return array
     */
    public function count_by_params($conds) {

        $params = array();
        // 更新条件
        $wheres = array();

        // 状态条件
        $wheres[] = "`{$this->prefield}status`<?";
        $params[] = $this->get_st_delete();

        $wheres[] = "m_uid != 0 ";

        if (!empty($conds['redpack_id'])) {
            $wheres[] = "redpack_id = ?";
            $params[] = $conds['redpack_id'];
        }

        return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
    }

    public function list_receive_excel($params, $page_option, $order_option){

        $data = array();

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());


        $where[] = "m_uid != 0 ";

        $where[] = "redpack_id = ?";
        $where_params[] = $params['redpack_id'];

        // 分页参数
        $limit = '';
        $this->_limit($limit, $page_option);
        // 排序
        $orderby = '';
        $this->_order_by($orderby, $order_option);

        $data = $this->_m->fetch_array('SELECT * FROM __TABLE__ WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);

        return $data;

    }
}
