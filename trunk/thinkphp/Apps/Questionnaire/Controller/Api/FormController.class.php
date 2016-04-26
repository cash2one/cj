<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午3:02
 */

namespace Questionnaire\Controller\Api;

use Common\Common\Department;
use Think\Log;
use Questionnaire\Model\QuestionnaireClassifyModel as QuestionnaireClassify;
use Questionnaire\Model\QuestionnaireModel as Questionnaire;

class FormController extends AbstractController {

	/** 单选项限制 */
	const RADIO_MAX_SELECT = 1;

	public function before_action($action = '') {

		$this->_require_login = false;

		return parent::before_action($action);
	}

	/**
	 * 获取问卷题目
	 * @return bool
	 */
	public function Field_get() {

		// 前置操作
		$is_repeat = false;
		$is_over_deadline = false;
		$question = $this->_execute('get', true, $is_repeat, true, $is_over_deadline);

		// 查询分类
		$serv_classify = D('Questionnaire/QuestionnaireClassify', 'Service');
		if (empty($question['qc_id'])) {
			$classify = QuestionnaireClassify::CN_NO_CLASSIFY;
		} else {
			$classify_data = $serv_classify->get_by_conds(array('qc_id' => $question['qc_id']));
			$classify = empty($classify_data['name']) ? QuestionnaireClassify::CN_NO_CLASSIFY : $classify_data['name'];
		}

		$this->_result = [
			'qu_id' => $question['qu_id'],
			'logo' => $this->_setting['square_logo_url'],
			'repeat' => $question['repeat'], // 是否允许重复
			'is_repeat' => $is_repeat, // 是否已经填写过
			'is_over_deadline' => $is_over_deadline, // 是否超过截止时间
			'share' => $question['share'], // 是否允许分享
			'classify' => $classify, // 分类
			'title' => htmlspecialchars_decode($question['title']), // 标题
			'body' => htmlspecialchars_decode($question['body']), // 描述
			'deadline' => $question['deadline'], // 截止时间
			'field' => $question['field'], // 题目
		];

		return true;
	}

	/** 题目答案提交 */
	public function Post_post() {

		// 前置操作
		$is_repeat = false;
		$is_over_deadline = false;
		$question = $this->_execute('post', false, $is_repeat, false, $is_over_deadline);

		// 判断提交的数据是否合法
		list($answer, $username) = $this->_validate($question);

		// 如果是实名 并且内部人员 名称为空
		if ($question['anonymous'] == Questionnaire::REAL_NAME && !empty($this->_login->user) && empty($username)) {
			$username = $this->_login->user['m_username'];
		}

		// 数据入库
		$wx_openid = $this->_login->getcookie('wx_openid');
		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
		$insert_data = array(
			'qu_id' => $question['qu_id'],
			'username' => $username, // 填写人名称
			'openid' => empty($wx_openid) ? '' : $wx_openid, // 外部人员微信openid
			'answer' => rjson_encode($answer),
			'uid' => empty($this->_login->user['m_uid']) ? 0 : $this->_login->user['m_uid'],
		);
		$serv_record->insert($insert_data);

		return true;
	}

