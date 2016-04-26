<?php
class voa_c_cyadmin_content_base extends voa_c_cyadmin_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	/**
	 * 处理附件
	 *
	 * @param array $file        	
	 * @return int
	 *
	 */
	protected function _pro_attachment($file = array()) {
		// 实例化附件处理类
		$config = array(
			'save_dir_path' => APP_PATH . config::get(startup_env::get('app_name') . '.cyadmin.dir'),
			// 'save_dir_path' =>voa_h_func::get_attachdir(startup_env::get('domain')),
			'allow_files' => array(
				'png',
				'jpg',
				'jpeg',
				'gif',
				'bmp' 
			) 
		);
		$upload = new upload($file, $config);
		$imginfo = $upload->get_file_info();
		
		$img = array();
		$img['atattachment'] = $imginfo['save_path'];
		$img['atname'] = $imginfo['file_name'];
		// 把图片路径存到附件表中
		$serv_at = &service::factory('voa_s_cyadmin_attachment');
		$tmp = $serv_at->insert($img);
		return $tmp['atid'];
	}

	/**
	 * 验证字符串长度是否合法
	 *
	 * @param string $str        	
	 * @param int $min        	
	 * @param int $max        	
	 * @param string $message        	
	 * @return
	 *
	 */
	protected function _is_legal($str, $min, $max, $message,$encode = null) {
		if (!validator::is_len_in_range($str, $min, $max, $encode)) {
			$this->message('error', $message);
			exit();
		}
	}

	/**
	 * $url string
	 */
	protected function _is_url($url) {
		$url = trim($url);
		$pattern ='/^(http)s?:\/\/[\w]+\.[\w]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/i';
		if (!preg_match($pattern, $url)) {
			$this->message('error', '链接不合法,以http开头');
			exit();
		}
	}

	/**
	 * 验证是否为负数
	 */
	protected function _is_negative($num) {
		$num = trim($num);
		if ($num < 0) {
			$this->message('error', '不能为负数');
		}
	}

	/**
	 * 处理标签
	 * 
	 * @param $tags string        	
	 * @return array
	 *
	 */
	protected function _pro_tags($tags) {
		if (!empty($tags)) {
			$tags = explode(',', $tags);
			return $tags;
		}
		return false;
	}
}
