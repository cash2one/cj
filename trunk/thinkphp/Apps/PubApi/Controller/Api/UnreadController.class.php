<?php
/**
 * UnreadController.class.php
 * $author$
 */
namespace PubApi\Controller\Api;

class UnreadController extends AbstractController {

	public function Unread_num() {

		$uid = $this->_login->user['m_uid'];
		$plugin = D('PubApi/Comment', 'Service');

		$event_count = 0;
		if ($plugin->avaiable_plugin('activity')) {
			// 活动的未读数
			$serv_e = D('Event/Event', 'Service');
			$event_count = $serv_e->get_count_by_uid($uid);
		}

		$cnvote_count = 0;
		if ($plugin->avaiable_plugin('nvote')) {
			// 投票的未读数
			$serv_c = D('Cnvote/Cnvote', 'Service');
			$cnvote_count = $serv_c->get_count_by_uid($uid);
		}

		// 返回数据
		$this->_result = array(
			'event_count' => (int)$event_count,
			'cnvote_count' => (int)$cnvote_count
		);

		return true;
	}

}
