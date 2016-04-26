<?php
/**
 * 新闻公告/分类
 * $Author$
 * $Id$
 */

class voa_s_oa_news_category extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 验证标题基本合法性
	 * @param string $name
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_name($name) {

		$name = trim($name);
		if (!validator::is_required($name)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::CATEGORY_NAME_ERROR, $name);
		}

		return true;
	}

	/**
	 * 验证排序ID
	 * @param int $orderid
	 * @return boolean
	 */
	public function validator_orderid($orderid){
		if ($orderid < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::ORDERID_ERROR, $orderid);
		}
		return true;
	}
}
