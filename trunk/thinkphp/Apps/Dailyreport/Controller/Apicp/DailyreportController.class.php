<?php
/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午10:53
 */

namespace Dailyreport\Controller\Apicp;


class DailyreportController extends AbstractController
{
    //登录开关
    protected $_require_login = true;

    /**
     * 获取报告的分页列表
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     */
    public function Getlist_get()
    {
        $param = I('get.');
        $serv_dr = D('Dailyreport', 'Service');
        if ($drs = $serv_dr->get_list($param)) {
            $this->_result = $drs;
            return true;
        };
        return false;
    }

    /**
     * 删除报告
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     */
    public function Del_get()
    {
        $dr_id = I('get.dr_id');
        $serv_dr = D('Dailyreport', 'Service');
        if ($serv_dr->del_dr($dr_id)) {
            return true;
        };
        return false;
    }

    /**
     * 获取日报类型
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     */
    public function Type_get(){
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($types = $serv_drt->get_typecp()) {
            $this->_result = $types;
            return true;
        };
        return false;
    }

    /**
     * 获取报告详情
     * @return boolean
     */
    public function GetAdminReport_get(){
        $dr_id = (int)I('get.dr_id');
        $serv_dr = D('Dailyreport', 'Service');
        if ($dr = $serv_dr->get_admin_report($dr_id)) {
            $this->_result=$dr;
            return true;
        };
        return false;
    }
    /**
     * 获取报告评论列表
     * @return boolean
     */
    public function GetAdminReportComments_get(){
        $dr_id = intval(I('get.dr_id'));
        $page =  intval(I('get.page'));
        $serv_dr = D('Dailyreport', 'Service');
        if ($comments = $serv_dr->get_admin_report_comments($dr_id,$page)) {
            $this->_result=$comments;
            return true;
        };
        return false;
    }
    /**
     * 删除报告评论
     * @return boolean
     */
    public function DelAdminReportComment_get(){
        $drp_id = intval(I('get.drp_id'));
        $serv_dr = D('Dailyreport', 'Service');
        if ($comments = $serv_dr->del_admin_report_comment($drp_id)) {
            $this->_result=$comments;
            return true;
        };
        return false;
    }

    /**
     * 导出csv文件
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $param
     */
    public function Export_get()
    {
        $param = I('get.');
        $serv_dr = D('Dailyreport', 'Service');

        // 获取总数
        $total = $serv_dr->get_export_count($param);

        // 初始化 压缩
        $zip = new \ZipArchive();
        // 路径和文件名
        $path = get_sitedir() . 'excel/';
        $zipname = $path . 'dailyreport' . date('YmdHis', time());
        if (!file_exists($zipname)) {
            $zip->open($zipname . '.zip', \ZipArchive::CREATE);
            // 分页参数
            if (!empty($total)) {
                $limit = 1000;
                $page = ceil($total / $limit);
                for ($i = 1; $i <= $page; $i++) {
                    // 分页参数
                    list($start, $limit, $i) = page_limit($i, $limit);
                    $param["start"] = $start;
                    $param["limit"] = $limit;
                    // 根据条件查询
                    $list = $serv_dr->get_export($param);
                    // 生成csv文件
                    $result = $this->__create_csv($list, $i, $path);
                    if ($result) {
                        $zip->addFile($result, $i . '.csv');
                    }
                }

            }
        } else {
            $result = $this->__create_csv(array(), 0, $path);
            if ($result) {
                $zip->addFile($result, 0 . '.csv');
            }
        }
        // 下载 并 清除文件
        $zip->close();
        $this->__put_header($zipname . '.zip');
        $this->__clear($path);
    }


