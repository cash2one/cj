<?php
namespace Jobtrain\Service;

class JobtrainCategoryService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();
		// 实例化相关模型
		$this->_d = D("Jobtrain/JobtrainCategory");
	}
	/**
	 * 获取分类信息
	 * @param $id
	 * @return array
	 */
	public function get_by_id($id) {
		$model_dp = D("Common/CommonDepartment");
		$serv_member = D('Common/Member', 'Service');
		$cata = $this->_d->get($id);
		// 获取部门
		$cata['departments'] = array();
		if(!empty($cata['cd_ids'])) {
			$conditions = array(
				'cd_id' => explode(',', $cata['cd_ids']),
			);
			$departments =  $model_dp->list_by_conds($conditions);
			foreach ($departments as $value) {
				$cata['departments'][] = $value['cd_name'];
			}
		}
		// 获取用户
		$cata['members'] = array();
		if(!empty($cata['m_uids'])) {
			$conditions = array(
				'm_uid' => explode(',', $cata['m_uids']),
			);
			$members =  $serv_member->list_by_conds($conditions);
			foreach ($members as $value) {
				$cata['members'][] = $value['m_username'];
			}
		}

		return $cata;
	}
	/**
	 * 根据权限获取分类树
	 * @param int $m_uid
	 * @return array
	 */
	public function get_tree_with_right($m_uid) {

		$s_mbdp = D('Common/MemberDepartment', 'Service');
		$dps = $s_mbdp->list_by_conds(array('m_uid'=>$m_uid));
		$cd_ids = array();
		foreach ($dps as $k => $v) {
			$cd_ids[] = $v['cd_id'];
		}
		$catas = $this->_d->get_tree_with_right($m_uid, $cd_ids);

		// 重新计算一级分类文章数
		foreach ($catas as $k => $v) {
			foreach ($catas[$k]['childs'] as $_v) {
				$catas[$k]['article_num'] += $_v['article_num'];
			}
		}
		// 取消键值
		$catas = array_values($catas);
		foreach ($catas as $k => $v) {
			$catas[$k]['childs'] = array_values($catas[$k]['childs']);
		}

		return $catas;
	}
	
	
}