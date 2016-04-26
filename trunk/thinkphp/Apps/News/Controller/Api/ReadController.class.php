<?php
/**
 * ReadController.class.php
 * xiaodingchen
 */

namespace News\Controller\Api;

class ReadController extends AbstractController {

	/**
	 * 未读人员列表接口
	 * get方式
	 * 参数：单一新闻公告ID
	 * 返回值：未读人员列表，姓名，头像和用户ID
	 */
	public function Non_reader_list_get() {

		// 判断新闻id是否合法
		$ne_id = I('get.ne_id', '', 'intval');
		// 当前页 默认第一页
		$page = I('get.page', 1, 'intval');

		if (!$ne_id) {
			$this->_set_error('_ERROR_ID_LEGAL');
			return false;
		}

		// 判断新闻是否存在
		$serv_news = D('News/News', 'Service');
		$news = $serv_news->get($ne_id);

		if (!$news) {
			$this->_set_error('_ERROR_NEWS_BEYOND');
			return false;
		}

		// 判断是否有查看权限
		$m_uid = $this->_login->user['m_uid'];
		if ($news['m_uid'] != $m_uid) {
			$this->_set_error('_ERROR_NO_PRIVILEGE');
			return false;
		}

		// 获取未读人员列表
		$serv_read = D('News/NewsRead', 'Service');
		$m_uids = $serv_read->un_read_list($ne_id);

		if (!$m_uids) {
			$this->_set_error('_ERROR_NON_READER_NULL');
			return false;
		}

		// 获取未读人员信息
		$serv_member = D('Common/Member', 'Service');
		$members = array();
		$users = array();
		$userinfo = array();
		$members = $serv_news->list_by_uids($m_uids);

		// 查看未读人员是否存在
		if (!$members) {
			$this->_set_error('_ERROR_NON_READER_NULL');
			return false;
		}

		// 处理数据，返回接口需要的值
		foreach ($members as $v) {
			$userinfo['m_uid'] = $v['m_uid'];
			$userinfo['m_username'] = $v['m_username'];
			$userinfo['m_face'] = $v['m_face'];
			$users[] = $userinfo;
		}

		list($start, $limit, $page) = page_limit($page, 10);

		// 总记录数
		$total = count($users);

		// 代替分页操作
		$users = array_slice($users, $start, $limit);

		$total = ceil($total/10) ? ceil($total/10) : 1;
		
		// 返回操作
		$this->_result = array(
			'page' => $page,
			'total' => $total,
			'last_send_time' => $news['send_no_time'],
			'users' => $users
		);

		return true;
	}
}
