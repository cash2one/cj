<?php
/**
 * NewsController.class.php
 * $author$
 */

namespace News\Controller\Api;

use Common\Common\WxqyMsg;

class NewsController extends AbstractController {

	/** 发布/未发布 */
	const PUBLISH = 1;
	const UNPUBLISH = 0;
	/** 是否全部人员 */
	const IS_ALL = 1;
	const IS_NOT_ALL = 0;
	/** 是否需要审核 */
	const IS_NOT_CHECK = 0;
	/** 是否保密 */
	const IS_SECRET = 1;
	/** 是否推送 */
	const IS_PUSH = 1;
	const IS_NOT_PUSH = 0;
	/** 是否是发布人 */
	const IS_RELEASE = 1;
	const IS_NOT_RELEASE = 0;
	public $act = array(
		0 => 'preview',
		1 => 'view',
	);

	/**
	 * 公告列表
	 */
	public function News_list_get() {
		$nca_id = I('get.nca_id', '', 'intval'); // 分类ID
		$page = I('get.page', 1, 'intval'); // 当前页 默认第一页
		$keyword = I('get.keyword', '');  // 当前搜索关键字

		// 如果公告分类未输入
		if (empty($nca_id)) {
			$this->_set_error('_ERROR_NCA_ID_NULL');

			return false;
		}

		// 当前用户
		$m_uid = $this->_login->user['m_uid'];

		list($start, $limit, $page) = page_limit($page, 10);

		// 查询条件
		$conds = array(
			'nca_id' => $nca_id,
			'm_uid' => $m_uid,
			'keyword' => $keyword,
		);

		$ser_news = D('News/News', 'Service');
		list($list, $total) = $ser_news->list_my_news($conds, array($start, $limit));
		if ($page == 1) {
			$check = $ser_news->list_by_ne_id_check($m_uid, $nca_id, $keyword);
			$list = array_merge($check, $list);
		}
		foreach($list as &$_act){
			if($_act['is_publish'] == self::IS_NOT_PUSH){
				$_act['published'] = $_act['created'];
			}
			$_act['action'] = $this->act[$_act['is_publish']];
		}

		$total = ceil($total / 10) ? ceil($total / 10) : 1;
		// 返回数据
		$this->_result = array(
			'page' => $page,
			'total' => $total,
			'list' => $list,
		);

		return true;
	}

	/**
	 * 新闻公告详情
	 * @return bool
	 */
	public function News_detail_get() {

		$ne_id = I('get.ne_id', '', 'intval');
		$action = I('get.action', 'view', 'htmlspecialchars');
		$action = empty($action) ? 'view' : $action;

		// 参数不合法
		if (empty($ne_id)) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}
		//返回当前域名
		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$domain = $sets['domain'];
		// 当前用户
		$m_uid = $this->_login->user['m_uid'];

		$serv_news = D('News/News', 'Service');
		// 根据当前动作设置公告状态
		if ($action == 'view') {
			$is_publish = 1;
			// 查询单条公告详情
			$detail = $serv_news->get_by_ne_id($ne_id, $is_publish);
		} elseif ($action == 'preview') {
			$is_publish = 0;
			// 查询单条公告详情

			$details = $serv_news->get_by_ne_id($ne_id, 1);
			if (!empty($details)) {
				// 该公告已经发布
				$this->_set_error('_ERROR_NEWS_SEND');
				return false;
			} else {
				$detail = $serv_news->get_by_ne_id($ne_id, $is_publish);
			}
		}

		// 如果公告不存在
		if (empty($detail)) {
			$this->_set_error('_ERROR_NEWS_BEYOND');
			return false;
		}

		// 只有在正式浏览时候才判断权限
		if ($action == 'view') {
			// 判断当前用户是否有权限阅读
			if (!$serv_news->issure($ne_id, $m_uid, $detail['is_all'])) {
				$this->_set_error('_ERROR_NO_READ');
				return false;
			}
		}

		// 格式化返回数据
		$serv_news->format_detail($detail, $m_uid);

		// 判断当前动作是否合法 如果是预览 则不更新阅读数
		if ($action == 'preview') {
			// 判断当前审核人员是否
			$serv_check = D('News/NewsCheck', 'Service');
			$did_result = $serv_check->did_show($ne_id, $m_uid);

			$detail['check_note'] = $did_result['check_note'] ? $did_result['check_note'] : '';
			$detail['action'] = 'preview';
		} elseif ($action == 'view') {

			$serv_read = D('News/NewsRead', 'Service');

			// 插入阅读记录
			if ($serv_read->insert_data($ne_id, $m_uid)) {
				// 更新阅读数
				$update_data['read_num'] = $detail['read_num'] + 1;
				$serv_news->update($ne_id, $update_data);
			}

			$detail['action'] = 'view';

		} else {
			$this->_set_error('_ERROR_NO_ACTION');

			return false;
		}

