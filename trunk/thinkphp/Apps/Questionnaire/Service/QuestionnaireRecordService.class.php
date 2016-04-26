<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/21
 * Time: 下午4:08
 */
namespace Questionnaire\Service;

use Common\Common\WxqyMsg;
use Questionnaire\Model\QuestionnaireModel as QuestionnaireModel;
use Think\Log;

class QuestionnaireRecordService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Questionnaire/QuestionnaireRecord");
	}

	// 全局设置
	protected $_setting = array();

	/**
	 * 我的问卷列表
	 * @param array $request
	 * @param array $page_option
	 * @param array $order_option
	 * @return mixed
	 */
	public function questionnaireMyList(array $request, $page_option = array(), $order_option = array()) {

		return $this->_d->myList_by_condition($request, $page_option, $order_option);
	}

	/**
	 * 问卷列表总数
	 * @param array $request
	 * @return mixed
	 */
	public function questionnaireMyListTotal(array $request) {

		return $this->_d->myTotal_by_condition($request);
	}

	/**
	 * 格式化我的问卷
	 * @param array $request
	 * @param array $result
	 * @return bool
	 */
	public function formatQuestionnaireMyList(array $request, array &$result) {

		if ($request) {
			foreach ($request as $key => $val) {
				$result[$key]['qr_id']    = $val['qr_id'];
				$result[$key]['qu_id']    = $val['qu_id'];
				$result[$key]['title']    = htmlspecialchars_decode($val['title']);
				$result[$key]['deadline'] = (int)$val['deadline'];
				$result[$key]['is_end']   = 1;
				if (NOW_TIME > $val['deadline']) {
					$result[$key]['is_end'] = 2; //已结束
				}
				$result[$key]['anonymous'] = (int)$val['anonymous'];
				$result[$key]['repeat']    = (int)$val['repeat'];
			}
		}

		return true;
	}

	/**
	 * 查询所有数据里的字段数据
	 * @param $qu_id
	 * @param $field
	 * @param $page_option
	 * @param $order_option
	 * @return mixed
	 */
	public function allList_filed_by_condition($qu_id, $field, $page_option, $order_option) {

		$record = $this->_d->allList_filed_by_condition($qu_id, $field, $page_option, $order_option);
		if (empty($record)) {
			return array();
		}
		// 格式化
		$temp = array();
		foreach ($record as $_key => $_record) {
			$temp[] = array(
				'number'    => $_key + $page_option[0] + 1, // 序号
				'username'  => $_record['username'],
				'from'      => empty($_record['uid']) ? '外部人员' : '内部人员',
				'created'   => rgmdate($_record['created'], 'Y-m-d H:i'),
				'operation' => '<span act="see" id="' . $_record['qr_id'] . '" style="color: #4083A9; cursor: pointer;"><i class="fa fa-eye"></i> 填写情况</span>  |  <span act="del" style="cursor: pointer;" class="text-danger _delete" id="' . $_record['qr_id'] . '"><i class="fa fa-times"></i> 删除</span>',
			);
		}

		return $temp;
	}

	/**
	 * 获取未填写用户列表
	 * @param $qu_id
	 * @param $page_option
	 * @return array
	 */
	public function list_user_unfill_in($qu_id, $page_option) {

		$model_mem    = D('Common/Member');
		$model_qu     = D('Questionnaire/Questionnaire');
		$model_record = D('Questionnaire/QuestionnaireRecord');
		// 获取问卷主数据
		$question = $model_qu->get($qu_id);
		// 查询已填人员
		$record_user      = $model_record->list_by_conds(array('qu_id' => $qu_id, 'uid !' => 0));
		$record_user_list = array();
		if (!empty($record_user)) {
			$record_user_list = array_column($record_user, 'uid');
		}
		// 如果是所有人
		$total     = 0;
		$user_list = array();
		if ($question['is_all'] == QuestionnaireModel::IS_ALL) {
			if (!empty($record_user_list)) {
				$conds     = array(
					'm_uid NOT' => $record_user_list
				);
			}
			$user_list = $model_mem->list_by_conds($conds, $page_option);
			$total     = $model_mem->count_by_conds($conds);
		} else {
			// 查询可见范围
			$uid_list  = $this->__list_uid_view_range($qu_id);
			if (empty($uid_list)) {
				return array(array(), 0);
			}
			// 去掉已经填写的人员
			if (!empty($record_user_list)) {
				$uid_list = array_diff($uid_list, $record_user_list);
			}
			if (empty($uid_list)) {
				return array(array(), 0);
			}

			$user_list = $model_mem->list_by_conds(array('m_uid' => $uid_list), $page_option);
			$total     = count(array_unique($uid_list));
		}
		if (!empty($user_list)) {
			// 格式化
			$temp = array();
			foreach ($user_list as $_key => $_record) {
				$temp[] = array(
					'uid'      => $_record['m_uid'],
					'number'   => $_key + $page_option[0] + 1, // 序号
					'username' => $_record['m_username'],
					'phone'    => empty($_record['m_mobilephone']) ? '' : $_record['m_mobilephone'],
					'email'    => empty($_record['m_email']) ? '' : $_record['m_email'],
				);
			}
			$user_list = $temp;
		}

		return array($user_list, $total);
	}

	/**
	 * 消息提醒发送未填写人员
	 */
	public function list_user_unwirte_send($qu_id) {

		$model_mem    = D('Common/Member');
		$model_plugin = D('Common/CommonPlugin');
		$model_qu     = D('Questionnaire/Questionnaire');
		$model_record = D('Questionnaire/QuestionnaireRecord');
		// 读取插件信息
		$plugin = $model_plugin->get_by_identifier('questionnaire');
		// 获取问卷主数据
		$question = $model_qu->get($qu_id);
		// 查询已填人员
		$record_user      = $model_record->list_by_conds(array('qu_id' => $qu_id, 'uid !' => '0'));
		// 获取所有未填写人员的总数
		$total = $this->count_user_unfill_in($qu_id);

		// 获取url
		$cache   = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.setting');

		$url         = cfg('PROTOCAL') . $setting['domain'] . '/newh5/questionnaire/index.html?#/app/page/questionnaire/questionnaire-form?qu_id=' . $question['qu_id'];
		$end         = rgmdate($question['deadline'], 'Y-m-d H:i');
		$desc        = "您有一个问卷需要填写，请于{$end}前填写完毕";

		if ($question['is_all'] == QuestionnaireModel::IS_ALL && empty($record_user)) {
			$result = WxqyMsg::instance()->send_news($question['title'], $desc, $url, '@all', '', '', $plugin['cp_agentid'], $plugin['cp_pluginid']);
			Log::record('消息推送all,result-------' . $result, Log::INFO);

			return true;
		}

		// 推送指定人员
		$times = ceil($total / 100); // 每次插入100条
		for ($i = 1; $i <= $times; $i ++) {
			list($start, $limit, $i) = page_limit($i, 100, 100);
			$page_option = array($start, $limit);

			// 获取未填人员列表
			list($user_list) = $this->list_user_unfill_in($qu_id, $page_option);
			if (empty($user_list)) {
				return true;
			}
			$uids = array_column($user_list, 'uid');

			// 发送消息
			$result      = WxqyMsg::instance()->send_news($question['title'], $desc, $url, $uids, '', '', $plugin['cp_agentid'], $plugin['cp_pluginid']);
			Log::record('消息推送,result-------' . $result, Log::INFO);
		}

		return true;
	}

	/**
	 * 查询未填人员总数
	 * @param $qu_id
	 * @return int
	 */
	public function count_user_unfill_in($qu_id) {

		$model_mem    = D('Common/Member');
		$model_qu     = D('Questionnaire/Questionnaire');
		$model_record = D('Questionnaire/QuestionnaireRecord');
		// 获取问卷主数据
		$question = $model_qu->get($qu_id);
		// 查询已填人员
		$record_user      = $model_record->list_by_conds(array('qu_id' => $qu_id));
		$record_user_list = array();
		if (!empty($record_user)) {
			$record_user_list = array_column($record_user, 'uid');
		}
		// 如果是所有人
		$total = 0;
		if ($question['is_all'] == QuestionnaireModel::IS_ALL) {
			$conds = array();
			if (!empty($record_user_list)) {
				$conds = array(
					'm_uid NOT' => $record_user_list
				);
			}
			$total = $model_mem->count_by_conds($conds);
		} else {
			// 查询可见范围
			$uid_list = $this->__list_uid_view_range($qu_id);
			$total    = count(array_unique($uid_list));
		}

		return $total;
	}

	/**
	 * 根据问卷ID获取可见范围人员ID列表
	 * @param $qu_id
	 * @return array|bool
	 */
	private function __list_uid_view_range($qu_id) {

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
	 * 合并问卷和 回答
	 * @param      $field
	 * @param      $answer
	 * @return array
	 */
	public function merge_field_answer($field, $answer) {

		// 读取全局缓存
		$cache = \Common\Common\Cache::instance();
		$this->_setting = $cache->get('Common.setting');

		if (empty($field) || empty($answer) || !is_array($field) || !is_array($answer)) {
			return array();
		}

		// 重组answer 数组格式
		$temp = array();
		foreach ($answer as $_data) {
			if (empty($_data['id'])) {
				continue;
			}
			$temp[$_data['id']] = $_data;
		}
		$answer = $temp;
		unset($temp);

		// 文字类处理
		$text_type = array('address', 'email', 'text', 'textarea', 'number', 'score', 'note', 'mobile', 'username', 'date', 'time', 'datetime');
		// 选择类型
		$select_type = array('radio', 'select');
		foreach ($field as &$_field) {
			// 修正布尔值
			$_field['required'] = $_field['required'] == 'true' ? true : false;
			// 修正题目标题html转义
			$_field['title'] = htmlspecialchars_decode($_field['title']);
			// 修正placeholder的html转义
			if (!empty($_field['placeholder'])) {
				$_field['placeholder'] = htmlspecialchars_decode($_field['placeholder']);
			}

			// 判断是否文字类
			if (in_array($_field['type'], $text_type)) {
				$_field = $this->__deal_text($_field, $answer);
				continue;
			}
			// 判断是否选择类
			if (in_array($_field['type'], $select_type)) {
				$_field = $this->__deal_select($_field, $answer);
				continue;
			}
			// 处理多选
			if ($_field['type'] == 'checkbox') {
				$_field = $this->__deal_checkbox($_field, $answer);
				continue;
			}
			// 处理图片
			if ($_field['type'] == 'image') {
				if (!empty($answer[$_field['id']]['value'])) {
					// 转换附件地址
					foreach ($answer[$_field['id']]['value'] as &$_fujian) {
						$_fujian = cfg('PROTOCAL') . $this->_setting['domain'] . '/attachment/read/' . $_fujian;
					}
					$_field['value'] = $answer[$_field['id']]['value'];
				} else {
					$_field['value'] = empty($answer[$_field['id']]['value']) ? '' : $answer[$_field['id']]['value'];
				}

				continue;
			}
			// 处理文件
			if ($_field['type'] == 'file') {
				if (!empty($answer[$_field['id']]['value'])) {
					$serv_att = D('Common/CommonAttachment', 'Service');
					$att = $serv_att->get_by_conds(array('at_id' => $answer[$_field['id']]['value']));
					// 文件名
					$_field['value'] = !empty($att['at_filename']) ? $att['at_filename'] : '';
					// 文件URL
					$_field['url'] = cfg('PROTOCAL') . $this->_setting['domain'] . '/attachment/read/' . $answer[$_field['id']]['value'];
				} else {
					$_field['value'] = '';
					$_field['url'] = '';
				}
			}
		}

		return $field;
	}

	/**
	 * 处理文字类赋值
	 * @param $_field
	 * @param $answer
	 * @return bool
	 */
	private function __deal_text($_field, $answer) {

		if (empty($answer[$_field['id']]['value']) && $answer[$_field['id']]['value'] != '0' && $_field['type'] != 'note') {
			$_field['value'] = '';
			return $_field;
		}

		if ($_field['type'] == 'note') {
			$_field['value'] = empty($_field['placeholder']) ? '' : $_field['placeholder'];
		} else {
			$_field['value'] = $answer[$_field['id']]['value'];
		}

		return $_field;
	}

	/**
	 * 处理单项选择类型的
	 * @param $_field
	 * @param $answer
	 * @return mixed
	 */
	private function __deal_select($_field, $answer) {

		foreach ($_field['option'] as &$_value) {
			if (isset($_value['other'])) {
				$_value['other'] = $_value['other'] == 'true' ? true : false;
			}

			// 名称为空
			if (empty($_value['value']) && $_value['other']) {
				$_value['value'] = '其他';
			}
			// 找出选中的选项
			if ($_value['id'] == $answer[$_field['id']]['value'][0]) {
				// 标记选中
				$_value['selected'] = true;
				// 是否有其他选项
				if (isset($_value['other']) && $_value['other']) {
					// 赋值'其他'
					$_value['other_value'] = empty($answer[$_field['id']]['other_value']) ? '' : $answer[$_field['id']]['other_value'];
					$_field['other_value'] = empty($answer[$_field['id']]['other_value']) ? '' : $answer[$_field['id']]['other_value'];
				}
				// 获取选中的选项名称 (H5下拉框显示选中用)
				$_field['value'] = empty($_value['value']) ? '' : $_value['value'];
			} else {
				$_value['selected'] = false;
			}
		}

		return $_field;
	}

	/**
	 * 处理多项选择类型的
	 * @param $_field
	 * @param $answer
	 * @return bool
	 */
	private function __deal_checkbox($_field, $answer) {

		// 遍历选项
		foreach ($_field['option'] as &$_value) {
			if (!isset($answer[$_field['id']])) {
				break;
			}
			// 名称为空
			if (empty($_value['value']) && $_value['other'] == 'true') {
				$_value['value'] = '其他';
			}

			// 判断是否勾选
			if (in_array($_value['id'], $answer[$_field['id']]['value'])) {
				$_value['selected'] = true;

				// 判断是否有其他
				if (isset($_value['other'])) {
					$_value['other_value'] = empty($answer[$_field['id']]['other_value']) ? '' : $answer[$_field['id']]['other_value'];
				}
			}
		}

		return $_field;
	}
}