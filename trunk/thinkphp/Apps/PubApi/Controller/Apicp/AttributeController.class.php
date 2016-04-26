<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/23
 * Time: 下午9:04
 * 人员属性设置
 * 可在__post_rule 方法里增加字段规则
 * 'name' => array(      // 这里name 是提交上来的变量
 *      'number' => 1,   // 序号
 *      'name' => '姓名', // 显示名称
 *      'open' => 1,     // 是否开启
 *      'required' => 1, // 是否必填
 *      'view' => 1,     // 是否在通讯录显示
 *      'level' => 0,    // 可更改等级
 *  ),
 * 规则可增加, 判断的条件也可增加 就像 open required view, number name level是固定的
 * 自定义字段也可设置上限
 */

namespace PubApi\Controller\Apicp;

class AttributeController extends AbstractController {

	/** 默认规则 */
	protected $_field = array();
	/** 规则最高级别:不能更改 */
	const ZERO_LEVEL = 0;
	/** 规则级别1:只能修改显示或不显示 */
	const VIEW_LEVEL = 1;
	/** 规则级别2:序列号 是否开启 是否必填 是否通讯录显示 */
	const ALL_LEVEL = 2;
	/** 规则级别3:序列号 是否开启 是否必填 是否通讯录显示 名字 */
	const EXT_LEVEL = 3;

	/** 规则开启 关闭范围 */
	protected $_array_range = array();
	/** 规则 (开启) */
	const ALLOW = 1;
	/** 规则 (关闭) */
	const UNALLOW = 0;
	/** 规则级别2 需要判断范围的 checkbox 数量 */
	const ALL_LEVEL_CHECK_NUM = 3;
	/** checkbox 规则 */
	protected $_array_checkbox = array();
	const CHECK_OPEN = 'open';
	const CHECK_VIEW = 'view';
	const CHECK_REQUIRED = 'required';
	/** 自定义 input type */
	protected $_ext_input_type = array();
	const INPUT_TEXT = 1;
	const INPUT_NUM = 2;
	const INPUT_DATA = 3;
	const INPUT_RADIO = 4;
	const INPUT_CHECKBOX = 5;
	/** 属性字段名称 */
	protected $_name_recur = array();

	/**
	 * 获取人员属性规则
	 * @return bool
	 */
	public function Index_get() {

		// 获取设置缓存
		$cache = &\Common\Common\Cache::instance();
		$setting = $cache->get('Common.member_setting');

		// 获取属性规则
		$field = $setting['fields'];
		$label = $setting['sensitive'];

		// 去掉view为零
		if (!empty($label)) {
			foreach ($label as $_key => &$_rule) {
				$temp = array();
				foreach ($_rule['view'] as $_name => $_value) {
					if ($_value['view'] == self::ALLOW) {
						$temp[$_name] = $_rule['view'][$_name];
					}
				}
				$_rule['view'] = $temp;
			}
		}

		// 获取标签列表
		$serv_label = D('Common/CommonLabel', 'Service');
		$label_list = $serv_label->list_all();

		$this->_result = array(
			'field' => $field, // 属性规则
			'label' => $label, // 标签设置
			'label_list' => $label_list,
		);

		return true;
	}

