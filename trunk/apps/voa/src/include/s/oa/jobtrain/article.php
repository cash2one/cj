<?php
/**
 * 培训/文章
 * $Author$
 * $Id$
 */

class voa_s_oa_jobtrain_article extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_jobtrain_article();
		}
	}

	public function validator_cid($cid){
		if ($cid < 1) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::ARTICLE_CID_ERROR, $cid);
		}
		return true;
	}

	public function validator_title($title) {
		$title = trim($title);
		if (!validator::is_required($title)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::ARTICLE_TITLE_ERROR, $title);
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
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::ARTICLE_CONTENT_ERROR, $content);
		}

		return true;
	}
	/**
	 * 获取学习总人数
	 * @param arr $cata
	 * @return int
	 */
	public function get_study_sum($cata) {
		$serv_md = new voa_s_oa_member_department();
		$serv_member = new voa_s_oa_member();
		//$members = $serv_member->fetch_all_by_ids();
		$num = 0;
		if($cata['is_all']){
			$num = $serv_member->count_all();
			return $num;
		}
		$cd_ids = explode(',', $cata['cd_ids']);
		if(!empty($cata['m_uids'])) {
			$m_uids = explode(',', $cata['m_uids']);
			foreach ($m_uids as $v) {
				// 获取用户所在部门
				$m_cd_ids = $serv_md->fetch_all_by_uid($v);
				// 检查用户是否在部门中
				$is_in = 0;
				foreach ($m_cd_ids as $m) {
					if(in_array($m, $cd_ids)) {
						$is_in = 1;
						break;
					}
				}
				// 不在所选部门则人数加1
				if(!$is_in){
					$num++;
				}
			}
		}
		// 加上部门下属人员总数
		$num += $serv_md->count_by_cdid($cd_ids);
		return $num;
	}


}