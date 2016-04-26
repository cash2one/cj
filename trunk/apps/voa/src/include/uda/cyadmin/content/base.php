<?php
class voa_uda_cyadmin_content_base extends voa_uda_cyadmin_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	/**
	 * 验证标题基本合法性
	 *
	 * @param string $title        	
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_title($title) {
		$title = trim($title);
		if (!validator::is_required($title)) {
			return voa_h_func::throw_errmsg(voa_errcode_cyadmin_content::TITLE_ERROR, $title);
		}
		
		return true;
	}

	/**
	 * 验证内容基本合法性
	 * 
	 * @param string $content        	
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_content($content) {
		$content = trim($content);
		if (!validator::is_required($content)) {
			return voa_h_func::throw_errmsg(voa_errcode_cyadmin_content::CONTENT_ERROR, $content);
		}
		
		return true;
	}

	public function validatot_url($url) {
		$url = trim($url);
		if (!validator::is_url($url)) {
			
			return voa_h_func::throw_errmsg(voa_errcode_cyadmin_content::URL_ERROR, $url);
		}
		return true;
	}
}
