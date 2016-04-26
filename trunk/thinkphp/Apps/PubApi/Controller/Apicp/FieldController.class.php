<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/29
 * Time: 下午3:28
 */

namespace PubApi\Controller\Apicp;

use Common\Common\Department;
use Common\Common\Cache;
use Common\Common\Wxqy;

class FieldController extends AbstractController {

	/** 规则 (开启) */
	const ALLOW = 1;
	/** 规则 (关闭) */
	const UNALLOW = 0;
	/** checkbox 规则 */
	const CHECK_OPEN = 'open';
	const CHECK_REQUIRED = 'required';
	const GENDER_UNKNOWN = 0;//未知
	const GENDER_MALE = 1;//男
	const GENDER_FEMALE = 2;//女

	/** 临时导入的数据储存路径 */
	private $__tmp_data_path = '';
	/** 可允许的动作集 */
	private $__action_names = array(
		'downloadtpl' => '下载模板文件',
		'batch' => '批量导入',
		'uploadexcel' => '上传 Excel 文件',
		'import' => '导入用户数据',
		'batchsubmit' => '批量提交',
		'resubmit' => '重新提交',
	);
	/** 模板字段定义 */
	public $__fields = array();

	/**
	 * 批量添加用户
	 */
	public function Batch_post() {

		$post = I('post.');
		$submit['weixinid'] = isset($post['weixinid']) ? trim($post['weixinid']) : '';
		$submit['mobile'] = isset($post['mobile']) ? trim($post['mobile']) : '';
		$submit['email'] = isset($post['email']) ? trim($post['email']) : '';
		$submit['name'] = isset($post['name']) ? trim($post['name']) : '';
		//电话/邮箱/微信不能同时为空
		if (empty($submit['mobile']) && empty($submit['email']) && empty($submit['weixinid'])) {
			E('_ERR_EMPTY_WX_MOBILE_EMAIL');
			return false;
		}
		if (empty($submit['name'])) {
			E('_ERR_EMPTY_USERNAME');
			return false;
		}

		//不生成openid
		$openid = '';
		$submit['userid'] = !empty($post['userid']) ? trim($post['userid']) : $openid;

		$submit['position'] = isset($post['job']) ? trim($post['job']) : '';
		//性别
		$submit['gender'] = '';
		if (isset($post['gender']) && $post['gender'] == '男') {
			$submit['gender'] = self::GENDER_MALE;
		} elseif (isset($post['gender']) && $post['gender'] == '女') {
			$submit['gender'] = self::GENDER_FEMALE;
		}

		//部门id
		if (!empty($post['department'])) {

			$cds = explode(';', trim($post['department']));
			$cd_ids = array();
			foreach ($cds as $cd) {
				$cd = trim($cd);
				$cd_id = $this->get_sub_cd_id_by_cd_name($cd);
				if (is_numeric($cd_id) && $cd_id > 0) {
					$cd_ids[] = $cd_id;
				} else {
					E('_ERR_NO_DEPARTMENT_MATCH');

					return false;
				}
			}
			$submit['department'] = $cd_ids;
		} else {
			// 没有部门就默认顶级部门
			Department::instance()->get_top_cdid($topid);
			$submit['department'] = array($topid);
		}

		if (empty($submit['department'])) {
			E('_ERR_NO_DEPARTMENT_MATCH');

			return false;
		}

		// 过滤掉未开启的
		$fields_list = $this->_get_field();
		$custom = $fields_list['custom'];

		foreach ($custom as $open_k => $_open) {
			if ($_open['open'] == 0) {
				unset($custom[$open_k]);
			}
		}
		$this->__fields = $fields_list['fixed'];

		foreach ($this->__fields as $k_fi => $__fi) {
			$fix_list[] = $k_fi;
		}
		$this->__fields = array_merge($this->__fields, $custom);

		//设置扩展字段
		foreach ($this->__fields as $k => $field) {
			if (isset($post[$k]) && !in_array($k, $fix_list)) {
				$submit[$k] = $post[$k];
			}
		}
		//是否有上级领导id
		if (!empty($submit['leader'])) {
			$user_name = explode(',', $post['leader']);
			$serv_mem = D('Common/Member', 'Service');
			//查询条件
			$conds_leader['m_username'] = $user_name;
			$leader_list = $serv_mem->list_by_conds($conds_leader);
			//构造数组
			$tmp_leader = array();
			if (!empty($leader_list)) {
				foreach ($leader_list as $_leader) {
					$tmp_leader[] = $_leader['m_uid'];
				}
			}
			$submit['leader'] = $tmp_leader;
		} else {
			$submit['leader'] = array();
		}

		$serv_mem = D('Common/Member', 'Service');
		$member = array();

		//判断是否是新增
		$this->add_update($submit, $act, $m_uid);

		if ($act == 'add') {
			$serv_mem->add_member($submit, $member);
		} else {
			$submit['m_uid'] = $m_uid;
			$serv_mem->edit_member($submit, $member);
		}

		// 更新部门人数
		$this->_update_department_num();

		return true;
	}

