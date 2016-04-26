<?php
/**
 * voa_c_cyadmin_article_view
 * 文章详情
 */
class voa_c_cyadmin_content_article_view extends voa_c_cyadmin_content_article_base {

	public function execute() {
		$aid = $this->request->get('aid');
		if (empty($aid)) {
			$this->message('error', '请指定要查看的数据');
		}
		$uda = &uda::factory('voa_uda_cyadmin_content_article_list');
		$view = $uda->get_view($aid);
		if (!empty($view['logo_atid'])) {
			$view['logo_url'] = $this->_get_img_url($view['logo_atid']);
		}
		
		if (!empty($view['face_atid'])) {
			$view['face_url'] = $this->_get_img_url($view['face_atid']);
		}
		$view['cname'] = $this->formart($view['acid']);
		$view['tags'] = $this->_pro_tags($view['tags']);
		$this->view->set('view', $view);
		$this->output('cyadmin/content/article/view');
	}

	public function formart($acid) {
		$cate = $this->_serv_cate->list_all();
		$cdata = array();
		foreach ($cate as $val) {
			$cdata[$val['acid']] = $val['acname'];
		}
		if (array_key_exists($acid, $cdata)) {
			$cname = $cdata[$acid];
		} else {
			$cname = '未分类';
		}

		return $cname;
	}
/**
 * 处理标签
 * 
 * @param string $tags        	
 * @param string $tagsurl        	
 * @return array
 *
 */
	/*
	 * protected function _tags($tags, $tagsurl){
	 * $_tags = array();
	 * $_tagsurl = array();
	 * $_combine = array();
	 * if(!empty($tags)){
	 *
	 * $_tags = explode(',', $tags);
	 *
	 * if(!empty($tagsurl)){
	 *
	 * $_tagsurl = explode(',', $tagsurl);
	 * }
	 * $ct = count($_tags);
	 * $cu = count($_tagsurl);
	 * if($cu == 0){
	 *
	 * return $_tags;
	 * }
	 * if($ct != $cu){
	 *
	 * if($ct > $cu){ //如果标签数大于链接数就娶$_tags的前$cu个单元
	 *
	 * $_tags = array_slice($_tags, 0, $cu);
	 *
	 * }else{ //如果标签数小于链接数就取$_tagsurl的前$ct个单元
	 *
	 * $_tagsurl = array_slice($_tagsurl, 0, $ct);
	 *
	 * }
	 *
	 * }
	 *
	 * $_combine = array_combine($_tags, $_tagsurl);
	 *
	 * return $_combine;
	 * }
	 *
	 * return false;
	 * }
	 */
}
