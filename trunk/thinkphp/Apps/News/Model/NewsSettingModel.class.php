<?php
/**
 * 新闻公告 设置表 Model
 * User: Muzhitao
 * Date: 2015/9/15 0015
 * Time: 14:25
 * Email:muzhitao@vchangyi.com
 */

namespace News\Model;

class NewsSettingModel extends AbstractModel {

	// 数据类型: 数组
	const TYPE_ARRAY = 1;
	// 数据类型: 字串
	const TYPE_NORMAL = 0;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	// 获取数组类型标识
	public function get_type_array() {

		return self::TYPE_ARRAY;
	}

	// 获取字串类型标识
	public function get_type_normal() {

		return self::TYPE_NORMAL;
	}

	/**
	 * 查询用户所在的部门并返回部门ID
	 * @param $m_uid 用户的m_uid
	 * @return array
	 */
	public function fetch_by_uid($m_uid) {
		return $this->_m->fetch_row("SELECT cd_id FROM oa_member_department WHERE m_uid=? AND md_status<? LIMIT 1", array (
			$m_uid,
			$this->get_st_delete()
		));
	}

}
