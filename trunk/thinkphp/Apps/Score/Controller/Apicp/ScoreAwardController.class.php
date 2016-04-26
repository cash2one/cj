<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/13
 * Time: 17:55
 */

namespace Score\Controller\Apicp;

class ScoreAwardController extends AbstractController {

    /**
     * 奖品设置-奖品列表页
     */
    public function getAwardList() {
        $page    = I('get.page', 0, 'intval');
        $limit   = 6;

        $service = D('Score/ScoreAward', 'Service');
        //分页属性
        list($start, $limit, $page) = page_limit($page, $limit);
        //总数量
        $list  = $service->getAwardList($start, $limit);
        $total = $service->getAwardCount();
        //输出
        $this->_result = array(
            'list'     => $list,
            'cur_page' => $page,
            'page'     => ceil($total/$limit),
            'limit'    => $limit,
            'count'    => $total,
        );
    }

    /**
     * 添加奖品接口
     */
    public function addAward_post() {
        $title = I('post.title', '');
        //判断奖品名称是否合法 0-15个字符
        if (mb_strlen($title) <= 0 || mb_strlen($title) > 15) {
            E('_ERROR_AWARD_TITLE_LEN_INVALID');
        }
        $limit     = I('post.limit', 0);
        $stock     = I('post.stock', 0);
        $score     = I('post.score', 0);
        $uids      = I('post.uids', array());
        $cd_ids    = I('post.cd_ids', array());
        $award_pic = I('post.award_pic');
        $desc      = I('post.desc', '');
        if (!$score || !($uids || $cd_ids) || !$award_pic || !$desc || !$stock) {
            E('_ERROR_ADD_AWARD_PARAM_NOT_ENOUGH');
        }
        //图片最多只能上传5张
        if (count($award_pic) > 5) {
            E('_ERROR_ADD_AWARD_PIC_TOO_MUCH');
        }

        $rs = D('Score/ScoreAward', 'Service')->addAward($title, $limit, $stock, $score, $uids, $cd_ids, $award_pic, $desc);

        $this->_result = array(
            'status' => $rs
        );
    }

    /**
     * 修改/编辑奖品接口
     */
    public function editAward_post() {
        $award_id = I('post.award_id', 0);
        $title = I('post.title', '');
        if (mb_strlen($title) <= 0 || mb_strlen($title) > 15) {
            E('_ERROR_AWARD_TITLE_LEN_INVALID');
        }
        $limit     = I('post.limit', 0);
        $stock     = I('post.stock', 0);
        $score     = I('post.score', 0);
        $uids      = I('post.uids', array());
        $cd_ids    = I('post.cd_ids', array());
        $award_pic = I('post.award_pic', 0);
        $desc      = I('post.desc', '');
        if (!$award_id || !$score || !($uids || $cd_ids) || !$award_pic || !$desc || !$stock) {
            E('_ERROR_ADD_AWARD_PARAM_NOT_ENOUGH');
        }

        if (count($award_pic) > 5) {
            E('_ERROR_ADD_AWARD_PIC_TOO_MUCH');
        }

        $rs = D('Score/ScoreAward', 'Service')->editAward($award_id, $title, $limit, $stock, $score, $uids, $cd_ids, $award_pic, $desc);

        $this->_result = array(
            'status' => $rs
        );
    }

    /**
     * 奖品兑换记录列表页
     */
    public function AwardExchangeList_get() {
        $page       = I('get.page', 0, 'intval');
        $start_date = I('get.start_date', '');
        $end_date   = I('get.end_date', '');
        $status     = I('get.status', 0);
        $order_num  = I('get.order_num', '');
        $limit      = 6;

        $start_date = strtotime($start_date);
        $end_date   = strtotime($end_date);

        $service = D('Score/ScoreAward', 'Service');
        //分页属性
        list($start, $limit, $page) = page_limit($page, $limit);
        //总数量
        $list  = $service->getAwardExchangeLogs($start_date, $end_date, $status, $order_num, $start, $limit);
        $total = $service->AwardExchangeLogCount($start_date, $end_date, $status, $order_num);
        //输出
        $this->_result = array(
            'start_date' => $start_date ? date('Y-m-d, H:i',$start_date) : 0,
            'end_date'   => $end_date ? date('Y-m-d, H:i',$end_date) : 0,
            'status'     => $status,
            'order_num'  => $order_num,
            'list'       => $list,
            'cur_page'   => $page,
            'page'       => ceil($total/$limit),
            'limit'      => $limit,
            'count'      => $total,
        );
    }

    /**
     * 奖品兑换记录详情页 （处理中，已同意，已拒绝三种页面）
     */
    public function awardDetail() {
        $order_id = I('get.order_id', 0);
        $order = D('Score/ScoreAward', 'Service')->getAwardOrderDetail($order_id);

        $this->_result = array(
            'order' => $order,
        );
    }

