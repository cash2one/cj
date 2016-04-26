<?php
/**
 * voa_d_oa_showroom_articleright
 * 文章目录查看权限
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */

class voa_d_oa_showroom_articleright extends voa_d_abstruct {

	const IS_ALL = 1; //所有人员可查看
	const NOT_IS_ALL = 0; //不是所有人员可查看
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.showroom_article_right';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'tar_id';

		parent::__construct(null);
	}

	/**
	 * 查找一定数量目录下用户有权限查看的文章
	 * @param int $m_uid 用户ID
	 * @param array $tc_ids 目录ID
	 * @param array $cd_ids 用户所属部门ID
	 * @param int $page_option 分页
	 */
	public  function list_right_artilces($m_uid, $tc_ids, $cd_ids, $page_option) {

		//tc_id
		$str1 = array();
		$this->_field_sign_condi($str1, "tc_id in (?)", $tc_ids);
		foreach ($tc_ids as $tc){
			$data[] = $tc;
		}

		$data[] = $m_uid;
		//cd_id
		$str2 = array();
		$this->_field_sign_condi($str2, "cd_id in (?)", $cd_ids);
		foreach ($cd_ids as $cd){
			$data[] = $cd;
		}

		$data[] = 1;
		$data[] = parent::STATUS_DELETE;
		$sql = " {$str1[0]} AND (m_uid=? OR {$str2[0]} OR is_all=?) AND status<? ";
		$list = $this->_list_by_complex($sql, $data, $page_option, array('updated' =>'DESC'), 'ta_id, tar_id');

		return $list;
	}

	/**
	 * 查找一个目录下用户有权限查看的文章
	 * @param int $m_uid 用户ID
	 * @param int $tc_id 目录ID
	 * @param array $cd_ids 用户所属部门ID
	 * @param int $page_option 分页
	 */
	public  function list_right_artilce($m_uid, $tc_id, $cd_ids, $page_option) {

		$data[] = $tc_id;
		$data[] = $m_uid;
		if ($cd_ids) {
			$str = array();
			$this->_field_sign_condi($str, "cd_id in (?)", $cd_ids);
			foreach ($cd_ids as $cd){
				$data[] = $cd;
			}
			$find_cd_sql = ' OR '.$str[0];
		} else {
			$find_cd_sql = '';
		}

		$data[] = 1;
		$data[] = parent::STATUS_DELETE;
		$sql = "tc_id=? AND (m_uid=? {$find_cd_sql} OR is_all=?) AND status<?";
		$list = $this->_list_by_complex($sql, $data, $page_option, array('updated' =>'DESC'), 'ta_id, tar_id');

		return $list;
	}

	/**
	 * 查找一个目录下用户有权限查看的文章总数
	 * @param int $m_uid 用户ID
	 * @param int $tc_id 目录ID
	 * @param array $cd_ids 用户所属部门ID
	 */
	public function list_right_article_count($m_uid, $tc_id, $cd_ids) {

		$data[] = $tc_id;
		$data[] = $m_uid;
		if ($cd_ids) {
			$str = array();
			$this->_field_sign_condi($str, "cd_id in (?)", $cd_ids);
			foreach ($cd_ids as $cd){
				$data[] = $cd;
			}
			$find_cd_sql = ' OR '.$str[0];
		} else {
			$find_cd_sql = '';
		}

		$data[] = 1;
		$data[] = parent::STATUS_DELETE;
		$sql = "tc_id=? AND (m_uid=? {$find_cd_sql} OR is_all=?) AND status<?";
		$count = $this->_count_by_complex($sql,$data, 'tar_id');

		return $count;
	}

	/**
	 * 物理删除文章权限
	 * @param array $ids 文章ID
	 */
	public function delete_real_by_article_ids ($ids) {

		return $this->_delete_real_by_conds(array('ta_id' => $ids));
	}

	/**
	 * 物理删除文章权限
	 * @param int $id 文章ID
	 */
	public function delete_real_by_article_id ($id) {

		return $this->_delete_real_by_conds(array('ta_id' => $id));
	}
}

