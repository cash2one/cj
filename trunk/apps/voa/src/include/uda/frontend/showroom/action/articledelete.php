<?php
/**
 * voa_uda_frontend_showroom_action_articledelete
 * 统一数据访问/陈列/删除文章
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_showroom_action_articledelete extends voa_uda_frontend_showroom_abstract {

	/**
	 * 根据条件删除文章
	 * @param array $ids 文章ID数组
	 */
	public function delete($ids) {
		$article = new voa_d_oa_showroom_article();
		$article_content = new voa_d_oa_showroom_articlecontent();
		$article_search = new voa_d_oa_showroom_articlesearch();
		$article_right = new voa_d_oa_showroom_articleright();
		$article_member = new voa_d_oa_showroom_articlemember();
		$category = new voa_d_oa_showroom_category();

		//删除文章、文章内容、文章权限、文章搜索
		try {
			$article->beginTransaction();

			$conds = array(
				'ta_id' => $ids
			);
			//每删除一篇文章，则相应目录的文章数量-1
			foreach ($ids as $id) {
				$article_row = $article->get($id);
				$category->update($article_row['tc_id'], array('article_num=article_num-?' => 1));
			}

			$article->delete($ids);  		                  //删除文章
			$article_content->delete_by_conds($conds);        //删除文章内容
			$article_search->delete_by_conds($conds);         //删除文章搜索
			$article_right->delete_real_by_article_ids($ids); //物理删除文章权限
			$article_member->delete_real_by_article_ids($ids); //物理删除文章阅读情况

			$article->commit();
		} catch (Exception $e) {
			$article->rollBack();

			return $this->set_errmsg(voa_errcode_oa_showroom::DELETE_ARTICLE_FAILED);
		}
		return true;
	}

}
