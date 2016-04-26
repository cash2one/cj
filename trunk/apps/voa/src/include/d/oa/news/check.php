<?php
/**
 * voa_d_oa_news_check
 * 文章审核表
 * @date: 2015年5月7日
 *
 * @author : kk
 * @version :
 */
class voa_d_oa_news_check extends voa_d_abstruct {

	/**
	 * 初始化
	 */
	public function __construct($cfg = null) {
		// 表名
		$this->_table = 'orm_oa.news_check';
		// 允许的字段
		$this->_allowed_fields = array();
		// 必须的字段
		$this->_required_fields = array();
		// 主键
		$this->_pk = 'nec_id';
		parent::__construct(null);

	}

	/**
	 * 判断用户是否有审核权限
	 *
	 * @param int $ne_id
	 * @param int $us_id
	 * @return boolean:
	 */
	public function is_check($ne_id, $us_id) {
		$result = $this->get_by_conds(array('news_id' => $ne_id, 'm_uid' => $us_id ));
		if ($result) {
			return true;
		}
		return false;
	}

}