	/**
	 * 前置操作
	 * @param string $type 请求类型 获取表单数据接口Field为get 提交表单数据接口Post为post
	 * @param bool   $skip_repeat 是否跳过判定为重复后的报错
	 * @param bool   $is_repeat 是否重复
	 * @param bool   $skip_deadline
	 * @param bool   $is_over_deadline
	 * @return bool
	 */
	protected function _execute($type = 'get', $skip_repeat = false, &$is_repeat, $skip_deadline = false, &$is_over_deadline) {

		$m_uid = $this->_login->user['m_uid'];
		$openid = $this->_login->getcookie('wx_openid');

		// 获取问卷ID
		$qu_id = I($type . '.qu_id', 0, 'intval');
		$share_id = I($type . '.share_id', '');

		$serv_qu = D('Questionnaire/Questionnaire', 'Service');
		if (!empty($qu_id)) {
			$conds = array('qu_id' => $qu_id);
		} elseif (!empty($share_id)) {
			$conds = array('share_id' => $share_id);
		}
		$question = $serv_qu->get_by_conds($conds);
		if (empty($question)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');
			return false;
		}

		// 判断问卷是否过期
		if (NOW_TIME > $question['deadline']) {
			// 过期后内部人员还能看
			if ($type == 'get' && $skip_deadline) {
				$is_over_deadline = true;
			} else {
				E('_ERR_IS_OVER_DEADLINE');
				return false;
			}
		}

		// 如果没有发布
		if ($question['release_status'] != Questionnaire::RELEASE_STATUS) {
			E('_ERR_UN_RELEASE');
		}
		// 判断该问卷 外部人员是否可填写 内部人员就不判断
		if ($question['share'] == Questionnaire::UN_SHARE) {
			if (!empty($m_uid)) {
				// 判断问卷 内部人员是否在可见范围内
				$this->_in_view_range($question);
			} else {
				E('_ERR_UN_SHARE');
				return false;
			}
		} else {
			// 分享时内部人员打开问卷进行判断
			if (!empty($m_uid)) {
				// 判断问卷 内部人员是否在可见范围内
				$this->_in_view_range($question);
			}
		}

		// 格式化题目规则
		$question['field'] = json_decode($question['field'], true);

		if ($type == 'get') {
			$serv_mem = D('Common/Member', 'Service');

			// 获取默认的姓名 手机 邮箱号
			if (!empty($m_uid)) {
				$m_data = $serv_mem->get_by_conds(array('m_uid' => $m_uid));
			}

			foreach ($question['field'] as &$_field) {
				// 修正布尔值
				$_field['required'] = $_field['required'] == 'true' ? true : false;
				// 修正标题HTML转义
				$_field['title'] = htmlspecialchars_decode($_field['title']);
				// 修正placeholder
				$_field['placeholder'] = htmlspecialchars_decode($_field['placeholder']);

				// 其他选项名称为空时,默认'其他'
				if (in_array($_field['type'], array('select', 'checkbox', 'radio'))) {
					if (!empty($_field['option'])) {
						foreach ($_field['option'] as &$_option) {
							if (isset($_option['other']) && $_option['other'] && $_option['value'] == '') {
								$_option['value'] = '其他';
							}
						}
					}
					continue;
				}
				switch ($_field['type']) {
					// 内部人员默认数据
					case 'username':
						if (!empty($m_data['m_username'])) {
							$_field['value'] = $m_data['m_username'];
						}
						break;
					case 'mobile':
						if (!empty($m_data['m_mobilephone'])) {
							$_field['value'] = $m_data['m_mobilephone'];
						}
						break;
					case 'email':
						if (!empty($m_data['m_email'])) {
							$_field['value'] = $m_data['m_email'];
						}
						break;
					// 处理地址
					case 'address':
						if (isset($_field['more'])) {
							if ($_field['more'] == 'true') {
								$_field['more'] = true;
							} else {
								$_field['more'] = false;
							}
						}
				}
			}
		}

		// 判断问卷是否可以重复提交
		if ($question['repeat'] != Questionnaire::REPEAT) {
			// 获取填写记录
			$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
			$conds = array('openid' => $openid, 'uid' => $m_uid, 'qu_id' => $qu_id);
			$record_list = $serv_record->list_by_conds($conds);
			if (!empty($record_list)) {
				// 是否跳过重复填写的 报错
				if (!$skip_repeat) {
					E('_ERR_CAN_NOT_REPEAT');
					return false;
				} else {
					$is_repeat = true;
				}
			}
		}

		return $question;
	}

	/**
	 * 判断是否在可见范围内
	 * @return bool
	 */
	protected function _in_view_range($question) {

		$m_uid = $this->_login->user['m_uid'];

		// 所有人可见
		if ($question['is_all'] == Questionnaire::IS_ALL) {
			return true;
		}

		if (!empty($m_uid)) {
			// 获取可见范围设置
			$serv_viewrange = D('Questionnaire/QuestionnaireViewrange', 'Service');
			$view_range = $serv_viewrange->list_by_conds(array('qu_id' => $question['qu_id']));
			if (empty($view_range)) {
				E('_ERR_NOT_IN_VIEW_RANGE');
				return false;
			}
			// 判断范围
			$range_cdid = array();
			$range_uid = array();
			$range_label = array();
			foreach ($view_range as $_range) {
				// 获取可见人员
				if (!empty($_range['view_range_uid'])) {
					$range_uid[] = $_range['view_range_uid'];
					continue;
				}
				// 获取可见部门
				if (!empty($_range['view_range_cdid'])) {
					$range_cdid[] = $_range['view_range_cdid'];
					continue;
				}
				// 获取可见标签
				if (!empty($_range['view_range_label'])) {
					$range_label[] = $_range['view_range_label'];
					continue;
				}
			}
			// 判断是否在可见人员里
			if (!empty($range_uid) && in_array($m_uid, $range_uid)) {
				return true;
			}

			// 获取人员上级部门ID
			list($my_cdids, $up_cdids) = Department::instance()->list_cdid_by_uid($m_uid, true);
			// 判断所在部门是否在可见范围内
			if (array_intersect($my_cdids, $range_cdid) || array_intersect($up_cdids, $range_cdid)) {
				return true;
			}

			// 获取所在标签
			$serv_label = D('Common/CommonLabelMember', 'Service');
			$label_list = $serv_label->list_by_conds(array('m_uid' => $m_uid));
			$my_labels = array();
			foreach ($label_list as $_label) {
				$my_labels[] = $_label['laid'];
			}
			// 判断是否在范围内
			if (!empty($my_labels) && array_intersect($my_labels, $range_label)) {
				return true;
			}

			E('_ERR_NOT_IN_VIEW_RANGE');
			return false;
		}

		return true;
	}

