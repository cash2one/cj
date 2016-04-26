<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午4:08
 */

namespace Questionnaire\Service;

use Questionnaire\Model\QuestionnaireClassifyModel as QuestionnaireClassify;

class QuestionnaireClassifyService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Questionnaire/QuestionnaireClassify");
	}

	/**
	 * 问卷类别的新增和修改
	 * @param array $data
	 * @param int   $qc_id
	 * @return bool
	 */
	public function add($data, $qc_id) {
		if (empty($data['name'])) {
			$this->_set_error('_ERR_CONTENT_NULL');

			return false;
		}

		if ($qc_id > 0) {
			//修改 
			$res = $this->_d->update_by_conds(array('qc_id' => $qc_id), $data);

		} else {
			//新增
			$res = $this->_d->insert($data);
		}

		return $res;
	}

	/**
	 * 删除问卷类别
	 * @param int $qc_id
	 * @return bool
	 */
	public function delete($qc_id) {
		if (empty($qc_id)) {
			$this->_set_error('_ERR_TITLE_NULL');

			return false;
		}
		$res = $this->_d->delete_by_conds(array('qc_id' => $qc_id));

		return $res;
	}

	/**
	 * 获取问卷列表
	 * @param $conds
	 * @param $page_option
	 * @return bool
	 */
	public function get_list($conds, $page_option) {

		$temp = array(
			array(
				'name' => QuestionnaireClassify::CN_NO_CLASSIFY,
				'created' => '固定分类',
				'qc_id' => 0,
			),
		);
		$list = $this->_d->list_by_conds($conds, $page_option);
		foreach ($list as &$_data) {
			$_data['created'] = rgmdate($_data['created'], 'Y-m-d H:i');
		}
		$list = array_merge($temp, $list);

		return $list;
	}
}