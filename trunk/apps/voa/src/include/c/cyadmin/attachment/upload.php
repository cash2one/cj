<?php

class voa_c_cyadmin_attachment_upload extends voa_c_cyadmin_attachment_base {

	public function execute() {

		// 上传文件表单名
		$input_name = (string) $this->request->get( 'file' );
		if( ! empty( $_FILES[ $input_name ]['name'] ) ) {
			$atid        = null;
			$attach_list = array();
			list( $atid, $attach_list[0] ) = $this->_pro_attachment( $_FILES[ $input_name ] );
			$attach_list[0]['url']          = $this->_get_img_url( $atid );
			$attach_list[0]['thumbnailUrl'] = $attach_list[0]['url'];
			$result                         = array(
				'id'        => $atid,// 附件id
				$input_name => $attach_list,
				'list'      => $attach_list
			);

		}

		$r = array(
			'errcode'   => 0,
			'errmsg'    => 'OK',
			'timestamp' => startup_env::get( 'timestamp' ),
			'result'    => $result
		);
		$this->response->append_body( rjson_encode( $r ) );
		$this->response->stop();
	}

	/**
	 * 处理附件
	 *
	 * @param array $file
	 *
	 * @return int
	 *
	 */
	protected function _pro_attachment( $file = array() ) {
		// 实例化附件处理类
		$config = array(
			'save_dir_path' => APP_PATH . config::get( startup_env::get( 'app_name' ) . '.cyadmin.dir' ),
			// 'save_dir_path' =>voa_h_func::get_attachdir(startup_env::get('domain')),
			'allow_files'   => array(
				'png',
				'jpg',
				'jpeg',
				'gif',
				'bmp'
			)
		);

		if( ! file_exists( $config['save_dir_path'] ) ) {
			mkdir( $config['save_dir_path'], 0777 );
		}
		$upload  = new upload( $file, $config );
		$imginfo = $upload->get_file_info();

		$img                 = array();
		$img['atattachment'] = $imginfo['save_path'];
		$img['atname']       = $imginfo['file_name'];
		// 把图片路径存到附件表中
		$serv_at = &service::factory( 'voa_s_cyadmin_attachment' );
		$tmp     = $serv_at->insert( $img );

		return array( $tmp['atid'], $img );

	}
}
	
	
