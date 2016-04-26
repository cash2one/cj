<?php
/**
 * voa_uda_frontend_showroom_action_categoryedit
 * 统一数据访问/陈列/编辑目录
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_showroom_action_categoryedit extends voa_uda_frontend_showroom_abstract {

	/**
	 * 新增/编辑目录
	 * @param int $id 目录ID
	 * @param array $data 目录数据
	 */
	public function edit($id, $data) {


		$category = new voa_d_oa_showroom_category();
		$category_right = new voa_d_oa_showroom_categoryright();
		$tmp = $data;
		unset($tmp['contacts']);
		unset($tmp['deps']);
		if ($id) { //编辑目录
			$category->update($id, $tmp);
			//将现有权限删除，以便后面统一新增
			$category_right->delete_real_by_category_id($id);
			$this->_addRights($id, $category_right, $data);//新增权限
		} else {  //新增目录
			$category->insert($tmp);
			$id = $category->getLastInsertId();
			$this->_addRights($id, $category_right, $data);//新增权限
		}



		return true;
	}

	/**
	 * 根据主键查找目录
	 * @param int $id 目录ID
	 */
	public function get_category_by_pk($pk) {

		$d_category = new voa_d_oa_showroom_category();
		$category =  $d_category->get($pk);
		$this->get_rights($pk, $category);

		return $category;
	}

	/**
	 * 新增/编辑 查看权限
	 * @param int $id 目录ID
	 * @param object $article_right 目录权限对象
	 * @param array $rights 权限数据
	 */
	protected  function _addRights($id, &$category_right, $data) {
	//  新增/编辑 查看权限
		if (empty ($data['contacts']) && empty ($data['deps'])) { //如果没有设置查看权限
			$data = array('tc_id' => $id, 'm_uid' => 0, 'cd_id' => 0,'is_all' => 1);
			$category_right->insert($data);
		} else {  //如果设置了查看权限（人员/部门）
			$prepare = array();
			if (!empty ($data['contacts'])) { //如果是人员
				foreach ($data['contacts'] as $contact) {
					$prepare[] = array('tc_id' => $id, 'm_uid' => $contact, 'cd_id' => 0, 'is_all' => 0);
				}
			}
			if (!empty ($data['deps'])) { //如果是部门
				foreach ($data['deps'] as $dep) {
					$prepare[] = array('tc_id' => $id, 'm_uid' => 0, 'cd_id' => $dep, 'is_all' => 0);
				}
			}
			$category_right->insert_multi($prepare);
		}

		return true;
	}

	/**
	 * 根据条件目录ID取回目录权限
	 * @param int $id  目录ID
	 * @param array   $content 目录内容（引用）
	 */
	public function get_rights($id, &$category) {

		$d_category_right = new voa_d_oa_showroom_categoryright();
		$category_rights = $d_category_right->list_by_conds(array('tc_id'=>$id));
		if ($category_rights) {
			foreach ($category_rights as $right) {
				if ($right['m_uid'] != 0 && $right['cd_id'] == 0) { //人员
					$category['contacts'][] = $right['m_uid'];
				}
				if ($right['m_uid'] == 0 && $right['cd_id'] != 0) { //部门
					$category['deps'][] = $right['cd_id'];
				}
			}
		}
	}

}
