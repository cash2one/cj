<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午4:08
 */

namespace Questionnaire\Service;

class QuestionnaireViewrangeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Questionnaire/QuestionnaireViewrange");
	}

	/**
	 * 根据问卷ID获取可见范围人员ID列表
	 * @param $qu_id
	 * @return array|bool
	 */
	public function list_uid_view_range($qu_id) {

		$model_viewrange = D('Questionnaire/QuestionnaireViewrange');
		$conds      = array('qu_id' => $qu_id);
		$view_range = $model_viewrange->list_by_conds($conds);
		if (empty($view_range)) {
			E('_ERR_MISS_VIEWRANGE');

			return false;
		}
		$range_uid   = array();
		$range_cdid  = array();
		$range_label = array();
		// 获取范围
		foreach ($view_range as $_range) {
			if (!empty($_range['view_range_uid'])) {
				$range_uid[] = $_range['view_range_uid'];
				continue;
			}
			if (!empty($_range['view_range_cdid'])) {
				$range_cdid[] = $_range['view_range_cdid'];
				continue;
			}
			if (!empty($_range['view_range_label'])) {
				$range_label[] = $_range['view_range_label'];
				continue;
			}
		}

		$uid_list = $range_uid;
		if (!empty($range_cdid)) {
			$range_cdid = array_unique($range_cdid);
			// 查询部门下的所有子部门
			$dep_list = \Common\Common\Department::instance()->list_childrens_by_cdid($range_cdid, true);
			// 获取部门下的人员
			if (!empty($dep_list)) {
				$model_dep_mem = D('Common/MemberDepartment');
				$temp_dep_mem  = $model_dep_mem->list_by_conds(array('cd_id' => $dep_list));
				$dep_mem       = array_unique(array_column($temp_dep_mem, 'm_uid'));
				$uid_list      = array_merge($range_uid, $dep_mem);
			}
		}
		// 获取标签下的人员
		if (!empty($range_label)) {
			$range_label = array_unique($range_label);
			// 查询标签下的人员
			$model_label_mem = D('Common/CommonLabelMember');
			$temp_label_mem  = $model_label_mem->list_by_conds(array('laid' => $range_label));
			$label_mem       = array_column($temp_label_mem, 'm_uid');
			$uid_list        = array_unique(array_merge($label_mem, $uid_list));
		}

		return $uid_list;
	}

	/**
	 * 保存可见人员数据
	 * @param int $qu_id 问卷ID
	 * @param array $data 数据
	 */
	public function saveData($qu_id, $data) {
		if (is_array($data)) {
			$this->deleteData($qu_id);
			
			foreach ($data as $type => $values) {
				foreach ($values as $value) {
					$viewrange = ['qu_id' => $qu_id];
					
					switch ($type) {
						case "departments":
							$viewrange['view_range_cdid'] = $value['id'];
							break;
						case "persons":
							$viewrange['view_range_uid'] = $value['m_uid'];
							break;
						case "tags":
							$viewrange['view_range_label'] = $value['laid'];
							break;
					}
					
					$this->_d->insert($viewrange);
				}
			}
		}
	}
	
	/**
	 * 删除可见人员数据
	 * @param int $qu_id 问卷ID
	 */
	public function deleteData($qu_id) {
		$this->_d->delete_by_conds(['qu_id' => $qu_id]);
	}

	/**
	 * 获取可见人员数据
	 * @param int $qu_id 问卷ID
	 * @return array
	 */
	public function getData($qu_id) {
		$viewranges = $this->_d->list_by_conds(['qu_id' => $qu_id]);
		$idArray = $data = [];

		foreach ($viewranges as $viewrange) {
			if ($viewrange['view_range_cdid']) {
				$idArray['departments'][] = $viewrange['view_range_cdid'];
			}
			elseif ($viewrange['view_range_uid']) {
				$idArray['persons'][] = $viewrange['view_range_uid'];
			}
			else {
				$idArray['tags'][] = $viewrange['view_range_label'];
			}
		}

		foreach ($idArray as $type => $ids) {
			switch ($type) {
				case "departments":
					$cache = \Common\Common\Cache::instance();
					$departments = $cache->get('Common.department');

					foreach ($ids as $id) {
						if (isset($departments[$id])) {
							$data[$type][] = [
								'id' => $departments[$id]['cd_id'],
								'name' => $departments[$id]['cd_name'],
							];
						}
					}
					break;
				case "persons":
					$memberModel = D('Common/Member');
					$members = $memberModel->list_by_conds(['m_uid' => $ids]);

					foreach ($members as $member) {
						if (empty($member['m_face'])) {
							$member['m_face'] = \Common\Common\User::instance()->avatar($member['m_uid'], $member);
						}

						$data[$type][] = [
							'm_uid' => $member['m_uid'],
							'm_username' => $member['m_username'],
							'm_face' => $member['m_face'],
						];
					}
					break;
				case "tags":
					$labelModel = D('Common/CommonLabel');
					$labels = $labelModel->list_by_conds(['laid' => $ids]);

					foreach ($labels as $label) {
						$data[$type][] = [
							'laid' => $label['laid'],
							'name' => $label['name'],
						];
					}
					break;
			}
		}

		return $data;
	}
}