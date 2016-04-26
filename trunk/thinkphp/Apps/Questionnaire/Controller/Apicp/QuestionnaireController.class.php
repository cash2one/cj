<?php
/**
 * QuestionnaireController.class.php
 * $author$
 */
namespace Questionnaire\Controller\Apicp;

use Common\Common\Pager;
use Questionnaire\Model\QuestionnaireModel;

class QuestionnaireController extends AbstractController {

	/**
	 * 获取问卷数据
	 */
	public function data_post() {
		$responseData = ['result' => false];
		
		$qu_id = I('post.qu_id', 0, 'intval');
		$serv_q = D('Questionnaire/Questionnaire', 'Service');
		$data = $serv_q->getData($qu_id);
		
		if ($data) {
			$responseData['result'] = true;
			$responseData['q'] = $data;
			
			if ($data['is_all'] == 0) {
				$serv_viewrange = D('Questionnaire/QuestionnaireViewrange', 'Service');
				$responseData['viewranges'] = $serv_viewrange->getData($qu_id);
			}
		}
		
		$this->_result = $responseData;
	}

	/**
	 * 保存问卷数据
	 */
	public function save_post() {
		$responseData = ['result' => false];
		
		$data = I('post.q');
		$viewranges = I('post.viewranges');
		
		$serv_q = D('Questionnaire/Questionnaire', 'Service');
		$qu_id = $serv_q->saveData($data);

		if ($qu_id) {
			$q = $serv_q->getData($qu_id);
			$serv_viewrange = D('Questionnaire/QuestionnaireViewrange', 'Service');
			
			//保存可见人员数据
			if ($q['is_all'] == 0) {
				$serv_viewrange->saveData($qu_id, $viewranges);
			}
            else {
                $serv_viewrange->deleteData($qu_id);
            }
            
			//问卷状态为预发布或进行中
			if ($q['release_status'] != 2) {
                //首次发布问卷
                if (isset($data['release'])) {
                    //定时发布
                    if ($q['release'] > 0) {
                        $serv_q->timeRelease($q);
                    }

                    //结束提醒
                    $serv_q->endRemind($q);
                }
				
				//即时发布问卷
				if ($q['release'] == 0) {
					//可见范围为所有人
					if ($q['is_all'] == -1) {
						$uid = '@all';
					}
					//可见范围为特定人员
					else {
						$uid = $serv_viewrange->list_uid_view_range($qu_id);
					}
                    
					$serv_q->sendReleaseMsg($q, $uid);
				}
			}

			$responseData['result'] = true;
			$responseData['q'] = $q;
		}
		
		$this->_result = $responseData;
	}

	/**
	 * 问卷列表
	 */
	public function list_get() {

		$page  = I('get.page', 1, 'intval');
		$limit = I('get.limit', 10, 'intval');
		$data  = I('get.'); //开始
		//封装数据
		$serv_q       = D('Questionnaire/Questionnaire', 'Service');
		$total        = $serv_q->questionnaireBackendListTotal($data);
		$end_page = ceil($total/$limit);
		if($page > $end_page){
			$page = $end_page;
		}
		list($start, $limit) = page_limit($page, $limit);
		$page_option  = array($start, $limit);
		$order_option = array('created' => 'DESC');
		$result       = $serv_q->questionnaireBackendList($data, $page_option, $order_option);
		$formatResult = array();
		$serv_q->formatQuesrtionnaireBackendList($result, $formatResult);

		$multi = '';
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			);
			$multi = Pager::make_links($pagerOptions);
		}
		$this->_result = array(
			'list'  => $formatResult,
			'multi' => $multi,
		);
	}

	/**
	 * 删除问卷
	 * @return bool
	 */
	public function questionnaireDel_post() {

		$qu_id = I('post.qu_id');
		if (empty($qu_id)) {
			E('_ERR_NO_EXIST_QUERY');

			return false;
		}
		if (is_array($qu_id)) {
			$qu_ids = $qu_id;
		} else {
			$qu_ids = explode(',', $qu_id);
		}
		$data   = array(
			'qu_id' => $qu_ids
		);
		$serv_q = D('Questionnaire/Questionnaire', 'Service');
		if (!$serv_q->delete_by_conds($data)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');

			return false;
		}

		return true;
	}

	/**
	 * 结束问卷
	 * @return bool
	 */
	public function questionnaireEnd_post() {

		$qu_id = I('post.qu_id');
		if (empty($qu_id)) {
			E('_ERR_NO_EXIST_QUERY');

			return false;
		}
		$data   = array(
			'deadline' => time()
		);
		$where = array(
			'qu_id' => $qu_id
		);
		$serv_q = D('Questionnaire/Questionnaire', 'Service');
		if (!$serv_q->update_by_conds($where, $data)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');

			return false;
		}
	}
}