    /**
     * 处理奖品兑换订单接口
     */
    public function processAwardOrder_post() {
        $order_id = I('post.order_id', 0);
        $status   = I('post.status', 2);
        $reason   = I('post.reason', '');

        //如果是拒绝 必须输入拒绝理由
        if ($status == 3 && !$reason) {
            E('_ERROR_AWARD_PROCESS_REASON_INVALID');
        }

        $rs = D('Score/ScoreAward', 'Service')->processAwardOrder($order_id, $status, $reason);
        if (!$rs) E('_SCORE_AWARD_AGREE_FAILED');
        $this->_result = array(
            'result' => $rs,
        );
    }

    /**
     * 更改奖品状态
     */
    public function changeAwardStatus_post() {
        $award_id = I('post.award_id', 0);
        $status   = I('post.status', 1);

        $rs = D('Score/ScoreAward', 'Service')->changeAwardStatus($award_id, $status);

        $this->_result = array(
            'result' => $rs
        );
    }

    /**
     * 编辑奖品页面
     */
    public function awardEdit_get() {
        $award_id = I('get.award_id', 0);
        $detail = D('Score/ScoreAward', 'Service')->getAwardDetail($award_id);

        $this->_result = array(
            'detail' => $detail,
        );
    }

    /**
     * 奖品兑换记录导出csv接口
     */
    public function exchangeLogOutputCsv() {
        $start_date = I('get.start_date', '');
        $end_date   = I('get.end_date', '');
        $status     = I('get.status', 0);
        $order_num  = I('get.order_num', '');

        $service = D('Score/ScoreAward', 'Service');
        $list  = $service->getAwardExchangeLogs($start_date, $end_date, $status, $order_num);

        // 待输出的数据，数组格式
        $data = array();
        // 标题栏 - 字段名称
        $data[] = array(
            'orderstatus' => '订单状态',
            'ordernumber' => '订单编号',
            'createtime' => '兑换时间',
            'awardtitle' => '兑换商品',
            'awardnum'   => '兑换数量',
            'score'      => '消耗积分',
            'musername'  => '兑换人姓名',
            'mmobilephone' => '手机号',
            'memail'       => '电子邮箱',
            'mdesc'        => '备注',
        );

        // 遍历数据每行一条
        foreach ($list as $value) {
            switch($value['status']) {
                case 1: $status_name = '未处理';break;
                case 2: $status_name = '已兑换';break;
                case 3: $status_name = '已拒绝';break;
                default :$status_name = '未处理';break;
            }
            $data[] = array(
                'orderstatus' => $status_name,
                'ordernumber' => $value['order_number'],
                'createtime' => date('Y/m/d H:i', $value['create_time']),
                'awardtitle' => $value['award_title'],
                'awardnum'   => $value['award_num'],
                'score'      => $value['score'],
                'musername'  => $value['m_username'],
                'mmobilephone' => $value['m_mobilephone'],
                'memail'     => $value['m_email'],
                'mdesc'      => $value['m_desc'],
            );
        }

        // 转换为csv字符串
        $csv_data = $this->array2csv($data);

        $filename = date('Y-m-d', time()) . '.csv';

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: text/csv");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Coentent_Length: ' . strlen($csv_data));
        echo $csv_data;
        exit();
    }

    /**
     * 数组转csv方法
     * @param array $list
     * @param string $newline_symbol
     * @param string $field_comma
     * @param string $field_quote_symbol
     * @param string $out_charset
     * @return mixed
     */
    public function array2csv(array $list, $newline_symbol = "\r\n", $field_comma = ",", $field_quote_symbol = '"', $out_charset = 'gbk') {
        // 初始化输出
        $data = '';
        // 初始化换行符号
        $_row_comma = '';
        // 遍历所有行数据
        foreach ($list as $_arr_row) {
            // 初始化行数据
            $_row = '';
            // 初始化每个字段的分隔符号
            $_comma = '';
            // 遍历所有字段
            foreach ($_arr_row as $_str) {
                // 字段数据分隔符
                $_row .= $_comma;
                if (strpos($_str, $field_comma) === false) {
                    // 字段数据不包含字段分隔符，直接使用
                    $_row .= $_str;
                } else {
                    // 字段数据包含字段分隔符，则使用字段引用符号引用并转义数据内的引用符号
                    $_row .= $field_quote_symbol.addcslashes($_str, $field_quote_symbol).$field_quote_symbol;
                }
                // 定义字段分隔符号
                $_comma = $field_comma;
            }
            // 行数据，以行分隔符号连接
            $data .= $_row_comma.$_row;
            // 定义换行符号
            $_row_comma = $newline_symbol;
        }
        // 输出数据
        return riconv($data, 'UTF-8', $out_charset);
    }
}