<?php
/**
 *  新闻公告 评论接口
 *  NewsCommentService
 *  User:Yinmengxuan
 *
 */

namespace News\Service;
use Common\Common\User;

class NewsCommentService extends AbstractService {

	protected $_news_model;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("News/NewsComment");
		$this->_news_model = D('News/News');
	}

	/**
	 * 格式化一条数据
	 *
	 * @param array $comment 评论数据
	 */
	public function format_one($comment) {

		$result = array();
		if (empty($comment)) {
			return false;
		}

		$result['m_uid'] = $comment['m_uid'];
		$result['content'] = nl2br(rhtmlspecialchars($comment['content']));
		$result['m_username'] = rhtmlspecialchars($comment['m_username']);
		$result['m_face'] = User::instance()->avatar($comment['m_uid']);
		$result['p_username'] = $comment['p_username'];

		return $result;
	}

	/**
	 * 格式化评论列表
	 *
	 * @param $data
	 * @return array
	 */
	public function format_comment($data) {

		// 如果为空
		$result = array();
		if (empty($data)) {
			return $result;
		}

		// 返回特定的数据格式
		foreach ($data as $_k => $_v) {
			$result[$_k]['ncomm_id'] = $_v['ncomm_id'];
			$result[$_k]['ne_id'] = $_v['ne_id'];
			$result[$_k]['p_username'] = $_v['p_username'];
			$result[$_k]['m_username'] = $_v['m_username'];
			$result[$_k]['content'] = $_v['content'];
			$result[$_k]['_create'] = $_v['created'];
			$result[$_k]['m_face'] = User::instance()->avatar($_v['m_uid']);
			$result[$_k]['m_uid'] = $_v['m_uid'];
		}

		// 返回数据
		return $result;
	}

}
