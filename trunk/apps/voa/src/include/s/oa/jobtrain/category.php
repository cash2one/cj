<?php
/**
 * 培训/分类
 * $Author$
 * $Id$
 */

class voa_s_oa_jobtrain_category extends voa_s_abstract {

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		if ($this->_d_class == null) {
			$this->_d_class = new voa_d_oa_jobtrain_category();
		}
	}

	public function validator_title($title) {

		$title = trim($title);
		if (!validator::is_required($title)) {
			return voa_h_func::throw_errmsg(voa_errcode_oa_jobtrain::CATEGORY_TITLE_ERROR, $title);
		}
		return true;
	}
	/**
	 * 根据$id获取子分类和自己
	 * @param int $id
	 * @return arr
	 */
	public function list_by_id_pid($id){
		return $this->_d_class->list_by_id_pid($id);
	}
	/**
	 * 根据$id更新文章数量包括上级分类
	 * @param int $id
	 * @param int $article_count 文章数量
	 */
	public function update_article_num($id, $article_count){
		// 更新本分类数量
		$this->_d_class->update($id, array('article_num'=>$article_count));
		$cata = $this->_d_class->get($id);
		if($cata['pid']!=0){
			$s_article = new voa_d_oa_jobtrain_article();
			// 上级分类不是0则更新数量
			$cid_list = $this->_d_class->list_by_conds(array('pid'=>$cata['pid'], 'is_open'=>1));
			$cids = array_column($cid_list, 'id');
			$cids[] = $cata['pid'];
			// 统计所有文章数量
			$sum = $s_article->count_by_conds(array('cid'=>$cids, 'is_publish'=>1));
			$this->_d_class->update($cata['pid'], array('article_num'=>$sum));
		}
	}

}