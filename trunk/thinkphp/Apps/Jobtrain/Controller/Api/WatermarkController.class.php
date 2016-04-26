<?php
namespace Jobtrain\Controller\Api;
use Common\Common\Cache;

class WatermarkController extends AbstractController {
    /**
	 * 水印
	 */
    public function watermark_get() {
        // 要打入水印的名字
        $text = $this->_login->user['m_username'];
        // 缓存的名字
        $filename = md5(strtolower($text)) . '.png';
        $z_path = get_sitedir();
        $save_dir = str_replace('/thinkphp/Apps/Runtime/Temp', '/apps/voa/data/attachments', $z_path) . 'watermark' . DIRECTORY_SEPARATOR;
        if (!is_dir($save_dir)) {
            rmkdir($save_dir, 0777);
        }
        $file_path = $save_dir . $filename;
        $ttf = str_replace('/thinkphp/Apps/', '/apps/voa/cyadmin_www/static/fonts/YaHei.ttf', APP_PATH);
        
        if (!file_exists($file_path)) {
            $size = mb_strlen($text, 'utf-8') > 6 ? 10 : 20;
            $block = imagecreatetruecolor(160, 150);//建立一个画板
            $bg = imagecolorallocatealpha($block, 0, 0, 0, 127);//
            $color = imagecolorclosestalpha($block, 0, 0, 0, 100);
            imagefill($block, 0, 0, $bg);//填充
            imagettftext($block, $size, 38, 70, 60, $color, $ttf, $text);
            //imagerotate($block, 145, 0);
            imagesavealpha($block, true);//设置保存PNG时保留透明通道信息
            imagepng($block, $file_path);//生成图片
            imagedestroy($block);
        }
        header('Content-type: image/png');
        ob_start();
        echo file_get_contents($file_path);
        ob_end_flush();
    }
    /**
     * 重写输出方法
     */
    protected function _response() {
        return true;
    }
}