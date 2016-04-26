<?php
/**
 * NewsReadModel.class.php
 * $author$
 */
namespace News\Model;

class NewsReadModel extends AbstractModel {

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 获取已读新闻列表
	 * @param int $m_uid 用户id
	 * @return array $result
	 */
	public function list_by_uid($m_uid) {

		$sql = "SELECT ne_id FROM __TABLE__ WHERE m_uid=? AND status<?";
		$params = array($m_uid, $this->get_st_delete());

		$result = $this->_m->fetch_array($sql, $params);

		return $result;
	}
}