	/**
	 * 编辑人员属性规则
	 * @return bool
	 */
	public function Edit_post() {

		// 获取默认规则
		$this->__post_rule();

		// 获取提交的 人员属性设置
		$field = I('post.field');
		if (empty($field) || empty($field['fixed']) || empty($field['custom'])) {
			E('_ERR_EMPTY_FIELD');
			return false;
		}

		// 获取提交的 标签设置
		$label = I('post.label');

		// 最终入库的规则
		$update_data = array();
		// 标签规则
		$label_rule = array();

		/** 处理人员属性 */
		// 编辑 默认固定规则 匹配提交的 属性规则是否合法
		foreach ($this->_field['fixed'] as $_name => $_deafult) {
			// 判断固定默认规则是否存在提交内
			if (!isset($field['fixed'][$_name])) {
				E(L('_ERR_FIELD_MISS', array('name' => $_deafult['name'])));
				return false;
			}

			$this->_custom_name_is_recur($field['fixed'][$_name]['name']);
			// 根据规则级别判断提交
			switch ($_deafult['level']) {
				case self::ZERO_LEVEL: // 最高级别不能更改
					$update_data['fixed'][$_name] = $_deafult;
					// 写入标签规则
					$label_rule[$_name] = array(
						'name' => $_deafult['name'],
						'level' => self::ZERO_LEVEL,
					);
					break;
				case self::VIEW_LEVEL: // 可修改是否在通讯录显示
					$update_data['fixed'][$_name] = $_deafult;
					// 判断view 的值是否在范围内
					if (!in_array($field['fixed'][$_name]['view'], $this->_array_range)) {
						E(L('_ERR_FIELD_OUT_OF_RANGE', array('name' => $field['fixed'][$_name]['name'])));
					}
					$update_data['fixed'][$_name]['view'] = $field['fixed'][$_name]['view'];

					// 写入标签规则
					$label_rule[$_name] = array(
						'name' => $_deafult['name'],
						'open' => $update_data['fixed'][$_name]['open'],
					);
					break;
			}
		}

		// 匹配级别为ALL_ALLOW的规则
		foreach ($this->_field['custom'] as $_name => $_deafult) {

			// 判断规则是否存在
			if (!isset($field['custom'][$_name])) {
				E(L('_ERR_FIELD_MISS', array('name' => $_deafult['name'])));
				return false;
			}

			// 判断属性字段名称是否重复
			$this->_custom_name_is_recur($field['custom'][$_name]['name']);
			$update_data['custom'][$_name] = $_deafult;
			$update_data['custom'][$_name]['number'] = empty($field['custom'][$_name]['number']) ? 1 : intval($field['custom'][$_name]['number']); // 序列号

			// 有 ALL_LEVEL_CHECK_NUM 个需要判断选择范围
			for ($i = 0; $i < self::ALL_LEVEL_CHECK_NUM; $i ++) {
				// checkbox 是否为空 或者在范围外
				if (isset($field['custom'][$_name][$this->_array_checkbox[$i]]) && in_array($field['custom'][$_name][$this->_array_checkbox[$i]], $this->_array_range)) {
					$update_data['custom'][$_name][$this->_array_checkbox[$i]] = $field['custom'][$_name][$this->_array_checkbox[$i]];
				} else {
					// 为空默认关闭
					$update_data['custom'][$_name][$this->_array_checkbox[$i]] = self::UNALLOW;
				}
			}

			// 去掉 提交里的默认规则
			unset($field['custom'][$_name]);
			// 写入标签规则
			$label_rule[$_name] = array(
				'name' => $_deafult['name'],
				'open' => $update_data['custom'][$_name]['open'],
			);
		}

		// 处理剩下的 自定义字段规则
		if (!empty($field['custom'])) {
			foreach ($field['custom'] as $_key => $value) {
				$i = substr($_key, 3);
				// 是否 提交里有
				if (isset($field['custom']['ext' . $i])) {
					// 判断属性字段名称是否重复
					$this->_custom_name_is_recur($field['custom']['ext' . $i]['name']);

					// 判断 并且 存入数组
					$update_data['custom']['ext' . $i] = array(
						'number' => empty($field['custom']['ext' . $i]['number']) ? 1 : intval($field['custom']['ext' . $i]['number']),
						'name' => !isset($field['custom']['ext' . $i]['name']) ? '未设置' : $field['custom']['ext' . $i]['name'],
						'type' => in_array($field['custom']['ext' . $i]['type'], $this->_ext_input_type) ? self::INPUT_TEXT : intval($field['custom']['ext' . $i]['type']),
						'level' => self::EXT_LEVEL,
					);

					// 如果是 单选或者多选框 , 获取select 值
					if (in_array($update_data['custom']['ext' . $i]['type'], array(self::INPUT_RADIO, self::INPUT_CHECKBOX))) {
						$update_data['custom']['ext' . $i]['select'] = empty($field['custom']['ext' . $i]['select']) ? array() : $field['custom']['ext' . $i]['select'];
					}

					// 判断 checkbox 提交数据
					for ($z = 0; $z < self::ALL_LEVEL_CHECK_NUM; $z ++) {
						// checkbox 是否为空 或者在范围外
						if (isset($field['custom']['ext' . $i][$this->_array_checkbox[$z]]) && in_array($field['custom']['ext' . $i][$this->_array_checkbox[$z]], $this->_array_range)) {

							$update_data['custom']['ext' . $i][$this->_array_checkbox[$z]] = $field['custom']['ext' . $i][$this->_array_checkbox[$z]];
						} else {
							// 为空默认关闭
							$update_data['custom']['ext' . $i][$this->_array_checkbox[$z]] = self::UNALLOW;
						}
					}
				}

				// 写入标签规则
				$label_rule['ext' . $i] = array(
					'name' => $update_data['custom']['ext' . $i]['name'],
					'open' => $update_data['custom']['ext' . $i]['open'],
				);
			}

			$this->_sort_custom($update_data['custom']);
		}

		/** 处理标签 */

		// 出现的标签ID
		$laid = array();

		// 判断提交的规则 是否和人员属性设置匹配 并且 合法
		if (!empty($label_rule) && !empty($label)) {
			// 遍历规则 匹配提交的数据
			foreach ($label as &$_post_label) {
				// 标签ID不为空
				unset($_post_label['$$hashKey']);
				if (empty($_post_label['laid'])) {
					E('_ERR_EMPTY_LAID');

					return false;
				} else {
					// 把提交的标签id写入总的标签池数组
					foreach ($_post_label['laid'] as $_laid) {
						// 判断是否重复
						if (in_array($_laid, $laid)) {
							E('_ERR_LABEL_CAN_NOT_REPEATED');

							return false;
						}
						$laid[] = $_laid;
					}
				}

				$_view = array();
				$this->_deal_label($_post_label['view'], $label_rule, $_view);

				// 规则赋值
				$_post_label['view'] = $_view;
			}
		}

		$serv_set = D('Common/MemberSetting', 'Service');

		// 更新规则
		$update_data = serialize($update_data);
		$serv_set->update_by_conds(array('m_key' => 'fields'), array('m_value' => $update_data));

		// 更新敏感成员信息
		$label = serialize($label);
		$serv_set->update_by_conds(array('m_key' => 'sensitive'), array('m_value' => $label));

		// 清理缓存
		clear_cache();

		$this->_result = array(
			'操作成功',
		);

		return true;
	}

