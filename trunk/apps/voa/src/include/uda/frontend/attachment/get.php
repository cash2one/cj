<?php
/**
 * voa_uda_frontend_attachment_get
 * 统一数据访问/公共附件表/读取
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_attachment_get extends voa_uda_frontend_attachment_base {

	public function read($attach, $options = array()) {
		$resp = controller_response::get_instance();
		$req = controller_request::get_instance();
		// 取存储目录
		$dir = voa_h_func::get_attachdir(startup_env::get('domain'));
		if ('/' != substr($dir, - 1)) {
			$dir .= '/';
		}
		
		// 附件文件的绝对路径
		$filepath = $dir . $attach['at_attachment'];
		
		// 最后修改时间
		$filemtime = filemtime($filepath);
		$resp->set_raw_header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
		
		// 尝试发送 304响应强制浏览器使用缓存
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && ($HTTP_IF_MODIFIED_SINCE = rstrtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $filemtime)) {
			$resp->set_raw_header('Etag: ');
			$resp->set_raw_header("HTTP/1.1 304 Not Modified");
			$resp->send_headers();
			return true;
		}
		
		// 附件文件的 mime 类型字符串
		$mime = rmime_content_type($filepath);
		
		// 清除输出缓冲
		@ob_end_clean();
		
		// 处理名称里的符号，避免chrome无法解析问题
		$attach['at_filename'] = str_replace(array(
			'&',
			'=',
			',' 
		), '_', $attach['at_filename']);
		if (!$options['download']) {
			$resp->set_raw_header('Content-Disposition: inline; filename=' . $attach['at_filename']);
			$resp->set_raw_header('Content-Type: ' . $mime);
		} else {
			$resp->set_raw_header('Content-Disposition: attachment; filename=' . $attach['at_filename']);
			$resp->set_raw_header('Content-Type: ' . $mime);
		}
		
		// 读取文件
		$wh = (int) $req->get('wh');
		$wh = $attach['at_isimage'] && 0 < $wh ? $wh : 0;
		// voa_h_attach::get_local_file($filepath, $wh);
		
		/**
		 * 如果是图片
		 */
		if (0 < $wh) {
			voa_h_attach::get_image_file($filepath, $wh);
		}
		
		if (!is_file($filepath)) {
			return true;
		}
		
		// 发送浏览器头
		$resp->set_raw_header("Content-Length: " . filesize($filepath));
		$resp->send_headers();
		
		$fp = fopen($filepath, 'rb');
		if ($fp) {
			fseek($fp, 0);
			if (function_exists('fpassthru')) {
				fpassthru($fp);
			} else {
				echo @fread($fp, filesize($filepath));
			}
		}
		
		fclose($fp);
		
		flush();
		ob_flush();
		return true;
	}
}
