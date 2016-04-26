<?php
/**
 * list.php
 * 内部api方法/超级报表---评论列表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_uda_frontend_superreport_listcomments extends voa_uda_frontend_superreport_abstract {

	/** 外部请求参数 */
	private $__request = array();
	/** 返回的结果 */
	private $__result = array();
	/** diy uda 类 */
	private $__diy = null;
	/** service 类 */
	private $__service = null;

	/**
	 * 初始化
	 * 引入  service 类
	 */
	public function __construct() {
		parent::__construct();

		if ($this->__service === null) {
			$this->__service = new voa_s_oa_superreport_comment();
		}
	}
	/**
	 * 根据条件查找记录,用于后台日报列表
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 */
	public function result( $page_option, &$result, $dr_id) {

		$result['list'] =  $this->_list_comments_by_dr_id($dr_id, $page_option);
		$result['total'] = $this->_count_comments_by_dr_id($dr_id);

		return true;
	}

	/**
	 * 根据条件查找目录
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @return array $list
	 */
	protected function _list_comments_by_dr_id($dr_id, $page_option) {

		$comments = array();
		$comments = $this->__service->list_comments_by_dr_id($dr_id, $page_option);
		if ($comments) {
			$uids = array_column($comments, 'm_uid');
			/** 评论用户头像信息 */
			$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
			$users = $servm->fetch_all_by_ids($uids);
			voa_h_user::push($users);
			foreach ($comments as &$comment) {
				$comment['username'] = $users[$comment['m_uid']]['m_username'];
				$comment['avatar'] = voa_h_user::avatar($comment['m_uid'], $users[$comment['m_uid']]);
			}
		}
		$comments = $this->__service->format_comments_list($comments);

		return $comments;
	}

	/**
	 * 根据条件计算日报数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_comments_by_dr_id($dr_id) {

		$total = $this->__service->count_comments_by_dr_id($dr_id);

		return $total;
	}

}
