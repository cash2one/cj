<?php
/**
 * function.cyoa_user_selector.php
 * H5前端选人组件
 * Create By Deepseath
 * $Author$
 * $Id$
 */

function smarty_function_cyoa_user_selector($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(
		'id' => 'src',// 容器ID
		'users' => array(),// array(array('m_uid' => int, 'm_face' => '', 'm_username' => ''), ...)
		'dps' => array(),//
		'ac' => '',
		'title' => '接收人',// 标签名
		'description' => '&nbsp;',
		'selectall' => 0,
		'user' => array(
			'ids' => null,// 默认要显示的用户ID列表或字符串
			'name' => '选择用户',
			'max' => -1,
			'cb' => null,
			'ajax' => array(),
			'input' => 'uids',
			'class' => 'ui-form-item ui-form-contacts ui-border-t clearfix _addrbook_list'
		),
		'dp' => array(
			'ids' => null,// 默认要显示的部门id列表或字符串
			'name' => '',
			'max' => -1,
			'cb' => null,
			'ajax' => array(),
			'input' => 'cd_ids',
			'class' => 'ui-form-item ui-form-contacts ui-border-t clearfix _dpname_list'
		),
		'cb' => null,
		'div' => array(
			'class' => 'ui-form-item ui-border-t ui-form-contacts'
		),
		'styleid' => 0
	);

	// 格式化默认
	$params = _cyoa_format_params($params, array('user', 'dp', 'div'));

	// 设置默认值
	$params = _cyoa_merge($defaults, $params);

	if (!empty($params['user']['ajax'])) {
		is_array($params['user']['ajax']) && $params['user']['ajax'] = rjson_encode($params['user']['ajax']);
	}
	if (!empty($params['dp']['ajax'])) {
		is_array($params['dp']['ajax']) && $params['dp']['ajax'] = rjson_encode($params['dp']['ajax']);
	}

	$label_html = '';
	if ($params['title']) {
		$label_html = '<label>'.$params['title'].'</label>';
	}

	$description_html = '';
	if ($params['description']) {
		$description_html = '<p>'.$params['description'].'</p>';
	}

	// 用户
	$user_show = array();
	// 部门
	$department_show = array();
	$user_show_html = '';
	$department_show_html = '';
	// 用户构造js的对象
	$js_params = array();
	$user_input = '';
	$department_input = '';
	// 是否显示全公司
	$is_all = false;
	if (!empty($params['dp']['ids'])) {
		if (!is_array($params['dp']['ids'])) {
			$params['dp']['ids'] = explode(',', $params['dp']['ids']);
		}
		if (array_search(-1, $params['dp']['ids']) !== false) {
			$is_all = true;
		}
	}

	// 人员显示
	if (!empty($params['user'])) {
		$uids = array();
		// 未指定人员信息列表，则尝试是否指定了uid列表
		if (!$is_all) {
			if (empty($params['users']) && !empty($params['user']['ids'])) {
				$_ids = is_array($params['user']['ids']) ? $params['user']['ids'] : explode(',', $params['user']['ids']);
				if ($_ids) {
					$params['users'] = voa_h_user::get_multi($_ids);
				}
				unset($_ids);
			}
			if (!empty($params['users'])) {
				foreach ($params['users'] as $_user) {
					$uids[] = $_user['m_uid'];
					$_face = '<span></span>';
					if ($_user['m_face']) {
						$_face = '<span style="background-image:url('.$_user['m_face'].')"></span>';
					}

					$user_show[] = <<<EOF
<div class="ui-badge-wrap" data-uid="{$_user['m_uid']}">
	<div class="ui-badge-cornernum"></div>
	<div class="ui-avatar-s">
		{$_face}
	</div>
	<div class="name">{$_user['m_username']}</div>
</div>
EOF;
				}
			}
		}

		$uids = implode(',', $uids);
		$user_show = implode('', $user_show);
		$user_show_html = <<<EOF
<div class="{$params['user']['class']}">{$user_show}</div>
EOF;
		if ($is_all) {
			$uids = '';
			$params['user']['ids'] = array();
		}
		$js_params['user'] = __clear_var($params['user']);
		if (!empty($params['user']['input'])) {
			$user_input = <<<EOF
<input type="hidden" id="{$params['user']['input']}" name="{$params['user']['input']}" value="{$uids}" />
EOF;
		}
	}

	// 部门
	if (!empty($params['dp']) && !empty($params['dp']['name'])) {
		$cd_ids = array();
		if (empty($params['dps']) && !empty($params['dp']['ids'])) {
			// 检查是否存在“全公司”
			if ($is_all) {
				// 选择了公司，则忽略其他全部部门
				$top = voa_h_department::get_top();
				if (!empty($top)) {
					$params['dps'] = array($top['cd_id'] => $top);
				}
				unset($top);
			} else {
				$params['dps'] = voa_h_department::get_multi($params['dp']['ids']);
			}
		}
		// 遍历部门信息列表，显示部门名称
		foreach ($params['dps'] as $_dp) {
			if (!$_dp['cd_id']) {
				continue;
			}
			$cd_ids[] = $_dp['cd_id'];
			$department_show[] = <<<EOF
<div class="ui-badge-wrap ui-border ui-contact-part" data-dpid="{$_dp['cd_id']}">
	<div class="ui-badge-cornernum"></div>
	<span>{$_dp['cd_name']}</span>
</div>
EOF;
		}

		$cd_ids = $is_all ? -1 : implode(',', $cd_ids);
		$department_show = implode('', $department_show);
		$params['dp']['datakey'] = 'lists';
		if ($is_all) {
			$params['dp']['ids'] = -1;
		}
		$js_params['dp'] = __clear_var($params['dp']);
		if (!empty($params['dp']['input'])) {
			$department_input = <<<EOF
<input type="hidden" id="{$params['dp']['input']}" name="{$params['dp']['input']}" value="{$cd_ids}" />
EOF;
		}

		$department_show_html = <<<EOF
<div class="{$params['dp']['class']}">{$department_show}</div>
EOF;
	}

	$js_object = __to_js_object($js_params);

	$js_selectall = $params['selectall'] ? 'true' : 'false';
	$js = <<<EOF
var ab = new addrbook();
ab.show({
	"dist": $('#addrbook'),
	"ac": '{$params['ac']}',
	"src": $('#{$params['id']}'),
	"tabs": {$js_object},
	"selectall": {$js_selectall}
});
EOF;

	$template->append('_cyoa_userselector_', $js);

	echo <<<EOF
<div id="{$params['id']}">
	{$department_input}{$user_input}
	<div class="{$params['div']['class']}">
		{$label_html}
		{$description_html}
		<a href="javascript:;" class="ui-icon-add ui-icon"></a>
	</div>
	{$department_show_html}
	{$user_show_html}
</div>
EOF;

}