    /**
     * 生成csv文件
     */
    private function __create_csv($list, $i, $path)
    {

        // 生成文件
        if (!is_dir($path)) {
            rmkdir($path);
        }
        $data = array();

        $filename = $i . '.csv';
        $data[0] = array(
            '提交人',
            '转发人',
            '部门',
            '报告类型',
            '报告标题',
            '报告内容',
            '图片',
            '提交时间'
        );

        if (!empty($list)) {
            foreach ($list as $val) {
                $tmp_message = str_replace(PHP_EOL, '',  $val['drp_message']);
                $temp = array(
                    'submitter' => $val['submitter'],
                    'forwarded' => $val['forwarded'],
                    'cd_name' => $val['cd_name'],//!empty($val['af_created']) ? rgmdate($val['af_created'], 'Y-m-d H:i') : '',
                    'drt_name' => $val['drt_name'],
                    'dr_subject' => !empty($val['dr_subject']) ? str_replace(PHP_EOL, '', $val['dr_subject']) : '', // 去掉换行
                    'drp_message' => $tmp_message,
                    'at_ids' => $val['at_ids'],
                    'dr_created' => !empty($val['dr_created']) ? rgmdate($val['dr_created'],'Y年m月d H:i:s') : ''
                );

                $data[] = $temp;
            }
        }

        $csv_data = $this->_array2csv($data);
        $fp = fopen($path . $filename, 'w');
        fwrite($fp, $csv_data); // 写入数据
        fclose($fp); // 关闭文件句柄

        return $path . $filename;
    }

    /**
     * 下载输出至浏览器
     */
    private function __put_header($zipname)
    {

        if (!file_exists($zipname)) {
            exit("下载失败");
        }
        $file = fopen($zipname, "r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . filesize($zipname));
        Header("Content-Disposition: attachment; filename=" . basename($zipname));
        echo fread($file, filesize($zipname));
        $buffer = 1024;
        while (!feof($file)) {
            $file_data = fread($file, $buffer);
            echo $file_data;
        }
        fclose($file);
    }

    /**
     * 清理产生的临时文件
     */
    private function __clear($path)
    {

        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                unlink($path . $file);
            }
        }
    }

    /**
     * 将一组整齐的数组转换为csv字符串
     * @param array $list 待转换的数组列表数据
     * @param string $newline_symbol 每行之间的分隔符号，默认为：“\r\n”
     * @param string $field_comma 字段之间的分隔符号，默认为：“,”
     * @param string $field_quote_symbol 字段的引用符号，默认为：“"”
     * @param string $out_charset 输出的数据字符集编码，默认为：gbk
     * @return string
     */
    protected function _array2csv(array $list, $newline_symbol = "\r\n", $field_comma = ",", $field_quote_symbol = '"', $out_charset = 'gbk')
    {

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
                    $_row .= $field_quote_symbol . addcslashes($_str, $field_quote_symbol) . $field_quote_symbol;
                }
                // 定义字段分隔符号
                $_comma = $field_comma;
            }

            // 行数据，以行分隔符号连接
            $data .= $_row_comma . $_row;

            // 定义换行符号
            $_row_comma = $newline_symbol;
        }

        // 输出数据
        return $this->_riconv($data, 'UTF-8', $out_charset);
    }

    /**
     * 转换编码
     * @param mixed $m
     * @param string $from
     * @param string $to
     * @return mixed
     */
    protected function _riconv($m, $from = 'UTF-8', $to = 'GBK')
    {
        if (strpos($to, '//') === false) {
            $to = $to . '//IGNORE';
        }
        switch (gettype($m)) {
            case 'integer':
            case 'boolean':
            case 'float':
            case 'double':
            case 'NULL':
                return $m;
            case 'string':
                return @iconv($from, $to, $m);
            case 'object':
                $vars = array_keys(get_object_vars($m));
                foreach ($vars AS $key) {
                    $m->$key = $this->_riconv($m->$key, $from, $to);
                }
                return $m;
            case 'array':
                foreach ($m AS $k => $v) {
                    $k2 = $this->_riconv($k, $from, $to);
                    if ($k != $k2) {
                        unset($m[$k]);
                    }
                    $m[$k2] = $this->_riconv($v, $from, $to);
                }
                return $m;
            default:
                return '';
        }
    }


}