	/**
	 * 判断是否是同一个人
	 * @param $submit array 提交的信息
	 * @return bool
	 */
	public function add_update($submit, &$act, &$uid) {

		$uid = 0;
		$act = 'add';
		// 拼接查询条件
		$fields = array();
		if (! empty($submit['weixinid'])) {
			$fields['m_weixin'] = (string)$submit['weixinid'];
		}
		if (! empty($submit['mobilephone'])) {
			$fields['m_mobilephone'] = (string)$submit['mobilephone'];
		}
		if (! empty($submit['mobile'])) {
			$fields['m_mobilephone'] = (string)$submit['mobile'];
		}
		if (! empty($submit['email'])) {
			$fields['m_email'] = (string)$submit['email'];
		}
		if (! empty($submit['userid'])) {
			$fields['m_openid'] = (string)$submit['userid'];
		}

		// 判断参数是否为空
		if (empty($fields)) {
			E('_ERR_FIELDS_EMPTY');
			return false;
		}

		$serv_mem = D('Common/Member', 'Service');
		$list = $serv_mem->list_by_unique_field($fields);
		if (! empty($list)) {
			if (1 < count($list)) {
				E('_ERR_MEMBER_INFO_EXISTS');
				return false;
			} else {
				$member = current($list);
				$uid = $member['m_uid'];
				$act = 'update';
			}
		}

		return true;
	}

