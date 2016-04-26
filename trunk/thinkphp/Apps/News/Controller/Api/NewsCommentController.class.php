<?php
/**
 *  新闻公告 评论接口
 *  NewsCommentController
 *  User:Yinmengxuan
 *
 */

namespace News\Controller\Api;
use Common\Common\Wxqy\Service;

class NewsCommentController extends AbstractController {

	/**
	 * 新闻评论
	 */
	public function Comment_post(){

		$ne_id = I('post.ne_id', '', 'intval'); // 当前新闻公告ID
		$content = I('post.content', '', 'htmlspecialchars'); //评论的内容
		$p_uid = I('post.p_uid', '', 'intval'); //回复者的uid
		$p_username = I('post.p_username' ,''); //当前回复的上一级的姓名

		// 判断参数是否合法
		if (empty($ne_id)) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}

		// 判断新闻是否存在
		$news = D('News/News', 'Service');
		$new = $news->get($ne_id);

		if (!$new) {

			$this->_set_error('_ERROR_NEWS_BEYOND');
			return false;
		}

		//当前登录用户
		$m_uid = $this->_login->user['m_uid'];

		// 添加评论
		$comment = array(
			'content' => $content,
			'm_uid' => $m_uid,
			'ne_id' => $ne_id,
			'm_username' => $this->_login->user['m_username'],
		);

		// 判断当前回复的上一级作者
		if (!empty($p_username)) {
			$comment['p_username'] =$p_username;
		} else {
			$comment['p_username'] = '';
		}

		$news_comment = D('News/NewsComment', 'Service');

		//评论数据入库操作
		$record = $news_comment->insert($comment);

		// 如果是顶级则不回复
		if (!empty($p_uid)) {
			// 发送消息
			$this->send_comment($p_uid, $ne_id, $content);
		}


		//数据格式化
		$result = $news_comment->format_one($comment);

		// 输出结果
		$this->_result = array(
			'comment' => $result
		);

		return true;

	}

	/**
	 * 新闻评论列表
	 */
	public function Commentlist_get(){

		// 当前新闻公告ID
		$ne_id = I('get.ne_id', '', 'intval');
		$page = I('get.page', 1, 'intval');

		// 判断参数是否合法
		if (empty($ne_id)) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}

		// 判断新闻是否存在
		$news = D('News/News', 'Service');
		$new = $news->get($ne_id);

		if (!$new) {
			$this->_set_error('_ERROR_NEWS_BEYOND');
			return false;
		}

		list($start, $limit, $page) = page_limit($page, 10);

		//  获取评论列表
		$news_comment = D('News/NewsComment', 'Service');
		$result = array();

		// 查询条件
		$conds = array('ne_id' => $ne_id);

		// 查询评论列表
		$comment_list = $news_comment->list_by_conds($conds, array($start, $limit), array('created' => 'DESC'));

		// 获取评论的数目
		$count = count($comment_list);

		// 格式化评论列表
		$result = $news_comment->format_comment($comment_list);
		// 总数
		$total = $news_comment->count_by_conds($conds);
		$totals = ceil($total/10) ? ceil($total/10) : 1;

		// 输出结果
		$this->_result = array(
			'page' => $page,
			'total' => $totals,
			'count' => $total,
			'list' => $result
		);

	}


	/**
	 * 评论回复 发给公告作者
	 * @param $p_uid
	 * @param $ne_id
	 * @param $content
	 */
	protected function send_comment($p_uid, $ne_id, $content) {

		$serv_news = D('News/News', 'Service');

		$detail = $serv_news->get_uid_by_ne_id($ne_id);

		$author = $serv_news->get_by_uid($p_uid);

		// 当然回复人的m_username
		$username = $this->_login->user['m_username'];

		// 消息描述
		$description =  "标题：【{$detail['title']}】".
			"\n来自：{$username}".
			"\n回复：".$content;

		$url = $this->_view_url($ne_id);

		// 数据组装
		$data = array(
			'title' => '您收到一条评论回复',
			'description' => $description,
			'url' => $url,
			'picurl' => ''
		);

		$to_users = array($author['m_openid']);

		$post = &Service::instance();

		// 插件应用信息
		$plugins = $this->get_plugin_id('news');

		// 发送消息
		$post->post_news($data, $plugins['cp_agentid'], $to_users);
	}

	/**
	 * 详情URL地址
	 * @param $ne_id
	 * @return string
	 */
	protected function _view_url($ne_id) {

		// 插件应用信息
		$plugins = $this->get_plugin_id('news');

		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];

		$pluginid = cfg('PLUGIN_ID');
		$url = $face_base_url . "/frontend/news/view?newsId=".$ne_id."&action=view&pluginid=".$pluginid;

		return $url;
	}
}
