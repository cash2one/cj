<?php
/**
 * voa_uda_frontend_news_addcategory
 * 统一数据访问/新闻公告/添加新闻公告类型
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_addcategory extends voa_uda_frontend_news_abstract {

	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 修改新闻公告类型
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)信息数组
	 * @return boolean
	 */
	public function add($ne_ids, $nca_id) {
		try {
			$this->__service->begin();

			$s_right = new voa_s_oa_news_right();
			$conds = array('ne_id' => $ne_ids);
			$this->__service->update_by_conds($conds, array('nca_id' => $nca_id));    //修改公告类型
			$s_right->update_by_conds($conds, array('nca_id' => $nca_id));  //修改right表中的冗余字段值

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();

			return $this->set_errmsg(voa_errcode_oa_news::DELETE_NEWS_FAILED);
		}
		return true;
	}


}
