<?php
/**
 *  新闻公告 点赞接口
 *  NewsLikeService
 *  User:Yinmengxuan
 *
 */

namespace  News\Service;

class NewsLikeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("News/NewsLike");
	}
}
