<?php
/**
 * voa_uda_frontend_news_comment_delete
 * 统一数据访问/新闻公告/删除评论
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_comment_delete extends voa_uda_frontend_news_abstract {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news_comment();
		}
	}

	/**
	 * 删除单个评论
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function delete_comment($ncomm_id) {

		$this->__service->delete($ncomm_id);   //删除评论

		return true;
	}


}