	/**
	 * 上传 excel 文件
	 * @return void
	 */
	public function Uploadexcel_post() {

		$current_config = array();
		// 储存根目录
		$cache = &Cache::instance();
		$settings = $cache->get('Common.setting');
		$current_config['save_dir_path'] = get_sitedir();
		if (!is_dir($current_config['save_dir_path'])) {
			rmkdir($current_config['save_dir_path'], 0777, true);
		}
		// 允许上传的附件类型
		$current_config['allow_files'] = array('xls');
		// 储存附件的文件名格式
		$current_config['file_name_format'] = 'auto';
		// 允许上传的文件最大尺寸
		$current_config['max_size'] = $settings['app_name'] . '.attachment.max_size';
		// 源文件名
		$current_config['source_name'] = isset($_POST['fileName']) ? $_POST['fileName'] : 'x.xsl';
		// 储存格式
		$current_config['file_name_format'] = '{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:8}';
		// 上传文件
		$upload = new \Com\Upload('upload', $current_config, 'upload');
		// 上传后的文件信息
		$result = $upload->get_file_info();
		$this->__tmp_data_path = get_sitedir() . 'data_members.php';

		if (!empty($result['error_code'])) {
			E('_ERR_UPLOAD_FAILED');

			return true;
		}

		// 过滤掉未开启的
		$fields_list = $this->_get_field();
		$custom = $fields_list['custom'];

		foreach ($custom as $open_k => $_open) {
			if ($_open['open'] == 0) {
				unset($custom[$open_k]);
			}
		}
		$this->__fields = $fields_list['fixed'];
		//临时数组
		$this->__fields = array_merge($this->__fields, $custom);
		// 上传的文件位置
		$file = $result['file_path'];
		// 解析 Excel 文件
		$excel = new \Com\Excel();
		$excel_parse_data = $excel->parse_xsl($file, 0, $this->__fields, 0, 1);

		if (!$excel_parse_data) {
			E('_ERR_PARSE_FAILED');

			return false;
		}

		@unlink($file);
		// 写入临时储存
		rfwrite($this->__tmp_data_path, "<?php\r\n\$excel_data = " . var_export($excel_parse_data, true) . ";");
		list($field, $list) = $excel_parse_data;
		//处理重复数据
		//字段对应的键
		foreach ($field as $k_excel => $v_excel) {
			if ($v_excel == 'userid' || $v_excel == 'mobile' || $v_excel == 'email' || $v_excel == 'weixinid') {
				$key_value[$k_excel] = $v_excel;
			}
		}
		$key_value = array_flip($key_value);
		//微信号
		$weixinid_list = array();
		$mobile_list = array();
		$email_list = array();
		$userid_list = array();
		//错误数据
		$err_list = array();
		$right_list = array();
		$errmsg_list = array();
		foreach ($list as $_unique) {
			// userid
			if (!empty($_unique[$key_value['userid']])) {
				if (in_array($_unique[$key_value['userid']], $userid_list)) {
					$errmsg_list[] = 'Userid error';
					$err_list[] = $_unique;
					continue;
				}
				$userid_list[] = $_unique[$key_value['userid']];
			}
			//weixinid
			if (!empty($_unique[$key_value['weixinid']])) {
				if (in_array($_unique[$key_value['weixinid']], $weixinid_list)) {
					$errmsg_list[] = 'Weixinid error';
					$err_list[] = $_unique;
					continue;
				}
				$weixinid_list[] = $_unique[$key_value['weixinid']];
			}
			//mobile
			if (!empty($_unique[$key_value['mobile']])) {
				if (in_array($_unique[$key_value['mobile']], $mobile_list)) {
					$errmsg_list[] = 'Mobile error';
					$err_list[] = $_unique;
					continue;
				}
				$mobile_list[] = $_unique[$key_value['mobile']];
			}
			//email
			if (!empty($_unique[$key_value['email']])) {
				if (in_array($_unique[$key_value['email']], $email_list)) {
					$errmsg_list[] = 'Email error';
					$err_list[] = $_unique;
					continue;
				}
				$email_list[] = $_unique[$key_value['email']];
			}
			$right_list[] = $_unique;
		}

		//去除掉规定以外的字段
		$key_list = array_keys($field);
		//规则字段
		$rule_list = array_keys($this->__fields);

		list($err_list, $new_order) = $this->format_list($err_list, $rule_list, $key_list, $field);
		list($new_list, $new_order) = $this->format_list($right_list, $rule_list, $key_list, $field);

		$field = array_unique($new_order);
		//判断列表格式
		if (!empty($new_list)) {
			foreach ($new_list as $_format) {
				if (count($field) != count($_format)) {
					E('_ERR_NOT_TRUE_FORMAT');

					return false;
				}
			}
		} else {
			E('_ERR_EMPTY_DUMP_DATA');

			return false;
		}

		$output = $this->__create_data_list($field, $new_list);
		$err_list = $this->create_data_list($field, $err_list);
		$output['err_list'] = $err_list;
		$output['errmsg_list'] = $errmsg_list;
		$output['err_count'] = count($err_list);
		$output['right_count'] = $output['total'];
		$output['total'] = $output['err_count'] + $output['total'];
		//返回值
		$this->_result = array(
			'output' => $output,
		);
	}

