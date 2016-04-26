<?php
namespace Jobtrain\Service;
use Org\Util\String;

class JobtrainCollService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Jobtrain/JobtrainColl");
	}

	/**
	 * 根据aid和m_uid物理删除
	 * @param int $aid
	 * @param int $m_uid
	 * @return bool
	 */
	public function delete_real_by_aid($aid, $m_uid) {
		return $this->_d->delete_real_by_aid($aid, $m_uid);
	}
	/**
	 * 获取收藏列表
	 * @param $type_id 文章类型
	 * @param $keywords 标题关键字
	 * @param $m_uid 用户id
	 * @return array
	 */
	public function get_list_join_article($type_id, $keywords, $m_uid, $start, $limit) {
		$list = $this->_d->get_list_join_article($type_id, $keywords, $m_uid, $start, $limit);
		foreach ($list as $k => $v) {
        	$list[$k]['picurl'] = $this->get_attachment($v['cover_id']);
        	$list[$k]['summary'] = String::msubstr(preg_replace ( "/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", '', $v['summary']),0,32);
        }
		return $list;
	}

}