		// 获取未读人员列表
		$serv_read = D('News/NewsRead', 'Service');
		$m_uids = $serv_read->un_read_list($ne_id);
		// 去除空数组
		$m_uids = array_filter($m_uids);

		// 未读人数
		$detail['unread_nums'] = count($m_uids);

		//判断是否是草稿
		if ($action == 'preview') {
			$reply_list = array();
			//获取回复列表
			$serv_check = D('News/NewsCheck', 'Service');
			$conds_check['news_id'] = $ne_id;
			$reply_list = $serv_check->list_by_conds($conds_check);

			if (!empty($reply_list)) {
				foreach($reply_list as $k_empty => $_empty){
					if(empty($_empty['check_note'])){
						unset($reply_list[$k_empty]);
					}
				}
				if(!empty($reply_list)){
					//获取人员id
					$mem_id = array_column($reply_list, 'm_uid');
					$serv_mem = D('Common/Member', 'Service');
					$conds_uid['m_uid'] = $mem_id;
					$info_mem = $serv_mem->list_by_conds($conds_uid);
					//以id做键
					foreach($info_mem as $_minfo){
						$info_list[$_minfo['m_uid']] = $_minfo;
					}
					//格式人名
					foreach ($reply_list as &$_info) {
						$_info['m_username'] = $info_list[$_info['m_uid']]['m_username'];
						$_info['m_face'] = $info_list[$_info['m_uid']]['m_face'];
					}
				}
			}
			$detail['reply_list'] = $reply_list;
			// 是否是发布人身份
			$detail['maker'] = self::IS_NOT_RELEASE;
			if ($detail['m_uid'] == $m_uid) {
				$detail['maker'] = self::IS_RELEASE;
			}
		}
		$detail['domain'] = $domain;
		// 返回数组
		$this->_result = $detail;

		return true;
	}

	/**
	 * 发布公告
	 */
	public function News_release_post() {

		$ne_id = I('post.ne_id', '', 'intval'); // 公告ID

		// 判断权限
		$this->_draft_execute($ne_id, $new);

		// 获取未读人员列表
		$serv_read = D('News/NewsRead', 'Service');
		$unread_uids = $serv_read->un_read_list($ne_id);

		// 更改为发送状态
		$updated = array(
			'published' => NOW_TIME,
			'is_publish' => self::PUBLISH,
			'is_check' => self::IS_NOT_CHECK,
		);
		$serv_news = D('News/News', 'Service');
		$serv_news->update_by_conds(array('ne_id' => $ne_id), $updated);

		// 发送消息
		if (!empty($unread_uids) && $new['is_message'] == self::IS_PUSH) {
			$msg = New Wxqymsg();
			$url = $this->_view_url($ne_id);
			if ($new['is_secret'] == self::IS_SECRET) {
				$new['title'] .= '[保密]';
			}
			if (!empty($new['summary'])) {
				$new['summary'] = '摘要：' . rhtmlspecialchars($new['summary']);
			}
			$picurl = !empty($new['cover_id']) ? cfg('PROTOCAL') . $this->_setting['domain'] . '/attachment/read/' . $new['cover_id'] : '';
			$msg->send_news($new['title'], $new['summary'], $url, $unread_uids, '', $picurl, cfg('AGENT_ID'), cfg('PLUGIN_ID'));
		}

		return true;
	}

	/**
	 * 草稿方法的前置操作
	 * @param int   $ne_id 公告ID
	 * @param array $news 公告详情
	 * @return bool
	 */
	protected function _draft_execute($ne_id, &$news) {

		// 获取新闻公告主题数据
		$serv_news = D('News/News', 'Service');
		$news = $serv_news->get_by_ne_id($ne_id, self::UNPUBLISH);
		if (empty($news)) {
			E('_ERROR_NEWS_BEYOND');

			return false;
		}

		// 判断是否有权限
		if ($news['m_uid'] != $this->_login->user['m_uid']) {
			E('_ERROR_NO_PRIVILEGE');

			return false;
		}
		// 判断发布权限
		$serv_setting = D('News/NewsSetting', 'Service');
		if (!$serv_setting->issue($this->_login->user['m_uid'])) {
			E('_ERROR_NO_SEND');

			return false;
		};
		// 判断是否已经发布
		if (!empty($news['published'])) {
			E('_ERROR_NEWS_SEND');

			return false;
		}

		return true;
	}
}
