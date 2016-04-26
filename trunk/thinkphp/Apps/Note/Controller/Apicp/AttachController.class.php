<?php

namespace Note\Controller\Apicp;

class AttachController extends AbstractController {
    /**
     * 强制下载文件
     */
    public function download() {
        $aid = I('get.at_id');
        $serv_a = D('Common/CommonAttachment', 'Service');
        // 获取附件
        $attach = $serv_a->get($aid);
        if($attach){
            // 获取附件物理路径
            $z_path = get_sitedir();
            $attach_path = str_replace('/thinkphp/Apps/Runtime/Temp', '/apps/voa/data/attachments', $z_path) . $attach['at_attachment'];
            if (is_file($attach_path)) {
                $length = filesize($attach_path);
            }else{
                exit();
            }

            $showname = iconv('UTF-8', 'GBK', $attach['at_filename']);
           
            $expire = 180;
            // 发送Http Header信息 开始下载
            header("Pragma: public");
            header("Cache-control: max-age=" . $expire);
            // header('Cache-Control: no-store, no-cache, must-revalidate');
            header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expire) . "GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . "GMT");
            header("Content-Disposition: attachment; filename=" . $showname);
            header("Content-Length: " . $length);
            header("Content-type: application/octet-stream");
            header('Content-Encoding: none');
            header("Content-Transfer-Encoding: binary");
            readfile($attach_path);

            exit();
        }
    }
}