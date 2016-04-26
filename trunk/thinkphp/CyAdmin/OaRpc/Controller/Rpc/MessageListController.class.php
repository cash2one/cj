<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/27
 * Time: 16:46
 */

namespace OaRpc\Controller\Rpc;

class MessageListController extends AbstractController {

	protected $_msg_log_ser = null; // 消息记录ser
	protected $_msg_read = null; // 已读消息ser
	// 初始化
	public function _initialize() {

		$this->_msg_log_ser = D('EnterpriseMessage', 'Service'); // 消息记录
		$this->_msg_read = D('EnterMessageRead', 'Service'); // 已读消息
	}



	public function get_old_message_list($page, $uid, $ep_id) {

		//获取已读的消息数据
		$read_data = $this->_msg_read->list_by_conds($ep_id, $uid);
		if ($read_data) {
			$re_array = array_column( $read_data, 'logid' ); // 消息id
		} else $re_array = array();
		// 获取总数
		$real_count = $this->_get_yd_count($ep_id, $re_array);
		//每页显示条数
		$perpage = 12;
		if( $real_count > 0 ) {
			$page_start = $perpage * ($page - 1);
			$limit = $perpage;

			$msg_list = $this->_get_old_list($ep_id, $re_array, $page_start, $limit);

			// 返回生产数据
			return array(
				'data_list' => $msg_list,
				'total' => $real_count,
				'page' => $page
			);

		}
		return false;

	}


	/**
	 * 获取已读分页数据
	 * @param $conditions
	 * @param $page
	 * @param $ep_id
	 * @param $uid
	 * @return array
	 */
	public function get_message_list($conditions, $page, $ep_id, $uid) {

		// 获取已读的消息数据
		$read_data = $this->_msg_read->list_by_conds($ep_id, $uid);
		if ($read_data) {
			$re_array = array_column( $read_data, 'logid' ); // 消息id
		} else $re_array = array();
		// 获取真正的总数
		$real_count = $this->_get_real_count($ep_id, $re_array);
		//每页显示条数
		$perpage = 12;

		if( $real_count > 0 ) {
			$pagerOptions = array(
				'total_items' => $real_count,
				'per_page' => $perpage,
				'current_page' => $page,
				'show_total_items' => true,
			);

			$page_start = $perpage * ($page - 1);
			$limit = $perpage;
			$orderby['created'] = 'DESC';

			$msg_list = $this->_get_real_list($ep_id, $re_array, $page_start, $limit, $orderby);

			// 返回生产数据
			return array(
				'data_list' => $msg_list,
				'total' => $real_count,
				'page' => $page
			);

		}

	}

	/**
	 * 获取未读分页的数据
	 * @param $ep_id
	 * @param $re_array
	 * @param $page_start
	 * @param $limit
	 * @param $orderby
	 * @return mixed
	 */
	protected function _get_real_list($ep_id, $re_array, $page_start, $limit, $orderby) {

		return $this->_msg_log_ser->get_real_list($ep_id, $re_array, $page_start, $limit, $orderby);
	}


	/**
	 * 获取已读分页数据
	 * @param $ep_id
	 * @param $re_array
	 * @param $page_start
	 * @param $limit
	 * @return mixed
	 */
	protected function _get_old_list($ep_id, $re_array, $page_start, $limit) {

		return $this->_msg_log_ser->get_old_list($ep_id, $re_array, $page_start, $limit);
	}



	/**
	 * @description 获取真正的总数
	 * @param $ep_id
	 * @param $re_array
	 * @return mixed
	 */
	protected function _get_real_count($ep_id, $re_array) {

		return $this->_msg_log_ser->get_real_count($ep_id, $re_array);
	}

	/**
	 * 获取已读数据
	 * @param $ep_id
	 * @param $re_array
	 * @return mixed
	 */
	protected  function _get_yd_count($ep_id, $re_array) {

		return $this->_msg_log_ser->get_yd_count($ep_id, $re_array);
	}

}