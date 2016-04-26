<?php
/**
 * voa_uda_frontend_train_action_articledit
 * 统一数据访问/培训/编辑文章
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_train_action_articleedit extends voa_uda_frontend_train_abstract {

	/**
	 * 新增/编辑文章
	 * @param int $id 文章ID
	 * @param array $data 文章数据
	 */
	public function edit($id, &$data) {

		$uda_validator = &uda::factory('voa_uda_frontend_train_validator');
		if (!$uda_validator->article_title($data['title'])) {  //检查标题长度是否合法
			return false;
		}
		if (!$uda_validator->article_author($data['author'])) {  //检查作者名字长度是否合法
			return false;
		}

		$article = new voa_d_oa_train_article();
		$category = new voa_d_oa_train_category();
		$article_right = new voa_d_oa_train_articleright();
		$article_content = new voa_d_oa_train_articlecontent();
		$article_search = new voa_d_oa_train_articlesearch();
		$article_member = new voa_d_oa_train_articlemember();
		if ($id) { //编辑文章
			$article->beginTransaction();
			try{
				$update_data = array('tc_id' => $data['tc_id'], 'title' => $data['title'], 'author' => $data['author']);
				$article->update($id, $update_data);//编辑文章
				$article_right->delete_real_by_article_id($id);	//将现有权限删除，以便后面统一新增
				$article_content->update_by_article_id($id, array('content' => $data['content']));//编辑内容
				$article_search->update_by_article_id($id, $this->_parseDataToSearch($id, $data));//编辑搜索
				$article_member->delete_real_by_article_id($id); //物理删除文章阅读情况
				$this->_addRights($id, $article_right, $data);//新增权限
				$article->commit();
			} catch (Exception $e) {
				$article->rollBack();

				return $this->set_errmsg(voa_errcode_oa_train::EDIT_ARTICLE_FAILED);
			}

		} else {  //新增文章
			$article->beginTransaction();
			try{
				$article_data = array('title' => $data['title'], 'author' => $data['author'], 'tc_id' => $data['tc_id']);
				$article->insert( $article_data );//新增文章
				$id = $article->getLastInsertId();
				$insert_data = array('ta_id'=> $id, 'content' => $data['content']);
				$article_content->insert($insert_data);//新增内容
				$search_data = $this->_parseDataToSearch($id, $data);
				$article_search->insert($search_data);//新增搜索
				$this->_addRights($id, $article_right, $data);//新增权限
				//目录的文章数量+1
				$article_row = $article->get($id);
				$category->update($article_row['tc_id'], array('article_num=article_num+?' => 1));
				$article->commit();
			} catch (Exception $e) {
				$article->rollBack();

				return $this->set_errmsg(voa_errcode_oa_train::ADD_ARTICLE_FAILED);
			}
		}
		$data['ta_id'] = $id;

		return true;
	}

	/**
	 * 将数据转换为可以添加到搜索表的数据
	 * @param int $id 文章ID
	 * @return array $search 搜索数据
	 */
	protected  function _parseDataToSearch ($id, $data) {
		$search = array();
		$search['title'] = $data['title'];
		$search['content'] = trim($data['title']).trim(strip_tags($data['content']));
		$search['ta_id'] = $id;
		$seach['tc_id'] = $data['tc_id'];

		return $search;
	}

	/**
	 * 新增/编辑 查看权限
	 * @param int $id 文章ID
	 * @param object $article_right 文章权限对象
	 * @param array $rights 权限数据
	 */
	protected  function _addRights($id, &$article_right, $data) {
		if (empty ($data['contacts']) && empty ($data['deps'])) { //如果没有设置查看权限
			$insert_data = array('ta_id' => $id, 'tc_id' => $data['tc_id'], 'm_uid' => 0, 'cd_id' => 0,'is_all' => 1);
			$article_right->insert($insert_data);
		} else {  //如果设置了查看权限（人员/部门）
			if (!empty ($data['contacts'])) { //如果是人员
				foreach ($data['contacts'] as $contact) {
					$prepare[] = array('ta_id' => $id,  'tc_id' => $data['tc_id'], 'm_uid' => $contact, 'cd_id' => 0, 'is_all' => 0);
				}
			}
			if (!empty ($data['deps'])) { //如果是部门
				foreach ($data['deps'] as $dep) {
					$prepare[] = array('ta_id' => $id,  'tc_id' => $data['tc_id'], 'm_uid' => 0, 'cd_id' => $dep, 'is_all' => 0);
				}
			}
			$article_right->insert_multi($prepare);
		}

		return true;
	}

	/**
	 * 根据主键查找目录
	 * @param int $id 目录ID
	 */
	public function get_article_by_pk($pk) {
		$article = new voa_d_oa_train_article();
		return $article->get($pk);
	}

}
