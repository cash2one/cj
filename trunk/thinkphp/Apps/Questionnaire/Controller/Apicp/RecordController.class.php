<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/3/23
 * Time: 下午8:36
 */

namespace Questionnaire\Controller\Apicp;

use Common\Common\Department;
use Common\Common\Pager;
use Questionnaire\Model\QuestionnaireModel;
use Sales\Common\Cache;
use Questionnaire\Model\QuestionnaireClassifyModel as QuestionnaireClassifyModel;

class RecordController extends AbstractController {

	/** 已填写 1 , 未填写 2 */
	const FILL_IN = 1;
	const UN_FILL_IN = 2;
	/** 每页导出数 */
	const MAX_LIMIT = 200;
	/** 部门缓存 */
	protected $_departments = array();

	/**
	 * 获取已填或者未填人员列表
	 * @return bool
	 */
	public function List_get() {

		$qu_id = I('get.qu_id', 0, 'intval');
		if (empty($qu_id)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');
			return false;
		}
		// 查看类型(已填或者未填)
		$fill = I('get.fill_in', 1, 'intval');
		// 分页参数
		$page = I('get.page', 1, 'intval');
		$limit = I('get.limit', 10, 'intval');
		list($start, $limit, $page) = page_limit($page, $limit);
		$page_option = array($start, $limit);

		$serv_qu = D('Questionnaire/Questionnaire', 'Service');
		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');

		// 查询问卷
		$question = $serv_qu->get($qu_id);
		if (empty($question)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');
			return false;
		}

		// 如果查询未填
		$total = 0;
		$user_list = array();
		if ($fill == self::UN_FILL_IN) {

			list($user_list, $total) = $serv_record->list_user_unfill_in($qu_id, $page_option);
		} else {
			// 查询问卷填写记录
			$field = array('username', 'created', 'uid', 'qr_id');
			$user_list = $serv_record->allList_filed_by_condition($qu_id, $field, $page_option, array('created' => 'DESC'));
			$total = $serv_record->count_by_conds(array('qu_id' => $qu_id));
		}

		// 匿名去掉姓名
		if ($question['anonymous'] == QuestionnaireModel::ANONYMOUS) {
			foreach ($user_list as &$_user) {
				$_user['username'] = '匿名';
				$_user['email'] = '---';
				$_user['phone'] = '---';
			}
		}

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
			'list' => $user_list,
			'multi' => $multi,
		);

