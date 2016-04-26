<?php
/**
 *  新闻公告 点赞接口
 *  NewsLikeController
 *  User:Yinmengxuan
 *
 */

namespace News\Controller\Api;

class NewsLikeController extends AbstractController {

	public function Like_post(){

		// 当前新闻公告ID
		$ne_id = I('post.ne_id', '', 'intval');

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
		//插入点赞表数据
		$like_data = array();
		$like_data['ip'] = get_client_ip();
		$like_data['m_uid'] = $m_uid;
		$like_data['m_username'] = $this->_login->user['m_username'];
		$like_data['m_face'] = $this->_login->user['m_face'];
		$like_data['ne_id'] = $ne_id;

		// 新增点赞记录

		$like = D('News/NewsLike', 'Service');

		// 获取当前用户此篇文章 最近的一次点赞次数，false 则1  最后一次点赞时间，用于过滤
		$list = $like->list_by_conds(array(
			'm_uid'=>$like_data['m_uid'],
			'ne_id'=>$like_data['ne_id']),
			array(0,1),array('created'=>'DESC','like_id'=>'DESC')
		);
		if($list){
			$current = reset($list);// 第一条记录
			$new_des = $current['description'];
			$new_like_time = $current['created'];

			// 判断时间的合法性,时间为15秒
			// 判断 点赞 和 取消

			if($new_des == 1 && time()-$new_like_time <= 15 ){
				$this->_set_error('_ERROR_TIME_DES');
				return false;
			}
		}

		// 隔离前端私自构造数据
		if(!$list){
			$like_data['description'] = 1;
		}else {
			$like_data['description'] = $new_des;
		}

		// 更新 description 状态码
		$new_like_data = $like_data;
		$new_like_data['description'] = $like_data['description'] == 1 ? 2 : ($like_data['description'] == 2?1:1) ;

		$record = $like->insert($new_like_data);
		// news 主要的数据存储 num_like
		$ne_id = $like_data['ne_id'];
		// 获取相对先钱的初始 点赞状态
		$description = $like_data['description'];

		// 获取当前最新点赞次数
		$news_data = $news->get($ne_id);
		$news_data['num_like'] = intval($news_data['num_like']);

		$data = array();
		switch ($description) {
			case '1': // 点赞
				$data['num_like'] = $news_data['num_like'] + 1;
				break;
			case '2': //取消点赞
				$data['num_like'] = $news_data['num_like'] - 1;
				break;
			default:
				// 没必要修改
				break;
		}
		// save 数据
		$news->update($ne_id,$data);

		return true;
	}

}