	/**
	 * 匹配提交的规则是否 和 人员属性设置的规则
	 * @param array $view 提交的规则
	 * @param array $_label_rule 人员属性设置的规则
	 * @param array $result 匹配后的规则
	 * @return bool
	 */
	protected function _deal_label($view, $_label_rule, &$result) {

		// 遍历人员设属性置的规则
		foreach ($_label_rule as $_name => $_u_rule) {
			if ($_name == 'name') {
				continue;
			}
			$result[$_name] = array(
				'name' => $_u_rule['name'],
				'view' => empty($view[$_name]['view']) ? self::UNALLOW : $view[$_name]['view'],
			);
		}

		// 姓名始终存在
		$result['name'] = array(
			'name' => '姓名',
			'view' => self::ALLOW,
		);

		return true;
	}

	/**
	 * 默认存在的人员属性
	 * @return bool
	 */
	private function __post_rule() {

		// 规则
		$this->_field = array(
			'fixed' => array(
				'name' => array(
					'number' => 1,
					'name' => '姓名',
					'open' => 1,
					'required' => 1,
					'view' => 1,
					'level' => 0,
				),
				'userid' => array(
					'number' => 2,
					'name' => '账号',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'gender' => array(
					'number' => 3,
					'name' => '性别',
					'open' => 1,
					'required' => 1,
					'view' => 1,
					'level' => 1,
				),
				'mobile' => array(
					'number' => 4,
					'name' => '手机号',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'weixinid' => array(
					'number' => 5,
					'name' => '微信',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'email' => array(
					'number' => 6,
					'name' => '邮箱',
					'open' => 1,
					'required' => 0,
					'view' => 1,
					'level' => 1,
				),
				'department' => array(
					'number' => 7,
					'name' => '部门',
					'open' => 1,
					'required' => 1,
					'view' => 1,
					'level' => 1,
				),
			),
			'custom' => array(
				'leader' => array(
					'number' => 1,
					'name' => '直属领导',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'birthday' => array(
					'number' => 2,
					'name' => '生日',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'address' => array(
					'number' => 3,
					'name' => '地址',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
				'position' => array(
					'number' => 4,
					'name' => '职位',
					'open' => 1,
					'required' => 0,
					'view' => 0,
					'level' => 2,
				),
			),
		);

		// 规则 范围
		$this->_array_range = array(
			self::ALLOW,
			self::UNALLOW,
		);

		// checkbox 种类
		$this->_array_checkbox = array(
			self::CHECK_OPEN,
			self::CHECK_REQUIRED,
			self::CHECK_VIEW,
		);

		// ext input type 种类 / 范围
		$this->_ext_input_type = array(
			self::INPUT_TEXT,
			self::INPUT_NUM,
			self::INPUT_DATA,
			self::INPUT_RADIO,
			self::INPUT_CHECKBOX,
		);

		return true;
	}

	/**
	 * 判断属性字段名称是否重复
	 * @param $name
	 * @return bool
	 */
	protected function _custom_name_is_recur($name) {

		// 判断名称是否重复
		if (in_array($name, $this->_name_recur)) {
			E(L('_ERR_CUSTOM_NAME_IS_RECUR', array('name' => $name)));
			return false;
		}
		$this->_name_recur[] = $name;

		return true;
	}

	/**
	 * 排序
	 * @param $custom
	 * @return bool
	 */
	protected function _sort_custom(&$custom) {

		$temp = array();
		// 按照number 排序
		foreach ($custom as $_key => $_val) {
			$temp[$_val['number']][][$_key] = $_val;
		}
		ksort($temp);

		$custom_temp = array();
		foreach ($temp as $_rule) {
			foreach ($_rule as $_val) {
				$custom_temp[key($_val)] = $_val[key($_val)];
			}
		}

		$custom = $custom_temp;
		return true;
	}
}
