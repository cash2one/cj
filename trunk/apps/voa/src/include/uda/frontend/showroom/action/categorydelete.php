<?php
/**
 * voa_uda_frontend_showroom_action_categorydelete
 * 统一数据访问/陈列/删除目录
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_showroom_action_categorydelete extends voa_uda_frontend_showroom_abstract {

	/**
	 * 根据条件删除目录
	 * @param array $ids 目录ID数组
	 */
	public function delete($ids) {

		$category = new voa_d_oa_showroom_category();
		$category_right = new voa_d_oa_showroom_categoryright();

		//删除目录、目录权限
		try {
			$category->beginTransaction();

			$category->delete($ids);  		                       //删除目录
			$category_right->delete_real_by_category_ids($ids);    //物理删除文章目录

			$category->commit();
		} catch (Exception $e) {
			$category->rollBack();

			return $this->set_errmsg(voa_errcode_oa_showroom::DELETE_CATEGORY_FAILED);
		}

		return true;
	}

}
