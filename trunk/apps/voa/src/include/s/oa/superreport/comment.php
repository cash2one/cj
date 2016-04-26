<?php
/**
 * comment.php
 * service/超级报表/评论
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_s_oa_superreport_comment extends voa_s_oa_superreport_abstract {



	public function __construct() {
		parent::__construct();
		$this->_d_class = new voa_d_oa_superreport_comment();
	}

	/**
	 * 根据报表ID读取评论
	 * @param number $dr_id 报表ID
	 * @param array $page_option 分页选项
	 * @return array
	 */
	public function list_comments_by_dr_id($dr_id, $page_option) {

		$list = $this->_d_class->list_by_conds(array('dr_id' => $dr_id), $page_option, array('created' => 'DESC'));

		return $list;
	}

	/**
	 * 根据报表ID读取评论数量
	 * @param number $dr_id 报表ID
	 * @return array
	 */
	public function count_comments_by_dr_id($dr_id) {

		$total = $this->_d_class->count_by_conds(array('dr_id' => $dr_id));

		return $total;
	}

	/**
	 * 格式化评论列表
	 * @param array $list 评论列表
	 * @return array
	 */
	public  function format_comments_list($list) {
		$result = array();
		if ($list) {
			foreach ($list as $k => $v) {
				$result[$k]['uid'] = $v['m_uid'];
				$result[$k]['username'] = $v['username'];
				$result[$k]['avatar'] = $v['avatar'];
				$result[$k]['created'] = $v['created'];
				$result[$k]['created_u'] = rgmdate($v['created'], 'Y-m-d H:i:s');
				$result[$k]['comment'] = rhtmlspecialchars($v['comment']);
			}
		}

		return array_values($result);
	}

	/**
	 * 验证用户ID的基本合法性
	 * @param number $m_uid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_uid($m_uid) {

		if (!validator::is_required($m_uid)) {  //验证是否为空
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::UID_BLANK, $m_uid);
		}

		if ($m_uid < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::UID_ERROR, $m_uid);
		}

		return true;
	}

	/**
	 * 验证报表ID的基本合法性
	 * @param number $m_uid
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_dr_id($dr_id) {

		if (!validator::is_required($dr_id)) {  //验证是否为空
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::SID_BLANK, $dr_id);
		}

		if ($dr_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::SID_ERROR, $dr_id);
		}

		return true;
	}

	/**
	 * 验证评论基本合法性
	 * @param string $comment
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_comment($comment) {

		$comment = trim($comment);
		if (!validator::is_required($comment)) {
			return voa_h_func::throw_errmsg(voa_errcode_api_superreport::COMMENT_ERROR, $comment);
		}

		return true;
	}

}
