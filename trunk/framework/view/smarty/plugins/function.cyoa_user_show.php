<?php
/**
 * function.cyoa_user_show.php
 * H5前端人员显示组件
 * Create By Deepseath
 * $Author$
 * $Id$
 */

function smarty_function_cyoa_user_show($params, $template) {

	// 载入相关函数库
	if (!function_exists('smarty_function_escape_special_chars')) {
		require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');
	}
	if (!function_exists('_cyoa_merge')) {
		require_once(SMARTY_PLUGINS_DIR . 'cyoa_functions.php');
	}

	// 定义默认值
	$defaults = array(
		'id' => 'user-show',// 容器ID
		'users' => array(),// 人员信息列表 array(array('m_uid' => int, 'm_username' => ''), ...)
		'dps' => array(),// 部门信息列表array(array('cd_id' => int, 'cd_name' => string), ...)
		'userids' => null,// 人员m_uid列表或者m_uid字符串
		'dpids' => null,// 部门id列表，或者部门id字符串
		'styleid' => 1,
		'title' => '',
		'description' => '',
		'max' => 0,
		'input' => '',
		'dpinput' => '',
		'userinput' => '',
		'morelink' => '',
		'div' => array(
			'class' => null,
		)
	);

	// 格式化默认
	$params = _cyoa_format_params($params);

	// 设置默认值
	$params = _cyoa_merge($defaults, $params);

	// 不输出任何信息
	if ($params['dpids'] === null && $params['userids'] === null && empty($params['users']) && empty($params['dps'])) {
		return '';
	}

	// 是否显示全公司
	$is_all = false;
	if (!empty($params['dpids'])) {
		if (!is_array($params['dpids'])) {
			$params['dpids'] = explode(',', $params['dpids']);
		}
		if (array_search(-1, $params['dpids']) !== false) {
			$is_all = true;
		}
	}

	// 指定了要显示的人员m_uid
	if (!$is_all && !empty($params['userids'])) {
		$_ids = is_array($params['userids']) ? $params['userids'] : explode(',', $params['userids']);
		if (!empty($_ids)) {
			$params['users'] = voa_h_user::get_multi($_ids);
		}
		unset($_ids);
	}
	// 指定了要显示的部门cd_id
	if (!empty($params['dpids'])) {
		if ($is_all) {
			$top = voa_h_department::get_top();
			if ($top) {
				$params['dps'] = array($top['cd_id'] => $top);
			}
		} else {
			$params['dps'] = voa_h_department::get_multi($params['dpids']);
		}
	}

	if (empty($params['users']) && empty($params['dps'])) {
		$top = voa_h_department::get_top();
		if ($top) {
			$params['dps'] = array($top['cd_id'] => $top);
		}
	}

	// 使用的方法
	$method = '_style_'.$params['styleid'];
	if (!function_exists($method)) {
		return 'user show styleid: '.$params['styleid'].' not exists';
	}

	return $method($params);
}

/**
 * 用户列表
 * @param array $params
 * @return string
 */
function __user_list($params) {

	$output = '';
	$uids = array();
	foreach ($params['users'] as $_uid => $_user) {

		if (isset($uids[$_uid])) {
			continue;
		}

		$uids[$_uid] = $_uid;

		$_face = '<span></span>';
		if ($_user['m_face']) {
			$_face = '<span style="background-image:url('.$_user['m_face'].')"></span>';
		}
		$_username = rhtmlspecialchars($_user['m_username']);

		$output .= <<<EOF
<div class="ui-badge-wrap" data-uid="{$_uid}">
	<div class="ui-avatar-s">
		{$_face}
	</div>
	<div class="name">{$_username}</div>
</div>
EOF;
	}

	if (empty($params['userinput'])) {
		$params['userinput'] = !empty($params['input']) ? $params['input'] : '';
	}
	return ($params['userinput'] ? '<input type="hidden" name="'.$params['userinput'].'" value="'.implode(',', $uids).'" />' : '').$output;
}

/**
 * 部门列表
 * @param array $params
 * @return string
 */
function __dp_list($params) {

	$cd_ids = array();
	$output = '';
	// 遍历部门列表
	foreach ($params['dps'] as $_cd_id => $_dp) {
		// 重复的部门则忽略
		if (isset($cd_ids[$_cd_id]) || !$_cd_id) {
			continue;
		}
		$cd_ids[$_cd_id] = $_cd_id;

		$_name = rhtmlspecialchars($_dp['cd_name']);

		$output .= <<<EOF
<div class="ui-badge-wrap ui-border ui-contact-part" data-dpid="{$_cd_id}">
	<span>{$_name}</span>
</div>
EOF;
	}

	return ($params['dpinput'] ? '<input type="hidden" name="'.$params['dpinput'].'" value="'.implode(',', $cd_ids).'" />' : '').$output;
}

/**
 * 风格1：只显示人员头像列表
 * @param unknown $params
 * @return string
 */
function _style_1($params) {

	if ($params['div']['class'] === null) {
		$params['div']['class'] = 'ui-form-item ui-form-contacts ui-border-t clearfix';
	}

	$output = '<div id="'.$params['id'].'">';
	$dp_list = __dp_list($params);
	$user_list = __user_list($params);

	// 标签
	$label = '';
	if (!empty($params['title'])) {
		$label = '<label>'.$params['title'].'</label>';
	}
	if ($params['title'] || $params['description']) {
		$output .= '<div class="ui-form-item ui-form-contacts ui-border-t clearfix">'.$label.'<p>'.($params['description'] ? $params['description'] : '&nbsp;').'</p></div>';
	}
	if ($dp_list) {
		$output .= '<div class="ui-form-item ui-form-contacts ui-border-t clearfix">'.$dp_list.'</div>';
	}
	if ($user_list) {
		$output .= '<div class="ui-form-item ui-form-contacts ui-border-t clearfix">'.$user_list.'</div>';
	}
	$output .= '</div>';

	return $output;
}

/**
 * 风格2：显示标签以及人员列表
 * @param unknown $params
 * @return string
 */
function _style_2($params) {

	if ($params['div']['class'] === null) {
		$params['div']['class'] = 'ui-form-item ui-form-contacts ui-border-t';
	}

	$label = '';
	if ($params['title']) {
		$label = '<label>'.$params['title'].'</label>';
	}

	$list_dp = __dp_list($params);
	$list_user = __user_list($params);

	return <<<EOF
<div id="{$params['id']}" class="{$params['div']['class']}">
	{$label}
	<div class="select-contact">
		<div class="select-box clearfix">
			{$list_dp}
			{$list_user}
		</div>
	</div>
</div>
EOF;
}