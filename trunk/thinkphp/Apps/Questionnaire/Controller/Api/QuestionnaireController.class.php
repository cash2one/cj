<?php
/**
 * QuestionnaireController.class.php
 * $author$
 */
namespace Questionnaire\Controller\Api;

use Common\Common\Department;
use Questionnaire\Model\QuestionnaireClassifyModel;

class QuestionnaireController extends AbstractController {

	/**
	 * 问卷列表
	 */
	public function list_get() {

		//问卷状态 1:问卷结束 0:问卷进行中
		$user  = $this->_login->user;
		$type  = (int)I('get.type');
		$page  = I('get.page', 1, 'intval');
		$limit = I('get.limit', 10, 'intval');
		//获取用户部门及上级部门
		$my_cdids = array();
		$p_cdids = array();
		$this->_list_my_cdids($my_cdids, $p_cdids);

		$cd_id = implode(',', array_merge($my_cdids, $p_cdids));
		//h获取标签
		$label = D("Common/CommonLabelMember", 'Service');
		$lagelData = array(
			'm_uid' => $user['m_uid']
		);
		$labelResult = $label->list_by_conds($lagelData);

		//封装数据
		$data         = array(
			'type' => $type,
			'm_uid' => $user['m_uid'],
			'cd_id' => $cd_id,
		);

		if($labelResult){
			$tagid = array_column($labelResult, 'laid');
			$tagid = implode(',', $tagid);
			$data['tagid'] = $tagid;
		}
		list($start, $limit) = page_limit($page, $limit);
		$page_option  = array($start, $limit);
		$order_option = array('created' => 'DESC');
		$serv_q       = D('Questionnaire/Questionnaire', 'Service');
		$result       = $serv_q->questionnaireList($data, $page_option, $order_option);
		$total        = $serv_q->questionnaireListTotal($data);
		$formatResult = array();
		$formatData   = array(
			'list'  => $result,
			'm_uid' => $user['m_uid'],
			'cd_id' => $cd_id,
		);
		if($labelResult){
			$tagid = array_column($labelResult, 'laid');
			$tagid = implode(',', $tagid);
			$formatData['tagid'] = $tagid;
		}
		$serv_q->formatQuesrtionnaireList($formatData, $formatResult);
		$this->_result = array(
			'list'  => $formatResult,
			'total' => (int)$total,
			'page'  => $page,
		);
	}

	/**
	 * 我的问卷
	 */
	public function listMy_get() {

		$page  = I('get.page', 1, 'intval');
		$limit = I('get.limit', 10, 'intval');
		$user  = $this->_login->user;
		$data  = array(
			'uid' => $user['m_uid'],
		);
		list($start, $limit) = page_limit($page, $limit);
		$page_option  = array($start, $limit);
		$order_option = array('qnr.created' => 'DESC');
		$serv_q       = D('Questionnaire/QuestionnaireRecord', 'Service');
		$result       = $serv_q->questionnaireMyList($data, $page_option, $order_option);
		$total        = $serv_q->questionnaireMyListTotal($data);

		$formatResult = array();
		$serv_q->formatQuestionnaireMyList($result, $formatResult);
		$this->_result = array(
			'list'  => $formatResult,
			'total' => (int)$total,
			'page'  => $page,
		);
	}

	/**
	 * 问卷详情
	 */
	public function View_get() {

		$qr_id = I('get.qr_id', 0, 'intval');
		if (empty($qr_id)) {
			E('_ERR_MISS_RECORD');
			return false;
		}

		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
		$serv_question = D('Questionnaire/Questionnaire', 'Service');

		// 获取回答
		$record = $serv_record->get_by_conds(array('qr_id' => $qr_id));
		if (empty($record)) {
			E('_ERR_MISS_RECORD');
			return false;
		}
		// 获取问卷
		$naire = $serv_question->get_by_conds(array('qu_id' => $record['qu_id']));

		// 合并问卷和回答
		$view = $serv_record->merge_field_answer(json_decode($naire['field'], true), json_decode($record['answer'], true));

		// 分类名称
		$qc_name = '';
		if (!empty($naire['qc_id'])) {
			$serv_qc = D('Questionnaire/QuestionnaireClassify', 'Service');
			$qc_name = $serv_qc->get_by_conds(array('qc_id' => $naire['qc_id']));
		}
		$this->_result = array(
			'repeat' => $naire['repeat'],
			'qu_id' => $naire['qu_id'],
			'logo' => $this->_setting['square_logo_url'],
			'share' => $naire['share'],
			'title' => htmlspecialchars_decode($naire['title']),
			'body' => htmlspecialchars_decode($naire['body']),
			'deadline' => $naire['deadline'],
			'classify' => empty($qc_name['name']) ? QuestionnaireClassifyModel::CN_NO_CLASSIFY : $qc_name['name'],
			'view' => $view,
		);

		return true;
	}

	/**
	 * 获取我的部门列表, 包括子部门和上级部门两部分
	 * @param array $my_cdids 有权限的部门
	 * @param array $p_cdids 上级部门
	 * @return boolean
	 */
	protected function _list_my_cdids(&$my_cdids, &$p_cdids) {

		$my_cdids = Department::instance()->list_cdid_by_uid($this->_login->user['m_uid']);
		// 检查部门权限
		$serv_dp = D('Common/CommonDepartment', 'Service');
		if (!$departments = $serv_dp->list_by_pks($my_cdids)) {
			return false;
		}

		// 遍历自己所在部门
		$my_cdids = array();
		foreach ($departments as $_dp) {

			$my_cdids[$_dp['cd_id']] = $_dp['cd_id'];
			Department::instance()->list_parent_cdids($_dp['cd_id'], $p_cdids);
		}

		// 查询权限部门
		$conds_perm = array('cd_id' => $my_cdids);
		$serv_perm = D('Common/CommonDepartmentPermission', 'Service');
		$perm_list = $serv_perm->list_by_conds($conds_perm);
		foreach ($perm_list as $_addrdep) {
			$my_cdids[$_addrdep['per_id']] = $_addrdep['per_id'];
		}

		// 获取部门下所有子部门
		//$my_cdids = Department::instance()->list_childrens_by_cdid($my_cdids, true);
		return true;
	}
}
