<?php
/**
 * voa_uda_frontend_news_delete
 * 统一数据访问/新闻公告/删除新闻公告
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_delete extends voa_uda_frontend_news_abstract {

	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 获取单个新闻公告
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function delete_news($ne_ids) {
		//删除公告、公告内容、公告权限、公告阅读情况、公告评论
		try {
			$this->__service->begin();

			$s_news_content = new voa_s_oa_news_content();
			$s_news_right = new voa_s_oa_news_right();
			$s_news_read = new voa_s_oa_news_read();
			$s_news_comment = new voa_s_oa_news_comment();

			$conds = array(
				'ne_id' => $ne_ids
			);

			$this->__service->delete($ne_ids);                 //删除公告
			$s_news_content->delete_by_conds($conds);            //删除公告内容
			$s_news_right->delete_real_records_by_conds($conds); //物理删除公告权限
			$s_news_read->delete_real_records_by_conds($conds);  //物理删除公告阅读情况
			$s_news_comment->delete_by_conds($conds);             //删除公告评论

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();

			return $this->set_errmsg(voa_errcode_oa_news::DELETE_NEWS_FAILED);
		}
		return true;
	}


}
