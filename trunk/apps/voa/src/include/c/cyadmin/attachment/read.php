<?php

/**
 * voa_c_cyadmin_attachment_read
 * 读取附件
 */
class voa_c_cyadmin_attachment_read extends voa_c_cyadmin_attachment_base {

	public function execute() {
		// 获取附件ID
		$atid = (int) $this->request->get( 'atid' );
		if( $atid < 1 ) {
			return true;
		}
		// 获取图片
		$serv   = &service::factory( 'voa_s_cyadmin_attachment' );
		$attach = $serv->get( $atid );
		// 判断附件是否存在

		/**
		 * 附件获取
		 */
		if( ! $this->getattach( $attach ) ) {
			return false;
		}

		return $this->getattach( $attach );
	}

	protected function getattach( $attach ) {
		$resp = controller_response::get_instance();
		$req  = controller_request::get_instance();
		// 取存储目录
		$dir = dirname( APP_PATH ) . DIRECTORY_SEPARATOR . 'voa';
		$dir = $dir . '/data/cyadmin';
		if( '/' != substr( $dir, - 1 ) ) {
			$dir .= '/';
		}

		// 附件文件的绝对路径
		$filepath = $dir . $attach['atattachment'];
		// 最后修改时间
		$filemtime = filemtime( $filepath );
		$resp->set_raw_header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $filemtime ) . ' GMT' );

		// 尝试发送 304响应强制浏览器使用缓存
		if( ! empty( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && ( $HTTP_IF_MODIFIED_SINCE = strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) >= $filemtime ) ) {
			$resp->set_raw_header( 'Etag: ' );
			$resp->set_raw_header( "HTTP/1.1 304 Not Modified" );
			$resp->send_headers();

			return true;
		}

		// 附件文件的 mime 类型字符串
		$mime = rmime_content_type( $filepath );

		// 清除输出缓冲
		@ob_end_clean();

		// 处理名称里的符号，避免chrome无法解析问题
		$attach['atname'] = str_replace( array(
			'&',
			'=',
			','
		), '_', $attach['atname'] );

		// 如果是附件则显示，而不是下载

		$resp->set_raw_header( 'Content-Disposition: inline; filename=' . $attach['atname'] );
		$resp->set_raw_header( 'Content-Type: ' . $mime );

		// 发送浏览器头
		$resp->set_raw_header( "Content-Length: " . filesize( $filepath ) );
		$resp->send_headers();

		$fp = fopen( $filepath, 'rb' );
		if( $fp ) {
			fseek( $fp, 0 );
			if( function_exists( 'fpassthru' ) ) {
				fpassthru( $fp );
			} else {
				echo @fread( $fp, filesize( $filepath ) );
			}
		}

		fclose( $fp );

		flush();
		ob_flush();

		return true;
	}
}
