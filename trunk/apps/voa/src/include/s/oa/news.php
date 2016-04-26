<?php
/**
 * 新闻公告
 * $Author$
 * $Id$
 */

class voa_s_oa_news extends voa_s_abstract {

	protected $_d_class = null;
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_news();
		}
	}

	/**
	 * 根据主键获取信息
	 * @param $id
	 * @return Ambigous
	 * @throws service_exception
	 */
	public function get($id) {
		return $this->_d_class->get($id);
	}

	/**
	 * 验证用户ID
	 * @param int $m_uid
	 * @return boolean
	 */
	public function validator_m_uid($m_uid){
		if ($m_uid < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::M_UID_ERROR, $m_uid);
		}
		return true;
	}

	/**
	 * 验证公告分类ID
	 * @param int $nca_id
	 * @return boolean
	 */
	public function validator_nca_id($nca_id){
		if ($nca_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NCA_ID_ERROR, $nca_id);
		}
		return true;
	}

	/**
	 * 验证页码
	 * @param int $page
	 * @return boolean
	 */
	public function validator_page($page){
		if ($page < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::PAGE_ERROR, $page);
		}
		return true;
	}

	/**
	 * 验证分页数
	 * @param int $limit
	 * @return boolean
	 */
	public function validator_limit($limit){
		if ($limit < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::LIMIT_ERROR, $limit);
		}
		return true;
	}
	/**
	 * 验证公告ID
	 * @param int $ne_id
	 * @return boolean
	 */
	public function validator_ne_id($ne_id){
		if ($ne_id < 1) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::NE_ID_ERROR, $ne_id);
		}
		return true;
	}
	/**
	 * 验证封面ID
	 * @param int $cover_id
	 * @return boolean
	 */
	public function validator_cover_id($cover_id){
		if (!is_numeric($cover_id)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::COVER_ID_ERROR, $cover_id);
		}
		return true;
	}
	/**
	 * 验证用户ID数组
	 * @param array $uids
	 * @return boolean
	 */
	public function validator_uids($uids){
		if (!is_array($uids)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::U_IDS_ERROR, $uids);
		}
		return true;
	}
	/**
	 * 验证部门ID数组
	 * @param array $uids
	 * @return boolean
	 */
	public function validator_cdids($cdids){
		if (!is_array($cdids)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::CD_IDS_ERROR, $cdids);
		}
		return true;
	}

	/**
	 * 验证用户id
	 * @param unknown $uids
	 * @return false|boolean
	 */
	public function validotor_m_uids_check($uids) {
		if (!is_array($uids)) {  //验证是否合法
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::M_UID_CHECK, $uids);
		}
		return true;
	}

	/**
	 * 验证标题基本合法性
	 * @param string $title
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_title($title) {

		$title = trim($title);
		if (!validator::is_required($title)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::TITLE_ERROR, $title);
		}

		return true;
	}
	/**
	 * 验证内容基本合法性
	 * @param string $content
	 * @throws Exception
	 * @return boolean
	 */
	public function validator_content($content) {

		$content = trim($content);
		if (!validator::is_required($content)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_news::CONTENT_ERROR, $content);
		}

		return true;
	}
}