	/**
	 * 验证回答是否符合题目设置规范
	 * @param $question
	 * @return bool
	 */
	protected function _validate($question) {

		// 接收回答数据
		$answer = I('post.formList');

		// 把ID 提取到KEY位置
		$temp = array();
		foreach ($answer as $_data) {
			$temp[$_data['id']] = $_data;
		}
		$answer = $temp;
		unset($temp);

		// 遍历题目设置判断
		$username = ''; // 人员姓名
		foreach ($question['field'] as $_field) {
			// 如果有这个题目的回答提交, 并且设置了必填 但是没有填
			$answer_value = empty($answer[$_field['id']]['value']) ? '' : $answer[$_field['id']]['value'];
			// 判断是否为空
			$empty_true = (empty($answer_value) && $answer_value != '0');
			if (isset($answer[$_field['id']]) && isset($_field['required']) && $_field['required'] == 'true' && $empty_true) {
				E(L('_ERR_REQUIRED_IS_EMPTY', array('name' => $_field['title'])));
			}

			// 如果题目不必填 并且值为空 则跳过
			if (isset($answer[$_field['id']]) && isset($_field['required']) && $_field['required'] == 'false' && $empty_true) {
				continue;
			}

			switch ($_field['type']) {
				case 'text':
					$this->__judge_strlen($_field, $answer_value);
					break;
				case 'textarea':
					$this->__judge_strlen($_field, $answer_value);
					break;
				case 'radio':
					$this->__judge_select($_field, $answer_value, self::RADIO_MAX_SELECT, null);
					break;
				case 'checkbox':
					$max = empty($_field['max']) ? 0 : $_field['max'];
					$min = empty($_field['min']) ? 0 : $_field['min'];
					$this->__judge_select($_field, $answer_value, $max, $min);
					break;
				case 'number':
					// 判断是否数字
					if (!is_numeric($answer_value)) {
						E(L('_ERR_ONLY_NUMBER', array('name' => $_field['title'])));
						return false;
					}
					// 判断数字大小
					if (!empty($_field['max']) && $answer_value > $_field['max']) {
						E(L('_ERR_OVER_NUMBER_RANGE_MAX', array('name' => $_field['title'], 'max' => $_field['max'])));
						return false;
					} else if (!empty($_field['min']) && $answer_value < $_field['min']) {
						E(L('_ERR_OVER_NUMBER_RANGE_MIN', array('name' => $_field['title'], 'min' => $_field['min'])));
						return false;
					}
					break;
				//				case 'date':
				//					$this->__judge_time($_field, $answer_value, $_field['type']);
				//					break;
				//				case 'time':
				//					$this->__judge_time($_field, $answer_value, $_field['type']);
				//					break;
				//				case 'datetime':
				//					$this->__judge_time($_field, $answer_value, $_field['type']);
				//					break;
				case 'score':
					// 判断是否数字
					if (!is_numeric($answer_value)) {
						E(L('_ERR_ONLY_NUMBER', array('name' => $_field['title'])));
						return false;
					}
					// 评分是否合法
					if ($answer_value > $_field['max'] || $answer_value < 0) {
						E(L('_ERR_SCORE_FORMAT_FAIL', array('name' => $_field['title'])));
						return false;
					}
					break;
				case 'image':
					$this->__judge_att($_field, $answer);
					break;
				case 'file':
					$this->__judge_att($_field, $answer);
					break;
				case 'select':
					$this->__judge_select($_field, $answer_value, self::RADIO_MAX_SELECT, null);
					break;
				case 'tel':
					if (!preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/', $answer_value)) {
						E(L('_ERR_FORMAT_ERROR', array('name' => $_field['title'])));
						return false;
					}
					break;
				case 'email':
					if (!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $answer_value)) {
						E(L('_ERR_FORMAT_ERROR', array('name' => $_field['title'])));
					}
					break;
				case 'address':
					// 判断地址的详情地址是否必填
					if (isset($_field['more']) && $_field['more'] == 'true' && !empty($answer_value)) {
						$address_array = explode('-', $answer_value);
						if (count($address_array) == 4 && empty($address_array[3])) {
							E(L('_ERR_ADDRESS_MORE_IS_EMPTY', array('name' => $_field['title'])));
							return false;
						}
					}
					break;
				case 'username':
					if (!empty($this->_login->user)) {
						// 是否匿名
						if ($question['anonymous'] == Questionnaire::ANONYMOUS) {
							$username = $answer_value;
						} else {
							$serv_mem = D('Common/Member', 'Service');
							$m_data = $serv_mem->get_by_conds(array('m_uid' => $this->_login->user['m_uid']));
							$username = $m_data['m_username'];
						}
					} else {
						$username = $answer_value;
					}
					break;
			}
		}

