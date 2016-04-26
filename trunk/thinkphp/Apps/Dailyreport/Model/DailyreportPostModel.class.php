<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:22
 */

namespace Dailyreport\Model;

class DailyreportPostModel extends AbstractModel {

    public $prefield = 'drp_';

    public function get_post($dr_id){
        $sql = "SELECT * FROM oa_dailyreport_post WHERE drp_status <> 3 AND drp_first=1 AND dr_id=".$dr_id;
        return $this->_m->fetch_row($sql);
    }

    /**
     * 获取报表评论列表
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     */
    public function get_comment_list($param){
        $page = intval($param['page']);

        $sql_select = "SELECT a.drp_id,a.m_username,a.m_uid,a.drp_message,a.drp_comment_user_name comment_username,a.drp_created,b.m_face,a.drp_comment_content comment_content
                FROM oa_dailyreport_post a LEFT JOIN oa_member b ON a.m_uid = b.m_uid
                WHERE a.drp_status <> 3 AND a.drp_first=0";

        $sql_order = "ORDER BY a.drp_created DESC";

        //拼装查询条件
        $sql_where = "";
        //报表ID
        if (array_key_exists("dr_id", $param) && $param["dr_id"] != "" && $param["dr_id"] != "0") {
            $sql_where .= "AND a.dr_id = ".$param["dr_id"];
        }

        $sql_count = "SELECT COUNT(*) FROM ({$sql_select} {$sql_where}) t";
        $count = $this->_m->result($sql_count);

        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        // 判断当前是否分页
        $sql_limit = '';
        if ($page) {
            $sql_limit = " LIMIT " . (($page - 1) * $page_len) . ',' . $page_len;
        }
        $sql = "{$sql_select} {$sql_where} {$sql_order} {$sql_limit}";
        $list = $this->_m->query($sql);

        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $list
        );
        return $result;
    }

    public function add_comment($param){
        //验证数据
        if($this->_validation_comment($param)){
            $this->_save_comment($param);
            return true;
        }
        return false;
    }

    /**
     * 保存评价
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     */
    private function _save_comment($param){
        $param['drp_status'] = 1;
        $param['drp_first'] = 0;
        $message_len=  mb_strlen($param['drp_message'],'utf8');
        if($message_len>140||$message_len<=0){
            E('_ERR_DAILYREPORRT_COMMENT_LEN');
            return false;
        }
        if ($this->insert($param)) {
            return true;
        }
        E('_ERR_DAILYREPORT_COMMENT_SAVE');
        return false;
    }

    /**
     * 验证数据
     * @param type $post
     */
    private function _validation_comment(&$param) {
        //验证dr_id
        if ($param['dr_id'] <= 0) {
            //汇报id
            E('_ERR_DAILYREPORT_COMMENT_DRID_SAVE');
            return false;
        }
        //验证用户
        if (!isset($param['m_uid'])) {
            //标题长度不正确
            E('_ERR_DAILYREPORT_COMMENT_DATA_SAVE');
            return false;
        }
        //验证用户姓名
        $drp_username_len = mb_strlen($param['m_username'], 'utf8');
        if (!isset($param['m_username']) || ($drp_username_len <= 0 || $drp_username_len >= 50)) {
            //用户姓名长度不正确
            E('_ERR_DAILYREPORT_COMMENT_DATA_SAVE');
            return false;
        }
        //验证用户姓名
        $drp_comment_len = mb_strlen($param['drp_message'], 'utf8');
        if (!isset($param['drp_message']) || ($drp_comment_len <= 0)) {
            //评论内容不能为空
            E('_ERR_DAILYREPORT_COMMENT_DATA_SAVE');
            return false;
        }
        return true;
    }

}
