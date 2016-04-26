<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/13
 * Time: 17:55
 */

namespace Score\Controller\Apicp;

class ScoreController extends AbstractController {

    /**
     * 积分设置首页
     */
    public function getSwitch() {
        $switch = M('ScoreConfig')->find('switch');
        $this->_result = array(
            'score_config' => $switch['value'],
        );
    }

    /**
     * 更改积分设置开启状态接口
     */
    public function postChangeSwitch() {
        $switch = I('post.switch', true);

        $rs = D('Score/Score', 'Service')->changeSwitch($switch);

        $this->_result = array(
            'status' => $switch,
        );
    }

    /**
     * 积分规则列表页面
     */
    public function getScoreRuleList() {
        $app_type = I('get.app_type', 0);
        $list = D('Score/Score','Service')->getRuleList($app_type);

        $this->_result = array(
            'app_type' => $app_type,
            'list'     => $list,
            'app_type_names' => D('Score/Score', 'Service')->scoreChangeAppType,
        );
    }

    /**
     * 修改积分规则接口
     */
    public function postUpdateRules() {
        $datas = I('post.rules', array());

        $re = D('Score/Score', 'Service')->updateRuleList($datas);

        $this->_result = array(
            'status' => $re,
        );
    }

    /**
     * 修改积分规则状态接口
     */
    public function changeRuleStatus_post() {
        $rule_id = I('post.rule_id', 0);
        $status  = I('post.status', 1);

        $rs = D('Score/Score', 'Service')->changeRuleStatus($rule_id, $status);

        $this->_result = array(
            'status' => $rs,
        );
    }

    /**
     * 积分变化记录列表页
     */
    public function getScoreLogList()
    {
        $service = D('Score/Score', 'Service');
        $page = I('get.page', 2, 'intval');
        $name = I('get.username', '');
        $cd_name = I('get.cd_name', '');
        $type = I('get.type', 0);
        $limit = 6;
        //分页属性
        list($start, $limit, $page) = page_limit($page, $limit);

        //总数量
        $total = $service->countScoreLogList($name, $cd_name, $type);
        $list = $list = $service->scoreLogList($name, $cd_name, $type, $start, $limit);
        //输出
        $this->_result = array(
            'list' => $list,
            'cur_page' => $page,
            'page' => ceil($total / $limit),
            'limit' => $limit,
            'count' => $total,
        );
    }

    /**
     * 积分变化记录导出csv接口
     */
    public function outputScoreLogCsv() {
        $service = D('Score/Score', 'Service');

        $name = I('get.username', '');
        $cd_name = I('get.cd_name', '');
        $type = I('get.type', 0);

        $list = $list = $service->scoreLogList($name, $cd_name, $type);
        // 待输出的数据，数组格式
        $data = array();
        // 标题栏 - 字段名称
        $data[] = array(
            'username' => '姓名',
            'department' => '部门',
            'createtime' => '操作时间',
            'type' => '积分调整类型',
            'score'   => '分值',
            'desc'      => '调整原因',
            'opusername'  => '操作人',
        );

        // 遍历数据每行一条
        foreach ($list as $value) {
            switch($value['type']) {
                case 1: $type_name = '积分增加';break;
                case 2: $type_name = '积分惩罚';break;
                default :$type_name = '积分增加';break;
            }
            $data[] = array(
                'username' => $value['m_username'],
                'department' => $value['cd_name'],
                'createtime' => date('Y/m/d H:i', $value['create_time']),
                'type' => $type_name,
                'score'   => $value['num'],
                'desc'      => $value['desc'],
                'opusername'  => $value['op_username'],
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