		return array($answer, trim($username));
	}

	//	/**
	//	 * 验证时间数据是否合法
	//	 * @param $_field
	//	 * @param $answer_value
	//	 * @param $type
	//	 * @return bool
	//	 */
	//	private function __judge_time($_field, $answer_value, $type) {
	//
	//		switch ($type) {
	//			case 'time':
	//				$time = rgmdate($answer_value, 'H:i');
	//				break;
	//			case 'date':
	//				$time = rgmdate($answer_value, 'Y-m-d');
	//				break;
	//			case 'datetime':
	//				$time = rgmdate($answer_value, 'Y-m-d H:i');
	//				break;
	//		}
	//
	//		if (empty($time)) {
	//			E(L('_ERR_TIME_FORMAT_FAIL', array('name' => $_field['title'])));
	//		}
	//
	//		return true;
	//	}

	/**
	 * 处理附件
	 * @param $_field
	 * @param $answer
	 * @return bool
	 */
	private function __judge_att($_field, &$answer) {

		if (empty($answer[$_field['id']]['value'])) {
			return true;
		}

		// 判断是否合法
		if (count($answer[$_field['id']]['value']) > $_field['max'] && $_field['type'] == 'image') {
			E(L('_ERR_IMAGE_OVER_MAX_TOTAL', array('name' => $_field['title'])));
			return false;
		}

		// URL处理不在这里做,因为后面要获取附件信息

		return true;
	}

	/**
	 * 判断字符串长度是否合法
	 * @param $_field
	 * @param $answer_value
	 * @return bool
	 */
	private function __judge_strlen($_field, $answer_value) {

		// 判断字符串最长长度
		if (isset($_field['max']) && mb_strlen($answer_value, 'UTF-8') > $_field['max']) {
			E(L('_ERR_OVER_MAXLENGTH', array('name' => $_field['title'], 'length' => $_field['max'])));
			return false;
		}
		// 判断字符串最少长度
		if (isset($_field['min']) && mb_strlen($answer_value, 'UTF-8') < $_field['min']) {
			E(L('_ERR_LESS_MINLENGTH', array('name' => $_field['title'], 'length' => $_field['min'])));
			return false;
		}

		return true;
	}

	/**
	 * 判断选项
	 * @param array $_field 题目规则
	 * @param array $answer_value 回答数据
	 * @param int $select_max_count 限制长度
	 * @param int $select_min_count 限制最少
	 * @return bool
	 */
	private function __judge_select($_field, $answer_value, $select_max_count, $select_min_count) {

		// 判断选项长度
		if (!empty($select_max_count) && count($answer_value) > $select_max_count) {
			E(L('_ERR_OVER_MAX_SELECT', array('name' => $_field['title'], 'count' => $select_max_count)));
			return false;
		}
		// 判断最少长度
		if (!empty($select_min_count) && count($answer_value) < $select_min_count) {
			E(L('_ERR_LESS_MIN_SELECT', array('name' => $_field['title'], 'count' => $select_min_count)));
			return false;
		}
		// 是否在选择范围内
		$option = $_field['option'];
		$select_ids = array_column($option, 'id');

		// 如果是多选
		if (count($answer_value) > self::RADIO_MAX_SELECT) {
			if (!array_intersect($answer_value, $select_ids)) {
				E(L('_ERR_NOT_IN_SELECT', array('name' => $_field['title'])));
				return false;
			}
		} else {
			// 如果是字符串, 转换成数组
			if (!is_array($answer_value) && is_string($answer_value)) {
				$answer_value = array(
					$answer_value
				);
			}
			if (!array_intersect($answer_value, $select_ids)) {
				E(L('_ERR_NOT_IN_SELECT', array('name' => $_field['title'])));
				return false;
			}
		}

		return true;
	}
}
