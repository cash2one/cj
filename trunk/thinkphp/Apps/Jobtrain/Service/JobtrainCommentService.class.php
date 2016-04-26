<?php
namespace Jobtrain\Service;
use Org\Util\String;

class JobtrainCommentService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Jobtrain/JobtrainComment");
	}

	
	/**
	 * 获取评论列表
	 * @param int $aid
	 * @param int $m_uid
	 * @return bool
	 */
	public function list_by_conds_join_member($aid, $start, $limit) {
		return $this->_d->list_by_conds_join_member($aid, $start, $limit);
	}

	/**
	 * 点赞数量+1
	 * @param int $id
	 * @return bool
	 */
	public function inc_zan_num($id) {
		return $this->_d->inc_zan_num($id);
	}

}