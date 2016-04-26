<?php
/**
 * BlessingRedpackLogController.class.php
 * 红包明细表
 * @author: anything
 * @createTime: 2016/1/15 12:03
 * @version: $Id$ 
 * @copyright: 畅移信息
 */

namespace BlessingRedpack\Controller\Apicp;
use Com\Excel;
use ZipArchive;

class BlessingRedpackLogController extends AbstractController {


    /**
     * 同步微信支付状态
     */
    public  function syncWePayResult_get(){
        $params = I('get.');
        if(empty($params['redpack_id'])){
            if(empty($params['single'])){
                E('_ERROR_PARAM_VAL_NULL');
                return false;
            }
        }

        $__redpack_log_service = D('BlessingRedpack/BlessingRedpackLog', 'Service');

        $__redpack_log_service->syncWePayResult($params);

    }


    /**
     * 导出领取详情
     */
    public function exportExcel_get(){
        $redpack_id = I('get.redpack_id');

        $__redpack_log_service = D('BlessingRedpack/BlessingRedpackLog', 'Service');

        $conds = array(
            'redpack_id'=>$redpack_id
        );

        $size = $__redpack_log_service->count_by_params($conds);


        // 实例化压缩类
        $zip = new ZipArchive();
        $path = get_sitedir() . 'excel/';
        rmkdir($path);
        $zipname = $path . 'sign' . date('YmdHis', time());
        $zip->open($zipname . '.zip', ZipArchive::CREATE);

        $title_string = array (
            '排名',
            '领取时间',
            '领取人',
            '所在部门',
            '领取状态',
            '领取金额（单位：元）',
        );

        $data = array();
        $row_data = array();

        // 排序
        $order_by = array('redpack_time' => 'ASC');

        //每次查询5000条
        $speed = 5000;
        $batch_count = 0;
        do{

            $from = $batch_count * $speed;
            $batch_count++;
            $filename = 'receve' . $batch_count;
            $page_option = array($from, $speed);
            $data = $__redpack_log_service->list_receive_excel($conds, $page_option, $order_by);
            foreach($data as $v){
                $obj = $__redpack_log_service->format($v);
                $tmp = array();
                $tmp[] = $obj['ranking'];
                $tmp[] = $obj['_redpack_time'];
                $tmp[] = $obj['m_username'];
                $tmp[] = $obj['dep_name'];
                $tmp[] = $obj['_redpack_status'];
                $tmp[] = $obj['_money'];

                $row_data[] = $tmp;
            }

            $tmp_path = $this->__mk_execl($filename, $title_string, $row_data);

            $zip->addFile($tmp_path, $filename . '.xls');

            unset($row_data);


        } while ($size > ($speed * $batch_count));


        $zip->close();
        $this->__put_header($zipname . '.zip');

        // 清除文件缓存
        $this->__clear($path);

    }


    /**
     * 生成excel
     * @param $filename
     * @param $titles
     * @param $row_data
     * @return string
     * @throws \PHPExcel_Exception
     */
    private function __mk_execl($filename, $titles, $row_data){
        $excel = new \Com\Excel();
        //当前操作sheet
        $excel->setActiveSheetIndex(0);
        //sheet名称
        $excel->getActiveSheet()->setTitle('sheet1');

        //设置标题
        $start = 'A';
        for($i = 0; $i < count($titles); $i ++) {
            $_column = $start ++;
            $excel->getActiveSheet()->setCellValue($_column . '1', $titles[$i]);
            $excel->getActiveSheet()->getColumnDimension($start)->setWidth(15);
        }

        $n  = 2;
        //填充数据
        foreach($row_data as $_row){
            $start = 'A';
            for($i = 0; $i < count($_row); $i ++) {
                $_column = $start ++;
                $excel->getActiveSheet()->setCellValueExplicit($_column . $n, $_row[$i], 'str');
                $excel->getActiveSheet()->getStyle($_column . $n)->getAlignment()->setWrapText(true)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            $n ++;
        }

        // 创建Excel输入对象
        $write = $this->__write($excel, $filename);
        // 设置存储路径
        $path = get_sitedir() . 'excel/';
        if (! is_dir($path)) {
            mkdir($path);
        }

        $write->save($path . $filename . ".xls");

        $filepath = $path . $filename . '.xls';

        return $filepath;
    }



    private function __write($excel, $name) {
        // 创建Excel输入对象
        $write = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $name . '.xls"');
        header("Content-Transfer-Encoding:binary");

        return $write;
    }

    /**
     * 下载输出至浏览器
     *
     * @param $zipname
     */
    private function __put_header($zipname) {

        if (! file_exists($zipname)) {
            exit("下载失败");
        }

        $file = fopen($zipname, "r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . filesize($zipname));
        Header("Content-Disposition: attachment; filename=" . basename($zipname));
        echo fread($file, filesize($zipname));
        $buffer = 1024;
        while (! feof($file)) {
            $file_data = fread($file, $buffer);
            echo $file_data;
        }
        fclose($file);
    }

    /**
     * 清理产生的临时文件
     */
    private function __clear($path) {

        $dh = opendir($path);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                unlink($path . $file);
            }
        }
    }

}
