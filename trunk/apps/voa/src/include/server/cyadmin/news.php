<?php
/**
 * class voa_server_cyadmin_news {
 * 畅移主站新闻模板接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_cyadmin_news {

	/**
	 * 读取模板
	 * @param array $request
	 */
	public function template_list($request = array()) {


		$uda_news = &uda::factory('voa_uda_cyadmin_news_template');
		// 检查企业是否存在
		$news = array();
		if(!empty($request['ne_id']) && is_numeric($request['ne_id'])) {
			if (!$uda_news->get_by_id($request, $news)) {
				return $this->_set_errmsg(voa_errcode_cyadmin_news::RPC_SERVER_SELECT_PROFILE_ID_NULL, $request['ne_id']);
			}
		} else {
			if (!$uda_news->list_all($request, $news)) {
				return $this->_set_errmsg(voa_errcode_cyadmin_news::RPC_SERVER_SELECT_DATA_EMPTY, $request);
			}
		}
		return $news;
	}

}
