<?php
/**
 * QueClassifyController.class.php
 * @author tony
 */

namespace Questionnaire\Controller\Apicp;

class ClassifyController extends AbstractController {

	/**
	* 添加问卷类别的post方法 
	* @return bool
	*/
	public function Add_post() {
		$qc_id = I('post.qcid', 'intval');
		$name = I('post.name');
		$trim_name = trim($name);
		if (empty($trim_name)) {
			E('_ERR_EMPTY_NAME');
			return false;
		}
		// 判断长度
		$name_strlen = mb_strlen($trim_name, 'UTF-8');
		if ($name_strlen > 15) {
			E(L('_ERR_NAME_STRLEN_NAME', array('strlen' => 15)));
			return false;
		}

		$service_qu = D('Questionnaire/QuestionnaireClassify', 'Service');
		// 判断名称是否重复
		if ($service_qu->get_by_conds(array('name' => $trim_name))) {
			E('_ERR_NAME_IS_REPEAT');
			return false;
		}

		$data = array('name' => $name);
		$service_qu->add($data, $qc_id);

		return true;
	}

	/**
	* 删除问卷类别
	* @return bool
	*/
	public function Delete_post(){

		$qc_id = I('post.qcid', 'intval');
		if (empty($qc_id)) {
			E('_ERR_MISS_QC_ID');
			return false;
		}
		// 删除分类
		$serv_class = D('Questionnaire/QuestionnaireClassify', 'Service');
		$serv_class->delete($qc_id);
		// 把分类下的问卷归类为 '未分类'
		$serv_qu = D('Questionnaire/Questionnaire', 'Service');
		$serv_qu->update_by_conds(array('qc_id' => $qc_id), array('qc_id' => 0));

		return true;
	}

	/**
	* 获得问卷类别的列表
	* @return bool
	*/
	public function List_get(){

		$service_qu = D('Questionnaire/QuestionnaireClassify', 'Service');

		$conds = array();
		$page_option = array();
		$list = $service_qu->get_list($conds, $page_option);

		$this->_result = array(
			'list' => $list,
		);

		return true;
	}

}
