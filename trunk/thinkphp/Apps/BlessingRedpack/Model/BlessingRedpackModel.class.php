<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 15/11/13
 * Time: 下午2:13
 */

namespace BlessingRedpack\Model;


class BlessingRedpackModel extends AbstractModel{

    // 随机红包
    const TYPE_RAND = 1;
    // 平均红包
    const TYPE_AVERAGE = 2;
    //自由红包
    const TYPE_FREE = 4;

    //红包消息未推送
    const MSG_NOSENT = 0;

    //红包消息已推送
    const MSG_SENT = 1;

    // 构造方法
    public function __construct() {

        parent::__construct();
    }

    /**根据条件查询红包列表（分页）
     * @param $params
     * @param $page_option
     * @param $order_option
     */
    public function list_page($params, $page_option, $order_option){
        $sql = "SELECT `id`, `m_uid`, `m_username`, `actname`, `remark`, `type`, `money`, `total`,
                `remainder`, `redpacks`, `times`, `starttime`, `endtime`, `nickname`, `sendname`, `wishing`,
                `logoimgurl`, `sharecontent`, `shareimgurl`, `highest`, `status`,`created`, `updated`,
                `deleted`,`persons`, `content`, `chat_bg`, `receive_bg`, `share_num`, `see_num`
				FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 活动主题
        if (!empty($params['actname'])) {
            $where[] = "actname LIKE ?";
            $where_params[] = '%' . $params['actname'] . '%';
        }

        // 分页参数
        $limit = '';
        $this->_limit($limit, $page_option);
        // 排序
        $orderby = '';
        $this->_order_by($orderby, $order_option);

        return $this->_m->fetch_array($sql . ' WHERE ' . implode(' AND ', $where) . "{$orderby}{$limit}", $where_params);

    }

    /**
     * 查询未发送红包记录（crontab使用）
     * @param $params
     * @return array
     */
    public function list_by_params($params){
        $sql = "SELECT `id`, `m_uid`, `m_username`, `actname`, `remark`, `type`, `money`,
                       `total`, `remainder`, `redpacks`, `times`, `starttime`, `endtime`,
                       `nickname`, `sendname`, `wishing`, `logoimgurl`, `sharecontent`,
                       `shareimgurl`, `highest`, `persons`, `content`, `chat_bg`, `receive_bg`,
                       `share_num`, `see_num`, `invite_content`, `msg_status`, `status`, `created`,
                       `updated`, `deleted`
				FROM __TABLE__";

        // 查询条件
        $where = array('status<?');
        $where_params = array($this->get_st_delete());

        // 活动主题
        $where[] = " msg_status = ?";
        $where_params[] = $params['msg_status'];

        if (!empty($params['systime'])) {
            $where[] = " ? > starttime";
            $where_params[] = $params['systime'];
        }

        return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $where), $where_params);

    }
}
