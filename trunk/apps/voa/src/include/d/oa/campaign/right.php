<?php
/**
 * 活动权限表
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_d_oa_campaign_right extends voa_d_abstruct {
	
	/** 初始化 */
	public function __construct($cfg = null) {

		/** 表名 */
		$this->_table = 'orm_oa.campaign_right';
		/** 允许的字段 */
		$this->_allowed_fields = array();
		/** 必须的字段 */
		$this->_required_fields = array();
		/** 主键 */
		$this->_pk = 'id';
		parent::__construct(null);
	}
	
	//获取活动部门
	public function get_right($id)
	{
		$list = $this->list_by_conds(array('actid' => $id));
		$list = $list ? array_values($list) : array();
		foreach ($list as &$l)
		{
			$l = $l['depid'];
		}
		return $list;
	}
	
	/**
	 * 添加活动部门
	 *
	 * @param int $id
	 * @param array $deps	部门id数组
	 */
	public function add_right($id, $deps)
	{
		$data = array();
		foreach ($deps as $dep)
		{
			$data[] = array('actid' => $id, 'depid' => $dep);
		}
		if($data) $this->insert_multi($data);
	}
	
	/**
	 * 删除活动部门
	 *
	 * @param int $id
	 * @param array $deps	部门id数组
	 */
	public function del_right($id, $deps)
	{
		if(!$deps) return ;
		$this->delete_by_conds(array('actid' => $id, 'depid' => $deps));
	}
}