/**
 * 转换数据为选人组件的JS对象
 * @param array $params
 * @return string
 */
function __to_js_object($params) {

	$js = array();

	if ($params['user'] && ($_js = __out_js($params, 'user'))) {
		$js[] = <<<EOF
"user": {
	{$_js}
}
EOF;
		unset($_js);
	}
	if ($params['dp'] && ($_js = __out_js($params, 'dp'))) {
		$js[] = <<<EOF
"dp": {
	{$_js}
}
EOF;
		unset($_js);
	}

	return '{'.implode(',', $js).'}';
}

/**
 * 清理变量参数
 * @param array $var
 * @return array
 */
function __clear_var($var) {
	$keys = array('name', 'max', 'dist', 'ajax', 'datakey', 'input');
	foreach ($var as $_k => $_v) {
		if (!in_array($_k, $keys)) {
			unset($var[$_k]);
		}
	}
	unset($var['class']);
	$format = array();
	if (empty($var['name'])) {
		return array();
	}
	foreach ($var as $_k => $_v) {
		if (!empty($_v)) {
			$format[$_k] = $_v;
		}
	}
	unset($_k, $_v);

	return $format;
}

/**
 * 输出为js格式代码
 * @param array $params
 * @param string $type
 * @return NULL|string
 */
function __out_js($params, $type) {

	if (empty($params)) {
		return null;
	}

	$params[$type]['input'] = '$(\'#'.$params[$type]['input'].'\')';
	$js = array();
	foreach ($params[$type] as $_k => $_v) {
		if (empty($_v) || !is_scalar($_v)) {
			continue;
		}

		$_js = '';
		$_js .= '"'.$_k.'":';
		if (is_numeric($_v)) {
			$_js .= $_v;
		} elseif (strpos($_v, '$') === 0) {
			$_js .= $_v;
		} else {
			$_js .= '"'.$_v.'"';
		}
		$user[] = $_js;
	}

	return implode(',', $user);
}