		return true;
	}

	/**
	 * 删除问卷回答
	 * @return bool
	 */
	public function Del_post() {

		$qr_id = I('post.qr_id', 0, 'intval');
		if (empty($qr_id)) {
			E('_ERR_MISS_RECORD');
			return false;
		}

		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
		$serv_record->delete($qr_id);

		return true;
	}

	/**
	 * 问卷填写提醒
	 */
	public function questionnaireSend_post() {

		$qu_id = I('post.qu_id', '', 'intval');
		if (empty($qu_id)) {
			E('_ERR_NO_EXIST_QUESTIONNAIRE');

			return false;
		}
		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
		$serv_record->list_user_unwirte_send($qu_id);

		return true;
	}

	/**
	 *导出未填写人员数据
	 */
	public function Export_nofill() {

		$qu_id = I('get.qu_id', 0, 'intval');
		if (empty($qu_id)) {
			exit('丢失问卷ID');
		}
		$serv_qu = D('Questionnaire/Questionnaire', 'Service');
		$question = $serv_qu->get_by_conds(array('qu_id' => $qu_id));
		if ($question['anonymous'] == QuestionnaireModel::ANONYMOUS) {
			exit('该问卷为匿名填写');
		}

		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
		$total = $serv_record->count_user_unfill_in($qu_id);

		// 初始化 压缩
		$zip = new \ZipArchive();
		// 路径和文件名
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'nofill' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);

		if (file_exists($zipname)) {
			@unlink($zipname);
		}

		if ($total <= 0) {
			// 导出空文件
			$result = $this->__create_csv(array(), 1, $path);
			if ($result) {
				$zip->addFile($result, 1 . '.csv');
			}
		} else {
			// 张数
			$times = ceil($total / self::MAX_LIMIT);

			for ($i = 1; $i <= $times; $i ++) {
				list($start, $limit, $i) = page_limit($i, self::MAX_LIMIT, self::MAX_LIMIT);
				list($list, $total) = $serv_record->list_user_unfill_in($qu_id, array($start, $limit));

				// 获取关联部门ID
				$uids = array_column($list, 'uid');

				// 获取人员信息
				$this->__get_mem_dep_in_chinese($uids, $list, false, array());

				$result = $this->__create_csv($list, $i, $path);
				if ($result) {
					$zip->addFile($result, $i . '.csv');
				}
			}
		}

		// 下载 并 清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);

		return true;
	}

	/**
	 * 生成csv文件
	 */
	private function __create_csv($list, $i, $path) {

		// 生成文件
		if (!is_dir($path)) {
			rmkdir($path);
		}
		$data = array();

		$filename = $i . '.csv';
		$data[0] = array(
			'姓名',
			'部门',
			'手机号码',
			'邮箱',
		);

		if (!empty($list)) {
			foreach ($list as $val) {

				$temp = array(
					'username' => $val['username'],
					'dep' => $val['dep'],
					'phone' => $val['phone'],
					'email' => $val['email'],
				);

				$data[] = $temp;
			}
		}

		$csv_data = $this->_array2csv($data);
		$fp = fopen($path . $filename, 'w');
		fwrite($fp, $csv_data); // 写入数据
		fclose($fp); // 关闭文件句柄

		return $path . $filename;
	}

	/**
	 * 获取填写详情数据
	 * @return bool
	 */
	public function View_answer_get() {

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

		// 是否匿名填写
		$view = $serv_record->merge_field_answer(json_decode($naire['field'], true), json_decode($record['answer'], true));

		// 分类名称
		$qc_name = '';
		if (!empty($naire['qc_id'])) {
			$serv_qc = D('Questionnaire/QuestionnaireClassify', 'Service');
			$qc_name = $serv_qc->get_by_conds(array('qc_id' => $naire['qc_id']));
		}
		$this->_result = array(
			'title' => $naire['title'],
			'body' => $naire['body'],
			'created' => rgmdate($naire['created'], 'Y-m-d H:i'),
			'classify' => empty($qc_name['name']) ? QuestionnaireClassifyModel::CN_NO_CLASSIFY : $qc_name['name'],
			'view' => $view,
		);

		return true;
	}

	/**
	 * 导出填写的问卷回答
	 */
	public function Export_fill() {

		$qu_id = I('get.qu_id', 0, 'intval');
		if (empty($qu_id)) {
			exit('丢失问卷ID');
		}

		// 获取问卷
		$serv_qu = D('Questionnaire/Questionnaire', 'Service');
		$question = $serv_qu->get_by_conds(array('qu_id' => $qu_id));
		if (empty($question)) {
			exit('没有这张问卷');
		}

		// 获取已填写的总数
		$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
		$total = $serv_record->count_by_conds(array('qu_id' => $qu_id));

		// 初始化 压缩
		$zip = new \ZipArchive();
		// 路径和文件名
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'fill' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);

		if (file_exists($zipname)) {
			@unlink($zipname);
		}

		if ($total <= 0) {
			// 导出空文件
			$result = $this->__create_fillin_csv(array(), 1, $path, $question);
			if ($result) {
				$zip->addFile($result, 1 . '.csv');
			}
		} else {
			// 张数
			$times = ceil($total / self::MAX_LIMIT);

			for ($i = 1; $i <= $times; $i ++) {
				list($start, $limit, $i) = page_limit($i, self::MAX_LIMIT, self::MAX_LIMIT);
				$list = $serv_record->list_by_conds(array('qu_id' => $qu_id), array($start, $limit), array('uid' => 'DESC'));

				$result = $this->__create_fillin_csv($list, $i, $path, $question, $start);
				if ($result) {
					$zip->addFile($result, $i . '.csv');
				}
			}
		}

		// 下载 并 清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);

		return true;
	}

	/**
	 * 生成csv文件
	 * @param     $list
	 * @param     $i
	 * @param     $path
	 * @param     $quesion
	 * @param int $start
	 * @return string
	 */
	private function __create_fillin_csv($list, $i, $path, $quesion, $start = 0) {

		// 生成文件
		if (!is_dir($path)) {
			rmkdir($path);
		}
		$data = array();

		$filename = $i . '.csv';
		// 问卷标题
		$data[0] = array(htmlspecialchars_decode($quesion['title']));
		/** 生成导出文件的题目(格式为 第一行是题目名, 第二行为选择类型的选择项的名称) */
		$field = json_decode($quesion['field'], true);
		$data[1] = array('序号');
		$data[2] = array(''); // 问卷选项
		// 如果是实名制,在开头加上内部人员信息
		$real_name_true = $quesion['anonymous'] == QuestionnaireModel::REAL_NAME;
		if ($real_name_true) {
			$data[1][] = '部门';
			$data[1][] = '姓名';
			$data[1][] = '邮箱';
			$data[1][] = '手机号';
			$data[1][] = '人员类型';
			$data[2] = array_fill(3, 6, '');
		} else {
			$data[1][] = '人员类型';
			$data[2][] = '';
		}
		$data[1][] = '填写时间';
		$data[2][] = '';

		// 遍历问卷题目
		foreach ($field as $_field) {
			// 实名制 去掉问卷里的答案
			if ($real_name_true && in_array($_field['type'], array('username', 'email', 'mobile'))) {
				continue;
			}
			// 如果是选项类型的
			if (in_array($_field['type'], array('select', 'checkbox', 'radio'))) {
				$option_count = count($_field['option']);
				$data[1][] = htmlspecialchars_decode($_field['title']);
				$data[2][] = htmlspecialchars_decode($_field['option'][0]['value']);
				for($i = 1; $i < $option_count; $i ++) {
					$data[1][] = ''; // 为选项留空
					$data[2][] = $_field['option'][$i]['value'];
				}
			} else {
				$data[1][] = $_field['title'];
				$data[2][] = '';
			}
		}

		/** 内容处理(若是没有回答问题则留空,反之填写;对选择类型的数据进行特殊处理:留 选择项数量 的空, 反之填写) */
		if (!empty($list)) {
			$serv_record = D('Questionnaire/QuestionnaireRecord', 'Service');
			// 获取内部人员数据
			if ($real_name_true) {
				$uid_list = array_column($list, 'uid');
				if (!empty($uid_list)) {
					$serv_mem = D('Common/Member', 'Service');
					$user_list = $serv_mem->list_by_conds(array('m_uid' => $uid_list));

					$this->__get_mem_dep_in_chinese($uid_list, $list, true, $user_list);
				}
			}

			// 合并问卷 回答
			foreach ($list as $_key => $_data) {
				$number = $_key + 1;
				$answer_data_number = 2 + $number; // csv数组键值 回答部分的开始位置

				$answer = json_decode($_data['answer'], true);
				// 问卷设置和回答进行合并
				$answer = $serv_record->merge_field_answer($field, $answer);

				// 重组回答数组格式(ID 提取到键值)
				$temp = array();
				foreach ($answer as $_id) {
					if (!empty($_id['value'])) {
						$_id['value'] = str_replace(array("\n"), ' ', $_id['value']);
					}
					$temp[$_id['id']] = $_id;
				}
				$answer = $temp;
				unset($temp);

				/** 此行数据填充开始 */
				// 序号
				$data[$answer_data_number] = array(
					$number + $start
				);
				// 如果实名制谈填写, 获取内部人员数据
				if ($real_name_true) {
					if (!empty($_data['uid'])) {
						$data[$answer_data_number][] = empty($_data['dep']) ? '' : $_data['dep'];
						$data[$answer_data_number][] = empty($_data['username']) ? '' : $_data['username'];
						$data[$answer_data_number][] = empty($_data['email']) ? '' : $_data['email'];
						$data[$answer_data_number][] = empty($_data['mobile']) ? '' : $_data['mobile'];
						// 人员类型
						if (!empty($_data['uid'])) {
							$data[$answer_data_number][5] = '内部人员';
						} else {
							$data[$answer_data_number][5] = '外部人员';
						}
					} else {
						$data[$answer_data_number] = array_merge($data[$answer_data_number], array_fill(1, 5, ''));
					}
				} else {
					// 人员类型
					if (!empty($_data['uid'])) {
						$data[$answer_data_number][2] = '内部人员';
					} else {
						$data[$answer_data_number][2] = '外部人员';
					}
				}
				// 填写时间
				$data[$answer_data_number][] = rgmdate($_data['created'], 'Y-m-d H:i');
				// 填充回答
				$this->__questionnaire_field($field, $real_name_true, $answer, $answer_data_number, $data);
			}
		}

		$csv_data = $this->_array2csv($data);
		$fp = fopen($path . $filename, 'w');
		fwrite($fp, $csv_data); // 写入数据
		fclose($fp); // 关闭文件句柄

		return $path . $filename;
	}

	/**
	 * 处理问卷设置
	 * @param array $field 问卷题目设置
	 * @param bool $real_name_true 是否实名填写
	 * @param array $answer 回答
	 * @param int $answer_data_number 回答对应的导出数组键值
	 * @param array $data 导出数组
	 * @return mixed
	 */
	private function __questionnaire_field($field, $real_name_true, $answer, $answer_data_number, &$data) {

		// 遍历问卷设置
		foreach ($field as $_field) {
			// 如果实名制 并且问卷里有姓名/邮箱/手机号等字段 覆盖之前的默认数据
			if ($real_name_true) {
				switch ($_field['type']) {
					case 'username':
						if (!empty($answer[$_field['id']]['value'])) {
							$data[$answer_data_number][2] = $answer[$_field['id']]['value'];
						}
						break;
					case 'email':
						if (!empty($answer[$_field['id']]['value'])) {
							$data[$answer_data_number][3] = $answer[$_field['id']]['value'];
						}
						break;
					case 'mobile':
						if (!empty($answer[$_field['id']]['value'])) {
							$data[$answer_data_number][4] = $answer[$_field['id']]['value'];
						}
						break;
				}
				if (in_array($_field['type'], array('username', 'email', 'mobile'))) {
					continue;
				}
			}
			// 如果是选择类型的回答
			if (in_array($_field['type'], array('select', 'checkbox', 'radio'))) {

				// 对回答数组进行ID的重组
				$temp = array();
				foreach ($answer[$_field['id']]['option'] as $_answer_option) {
					$temp[$_answer_option['id']] = $_answer_option;
				}
				$answer[$_field['id']]['option'] = $temp;
				unset($temp);

				foreach ($_field['option'] as $_option) {
					// 如果有回答
					if (array_key_exists($_option['id'], $answer[$_field['id']]['option'])) {
						if ($answer[$_field['id']]['option'][$_option['id']]['selected']) {
							// 判断是否有其他选项
							if ($answer[$_field['id']]['option'][$_option['id']]['other']) {
								$data[$answer_data_number][] = '√ (' . $answer[$_field['id']]['option'][$_option['id']]['other_value'] . ')';
							} else {
								$data[$answer_data_number][] = '√';
							}
						} else {
							$data[$answer_data_number][] = '';
						}
					} else {
						// 没有回答就留空
						$data[$answer_data_number][] = '';
					}
				}
			} elseif ($_field['type'] == 'image') {
				// 处理附件URL地址
				if (isset($answer[$_field['id']])) {
					// 转换附件地址
					$data[$answer_data_number][] = implode(';', $answer[$_field['id']]['value']);
				} else {
					$data[$answer_data_number][] = '';
				}
			} elseif ($_field['type'] == 'file') {
				// 处理附件URL地址
				$data[$answer_data_number][] = !empty($answer[$_field['id']]['url']) ? $answer[$_field['id']]['url'] : '';
			} else {
				if (isset($answer[$_field['id']])) {
					$data[$answer_data_number][] = empty($answer[$_field['id']]['value']) ? '' : $answer[$_field['id']]['value'];
				} else {
					$data[$answer_data_number][] = '';
				}
			}
		}

		return true;
	}

	/**
	 * 获取人员列表 对应的 多部门名称
	 * @param array $uid_list 人员ID 数组
	 * @param array $list 要加入数据的数组
	 * @param bool $add_user_data 是否加入人员数据
	 * @param array $user_list 人员数据
	 * @return bool
	 */
	private function __get_mem_dep_in_chinese($uid_list, &$list, $add_user_data = false, $user_list) {

		// 获取关联部门ID
		$serv_mem_dep = D('Common/MemberDepartment', 'Service');
		$mem_dep = $serv_mem_dep->list_by_conds(array('m_uid' => $uid_list));
		// 获取级部门的上级部门
		$cd_ids = array_unique(array_column($mem_dep, 'cd_id'));
		$dep_list = $this->_list_up_cdname($cd_ids);

		// 如果要获取人员数据 , 把人员ID 提取到KEY位置
		if ($add_user_data) {
			$temp = array();
			foreach ($user_list as $_user) {
				$temp[$_user['m_uid']] = $_user;
			}
			$user_list = $temp;
		}

		// 关联部门ID
		foreach ($list as &$_list) {
			foreach ($mem_dep as $_dep) {
				if ($_list['uid'] == $_dep['m_uid']) {
					$_list['dep'][] = $dep_list[$_dep['cd_id']];
				}
			}

			// 加入部门
			if (!empty($_list['dep'])) {
				$_list['dep'] = implode('; ', $_list['dep']);
			} else {
				$_list['dep'] = '';
			}

			// 获取内部人员数据
			if ($add_user_data) {
				$_list['username'] = '';
				$_list['email'] = '';
				$_list['mobile'] = '';

				if (!empty($_list['uid'])) {
					// 获取人员数据
					$user_data = $user_list[$_list['uid']];
					$_list['username'] = $user_data['m_username'];
					$_list['email'] = $user_data['m_email'];
					$_list['mobile'] = $user_data['m_mobilephone'];
				}
			}
		}

		return true;
	}

	/**
	 * 获取部门的上级部门名称
	 * @param $cd_ids
	 * @return array
	 */
	protected function _list_up_cdname($cd_ids) {

		if (empty($this->_departments)) {
			// 读取部门缓存
			$cache = &\Common\Common\Cache::instance();
			$this->_departments = $cache->get('Common.department');
		}

		if (!is_array($cd_ids) || empty($cd_ids)) {
			return array();
		}

		$dep_list = array();
		foreach ($cd_ids as $_dep) {
			Department::instance()->list_parent_cdids($_dep, $up_dep);
			// 建立部门对应的上级部门名称数组
			foreach ($up_dep as $_up_dep) {
				$dep_list[$_dep][] = $this->_departments[$_up_dep]['cd_name'];
			}
			$dep_list[$_dep][] = $this->_departments[$_dep]['cd_name'];
		}

		foreach ($dep_list as &$_list) {
			$_list = implode('->', $_list);
		}

		return $dep_list;
	}

	/**
	 * 下载输出至浏览器
	 */
	private function _put_header($zipname) {

		if (!file_exists($zipname)) {
			exit("下载失败");
		}
		$file = fopen($zipname, "r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . filesize($zipname));
		Header("Content-Disposition: attachment; filename=" . basename($zipname));
		echo fread($file, filesize($zipname));
		$buffer = 1024;
		while (!feof($file)) {
			$file_data = fread($file, $buffer);
			echo $file_data;
		}
		fclose($file);
	}

	/**
	 * 清理产生的临时文件
	 */
	private function _clear($path) {

		$dh = opendir($path);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				unlink($path . $file);
			}
		}
	}

	/**
	 * 将一组整齐的数组转换为csv字符串
	 * @param array $list 待转换的数组列表数据
	 * @param string $newline_symbol 每行之间的分隔符号，默认为：“\r\n”
	 * @param string $field_comma 字段之间的分隔符号，默认为：“,”
	 * @param string $field_quote_symbol 字段的引用符号，默认为：“"”
	 * @param string $out_charset 输出的数据字符集编码，默认为：gbk
	 * @return string
	 */
	protected function _array2csv(array $list, $newline_symbol = "\r\n", $field_comma = ",", $field_quote_symbol = '"', $out_charset = 'gbk') {

		// 初始化输出
		$data = '';
		// 初始化换行符号
		$_row_comma = '';

		// 遍历所有行数据
		foreach ($list as $_arr_row) {

			// 初始化行数据
			$_row = '';
			// 初始化每个字段的分隔符号
			$_comma = '';

			// 遍历所有字段
			foreach ($_arr_row as $_str) {
				// 字段数据分隔符
				$_row .= $_comma;
				if (strpos($_str, $field_comma) === false) {
					// 字段数据不包含字段分隔符，直接使用
					$_row .= $_str;
				} else {
					// 字段数据包含字段分隔符，则使用字段引用符号引用并转义数据内的引用符号
					$_row .= $field_quote_symbol.addcslashes($_str, $field_quote_symbol).$field_quote_symbol;
				}
				// 定义字段分隔符号
				$_comma = $field_comma;
			}

			// 行数据，以行分隔符号连接
			$data .= $_row_comma.$_row;

			// 定义换行符号
			$_row_comma = $newline_symbol;
		}

		// 输出数据
		return $this->_riconv($data, 'UTF-8', $out_charset);
	}

	/**
	 * 转换编码
	 * @param mixed $m
	 * @param string $from
	 * @param string $to
	 * @return mixed
	 */
	protected  function _riconv($m, $from = 'UTF-8', $to = 'GBK'){
		if ( strpos($to, '//') === false ) {
			$to	=	$to.'//IGNORE';
		}
		switch ( gettype($m) ) {
			case 'integer':
			case 'boolean':
			case 'float':
			case 'double':
			case 'NULL':
				return $m;
			case 'string':
				return @iconv($from, $to, $m);
			case 'object':
				$vars = array_keys(get_object_vars($m));
				foreach($vars AS $key) {
					$m->$key = $this->_riconv($m->$key, $from ,$to);
				}
				return $m;
			case 'array':
				foreach($m AS $k => $v) {
					$k2	=	$this->_riconv($k, $from, $to);
					if ( $k != $k2 ) {
						unset($m[$k]);
					}
					$m[$k2] = $this->_riconv($v, $from, $to);
				}
				return $m;
			default:
				return '';
		}
	}
}
