<?php
/**
 * voa_uda_frontend_train_action_articleview
 * 统一数据访问/培训/文章查看
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_train_action_articleview extends voa_uda_frontend_train_abstract {

	/**
	 * 根据条件文章ID查找文章及文章内容
	 * @param int $id  文章ID
	 * @return array  $article
	 */
	public function view( $id, $m_uid = 0 ) {

		$d_article = new voa_d_oa_train_article();
		$d_category = new voa_d_oa_train_category();
		$article_member = new voa_d_oa_train_articlemember();
		$article =  $d_article->get($id);
		if($article) { //如果根据ID能查找到文章

			//取得文章内容
			$article['content'] = $this->_get_article_content($id);
			$category = $d_category->get($article['tc_id']);
			$article['tc_name'] = $category['title'];

			//取得有查看权限的部门和人员
			$this->_get_rights($id, $article);

			//供前台接口使用，前台查看文章，才标注为已读
			if ($m_uid) {
				//将文章标记为已读
				$conds = array('ta_id' => $id, 'm_uid' => $m_uid);
				$read = $article_member->get_by_conds($conds);
				if (!$read) { //如果没有阅读记录，则插入阅读记录
					$data = array('ta_id' => $id, 'm_uid' => $m_uid, 'read_time' => time());
					$article_member->insert($data);
				}
			}
		}

		return $article;
	}

	/**
	 * 根据条件文章ID查找文章内容
	 * @param int $id  文章ID
	 * @return string   $content 文章内容
	 */
	protected  function _get_article_content($id) {

		$d_article_content = new voa_d_oa_train_articlecontent();
		$article_content = $d_article_content->get_by_conds(array('ta_id'=>$id));
		if ($article_content) { //如果根据文章ID能查找到文章内容
			$content = $article_content['content'];
		}else{ //如果没有则返回空
			$content = '';
		}

		 return $content;
	}

	/**
	 * 根据条件文章ID取回文章权限
	 * @param int $id  文章ID
	 * @param array   $content 文章内容（引用）
	 */
	protected function _get_rights($id, &$article) {

		$d_article_right = new voa_d_oa_train_articleright();
		$article_rights = $d_article_right->list_by_conds(array('ta_id'=>$id));
		if ($article_rights) {
			foreach ($article_rights as $right) {
				$article['is_all'] = voa_d_oa_train_articleright::IS_ALL;
				if ($right['m_uid'] != 0 && $right['cd_id'] == 0) {//人员
					$article['contacts'][] = $right['m_uid'];
					$article['is_all'] = voa_d_oa_train_articleright::NOT_IS_ALL;
				}
				if ($right['m_uid'] == 0 && $right['cd_id'] != 0) {//部门
					$article['deps'][] = $right['cd_id'];
					$article['is_all'] = voa_d_oa_train_articleright::NOT_IS_ALL;
				}
			}
		}
	}
}
