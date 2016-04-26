<?php

namespace Jobtrain\Service;
use Org\Util\String;

class JobtrainArticleService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Jobtrain/JobtrainArticle");
	}

	/**
	 * 获取文章列表
	 *
	 * @param $cids 分类id
	 * @param $type_id 文章类型
	 * @param $keywords 标题关键字
	 * @param $is_study 是否学习
	 * @param $m_uid 用户id
	 * @return array
	 */
	public function get_list($cids, $type_id, $keywords, $is_study, $m_uid, $start, $limit) {

		$result = $this->_d->get_list($cids, $type_id, $keywords, $is_study, $m_uid, $start, $limit);
		foreach ($result['list'] as &$v) {
			$v['picurl'] = $this->get_attachment($v['cover_id']);
			$v['summary'] = String::msubstr(preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", '', $v['summary']), 0, 32);
			$v['is_study'] = $v['aid'] ? 1 : 0;
		}
		return $result;
	}

	/**
	 * 学习数量+1 成功则返回true 否则返回false
	 *
	 * @param int $id
	 * @return bool
	 */
	public function inc_study_num($id) {

		return $this->_d->inc_study_num($id);
	}

	/**
	 * 收藏+1
	 *
	 * @param int $id
	 * @return bool
	 */
	public function inc_coll_num($id) {

		return $this->_d->inc_coll_num($id);
	}

	/**
	 * 收藏-1
	 *
	 * @param int $id
	 * @return bool
	 */
	public function dec_coll_num($id) {

		return $this->_d->dec_coll_num($id);
	}

}