	/**
	 * 格式错误列表方法
	 * @param $field array 字段
	 * @param $list array 待格式列表
	 * @return array
	 */
	public function create_data_list($field, $list) {

		// “忽略”列，键名定义
		$key_ignore = '_ignore';
		// “导入结果”列，键名定义
		$key_result = '_result';

		// 标题栏总宽度
		$_fields = array();
		$_fields[] = array('key' => $key_ignore, 'name' => '忽略', 'width' => 12);
		$_fields = array_merge($_fields, $this->__fields);
		$_fields[] = array('key' => $key_result, 'name' => '导入结果', 'width' => 120);
		unset($_key, $_ini);

		// 取得标题栏列的名称和宽度比例
		$field_name = array();
		foreach ($_fields as $_key => $_ini) {
			$field_name[] = array(
				'key' => $_key,
				'name' => $_ini['name'],
				'width' => '',
			);
		}

		unset($_key, $_ini, $width_total);
		// 重新整理导入的数据列表
		$data_list = array();
		foreach ($list as $_key => $_val) {
			$is_empty = true;
			$temp = array();
			foreach ($_val as $_k => $_v) {
				$temp[$field[$_k]] = $_v !== null ? $_v : '';
				if (!empty($temp[$field[$_k]])) {
					$is_empty = false;
				}
			}
			if ($is_empty === true) {
				unset($list[$_key]);
				continue;
			}
			$data_list[$_key] = $temp;
		}
		unset($_key, $_k, $_val, $_v);
		// 重新整理列表
		foreach ($list as $_key => &$_val) {
			foreach ($_val as $_k => &$_v) {
				if ($_v === null) {
					$_v = '';
				}
			}
			unset($_v, $_k);
		}
		unset($_key, $_val);

		return $data_list;
	}

	/**
	 * 完善字段格式
	 * @param $list array 待处理数组
	 * @param $rule_list array 规则
	 * @param $key_list array 顺序
	 * @param $field array 字段
	 * @return array
	 */
	public function format_list($list, $rule_list, $key_list, $field) {

		//去除掉规定以外的字段
		if (!empty($list)) {
			foreach ($list as $k_list => $_list) {
				foreach ($_list as $k_unlist => $_unlist) {
					if (!in_array($k_unlist, $key_list)) {
						unset($list[$k_list][$k_unlist]);
					}
				}
			}

			//填充少的字段
			foreach ($rule_list as $_rule) {
				if (!in_array($_rule, $field)) {
					$field[] = $_rule;
				}
			}

			//填充少的值
			foreach ($field as $k_field => $v_field) {
				foreach ($list as $k_add => &$v_add) {
					if (!isset($v_add[$k_field])) {
						$v_add[$k_field] = null;
					}
				}
			}
		}

		//pai
		//字段名的排序
		foreach ($rule_list as $_rule) {
			$cur_key = array_search($_rule, $field);
			$new_order[$cur_key] = $_rule;
			unset($cur_key);
		}
		//字段值的排序
		foreach ($list as $__k => &$__list) {
			foreach ($__list as $_order_list) {
				foreach ($new_order as $k_order => $v_order) {
					$new_list[$__k][$k_order] = $list[$__k][$k_order];
				}
			}
		}

		foreach ($new_list as $k_reset => &$val) {
			$val = array_values($val);
		}
		$new_order = array_values($new_order);

		return array($new_list, $new_order);
	}

