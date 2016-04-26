<?php
/**
 * GuestbookController.class.php
 * $author$
 */

namespace Guestbook\Controller\Api;

class GuestbookController extends AbstractController {

	// 获取留言列表
	public function List_get() {

		// 分页信息
		$page = I('get.' . cfg('var_page'));
		list($start, $limit, $page) = page_limit($page, $this->_plugin->setting['perpage']);

		// 读取列表
		$serv_gb = D('Guestbook/Guestbook', 'Service');
		$list = $serv_gb->list_all(array($start, $limit), array('id' => 'ASC'));

		// 格式化
		$serv_fmt = D('Guestbook/Format', 'Service');
		foreach ($list as &$_v) {
			$serv_fmt->guestbook($_v);
		}

		unset($_v);

		// 统计总数
		$count = $serv_gb->count();

		return $this->_response(array(
			'total' => $count,
			'limit' => $limit,
			'list' => $list
		));
	}

	// 新增留言
	public function Message_post() {

		// 留言信息
		$guestbook = array();
		// 用户提交的参数
		$params = I('request.');
		// 非用户提交的扩展参数
		$extend = array(
			'uid' => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);
		// 如果新增操作失败
		$serv_gb = D('Guestbook/Guestbook', 'Service');
		if (!$serv_gb->add($guestbook, $params, $extend)) {
			$this->_set_error($serv_gb->get_errmsg(), $serv_gb->get_errcode());
			return false;
		}

		// 格式化
		$serv_fmt = D('Guestbook/Format', 'Service');
		$serv_fmt->guestbook($guestbook);

		$this->_result = $guestbook;
		return true;
	}

}