	/**
	 * 下载模板
	 * @return bool
	 */
	public function download_get() {

		// 标题栏样式定义
		$options = array(
			'title_text_color' => 'FFf5f5f5',
			'title_background_color' => 'FF000099',
		);
		// 下载的文件名
		$filename = '畅移云工作_用户批量导入';
		// 标题文字 和 标题栏宽度
		$title_width = array();

		// 获取属性规则
		$field = $this->_get_field();
		// 默认数据
		$row_data = array();
		$row_data[0][0] = '张三';
		$row_data[0][1] = 'zhanghaosuiyikeyiweikong';
		$row_data[0][2] = '男';
		$row_data[0][3] = '13888888888';
		$row_data[0][4] = 'wxid_demo';
		$row_data[0][5] = 'test@test.com';
		$row_data[0][6] = '市场部/市场一部';
		$row_data[1][0] = '李四';
		$row_data[1][1] = 'danshibunengxiangtong';
		$row_data[1][2] = '女';
		$row_data[1][3] = '13333333333';
		$row_data[1][4] = 'wxid_demo1';
		$row_data[1][5] = 'demo@demo.com';
		$row_data[1][6] = '技术部;市场部/市场一部';

		// 如果字段是开启
		$title_string = array();
		foreach ($field as $_fixed_or_custom => $_rule) {
			foreach ($_rule as $_name => $_field) {
				if ($_field[self::CHECK_OPEN] == self::ALLOW) {
					$title_string[] = $_field['name'];

					// 为了加上例子
					if ($_fixed_or_custom == 'custom') {
						if (in_array($_name, array('leader', 'birthday'))) {
							// 单独判断
							switch ($_name) {
								case 'leader':
									$row_data[0][] = '小明';
									$row_data[1][] = '小花';
									break;
								case 'birthday':
									$row_data[0][] = '1990/01/01';
									$row_data[1][] = '1990/01/01';
									break;
							}
						} else {
							$row_data[0][] = '';
							$row_data[1][] = '';
						}
					}
				}
			}
		}

		// 载入 Excel 类
		$excel = new \Com\Excel();
		$excel->make_excel_download($filename, $title_string, $title_width, $row_data, $options);

		return true;
	}

	/**
	 * 将数据整理为批量导入需要的格式
	 * @param array $field 字段定义
	 * @param array $list 数据列表
	 * @return array
	 */
	private function __create_data_list($field, $list) {

		// “忽略”列，键名定义
		$key_ignore = '_ignore';
		// “导入结果”列，键名定义
		$key_result = '_result';

		// 标题栏总宽度
		$_fields = array();
		$_fields[] = array('key' => $key_ignore, 'name' => '忽略', 'width' => 12);
		$_fields = array_merge($_fields, $this->__fields);
		$_fields[] = array('key' => $key_result, 'name' => '导入结果', 'width' => 120);
		unset($_key, $_ini);

		// 取得标题栏列的名称和宽度比例
		$field_name = array();
		foreach ($_fields as $_key => $_ini) {
			$field_name[] = array(
				'key' => $_key,
				'name' => $_ini['name'],
				'width' => '',
			);
		}

		unset($_key, $_ini, $width_total);
		// 重新整理导入的数据列表
		$data_list = array();
		foreach ($list as $_key => $_val) {
			$is_empty = true;
			$temp = array();
			foreach ($_val as $_k => $_v) {
				$temp[$field[$_k]] = $_v !== null ? $_v : '';
				if (!empty($temp[$field[$_k]])) {
					$is_empty = false;
				}
			}
			if ($is_empty === true) {
				unset($list[$_key]);
				continue;
			}
			$data_list[$_key] = $temp;
		}
		unset($_key, $_k, $_val, $_v);
		// 重新整理列表
		foreach ($list as $_key => &$_val) {
			foreach ($_val as $_k => &$_v) {
				if ($_v === null) {
					$_v = '';
				}
			}
			unset($_v, $_k);
		}
		unset($_key, $_val);

		$this->__import_dp($data_list);

		$output = array(
			'total' => count($list),
			'key_ignore' => $key_ignore,
			'key_result' => $key_result,
			'field' => $field,
			'field_name' => $field_name,
			'list' => $list,
			'data_list' => $data_list,
		);

		return $output;
	}


	/**
	 * 批量提交数据，用于处理编辑后的错误数据，类似execel导入的后半部的处理过程
	 * @return void
	 */
	public function Resubmit_post() {

		$post = I('post.');
		//过滤掉未开启的
		$fields_list = $this->_get_field();
		$custom = $fields_list['custom'];

		foreach ($custom as $open_k => $_open) {
			if ($_open['open'] == 0) {
				unset($custom[$open_k]);
			}
		}
		$this->__fields = $fields_list['fixed'];
		//临时数组
		$this->__fields = array_merge($this->__fields, $custom);
		// 字段定义
		$field = array_keys($this->__fields);
		// 读取上传的数据
		$data = array();
		foreach ($field as $_k) {
			$data[$_k] = $post[$_k];
		}
		unset($_k, $_v);
		if (empty($data)) {
			E('_ERR_EMPTY_DUMP_DATA');

			return false;
		}

		// 请求忽略的数据
		$ignore = (array)$post['ignore'];

		// 整理格式，以名称为标准
		$name_key = array_search('name', $field);
		if (!isset($data[$field[$name_key]])) {
			E('_ERR_NAME_DATA_NORMAL');

			return false;
		}

		// 整理数据
		$list = array();
		foreach ($data[$field[$name_key]] as $_id => $_val) {
			if (isset($ignore[$_id])) {
				continue;
			}
			foreach ($field as $_field_id => $_field) {
				if (isset($data[$_field][$_id])) {
					$list[$_id][$_field_id] = $data[$_field][$_id];
				}
			}
		}

		if (empty($list)) {
			E('_ERR_EMPTY_DUMP_DATA');

			return false;
		}
		// 输出批量导入需要的格式
		$output = $this->__create_data_list($field, $list);

		$this->_result = array(
			'output' => $output,
		);

		return true;
	}

	/**
	 * 导入部门
	 * @param $data_list
	 */
	private function __import_dp($data_list) {

		if (empty($data_list) || !is_array($data_list)) {
			return true;
		}

		foreach ($data_list as $data) {
			if (empty($data['department'])) {
				continue;
			}
			$cds = explode(';', trim($data['department']));
			foreach ($cds as $cd) {
				$this->get_sub_cd_id_by_cd_name(trim($cd));
			}
		}

		return true;
	}


	/**
	 * 获取最下级的部门id
	 * @param $cd_name_str
	 * @return int
	 */
	public function get_sub_cd_id_by_cd_name($cd_name_str) {

		// 分隔部门
		$cd_names = explode('/', $cd_name_str);
		// 获取公司id
		Department::instance()->get_top_cdid($upid);
		$children_cd_id = 0;
		// 遍历公司
		foreach ($cd_names as $cd_name) {
			$children_cd_id = $this->get_cd_id_by_cd_name($cd_name, $upid);
			$upid = $children_cd_id;
			if (empty($upid) || !is_numeric($upid)) {
				return $upid;
			}
		}

		return $children_cd_id;
	}

	/**
	 * 获取部门id根据名称
	 * @param $cd_name
	 * @return int
	 */
	function get_cd_id_by_cd_name($cd_name, $upid) {

		$serv_dep = D('Common/CommonDepartment', 'Service');
		$cd_name = trim($cd_name);
		$cd_id = $serv_dep->get_id_by_cdname_upid($cd_name, $upid);
		if (!empty($cd_id)) {
			return $cd_id;
		}

		//不存在则添加部门
		$data['cd_upid'] = $upid;
		$data['cd_name'] = $cd_name;
		$data['cd_displayorder'] = 1;
		$data['cd_id'] = 0;
		$update = array();

		if ($serv_dep->update_dep($data, $update)) {
			clear_cache();

			return $update['cd_id'];
		} else {
			E('_ERR_FAILED_UPDATE');

			return false;
		}
	}

	/**
	 * 利用姓名来构造一个用户微信标识ID字符串
	 * @param string $realname 姓名
	 * @param string $userid <strong>(引用结果)</strong> 生成的唯一标识符userid /openid
	 * @return string
	 */
	protected function _make_userid($realname = '', &$userid = '') {

		$userid = md5(mt_rand(1, 999999) . $realname . time() . mt_rand(1, 999999));

		// 判断是否重复
		$this->_had_userid($realname, $userid);

		return true;
	}
}
