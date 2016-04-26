<?php
/**
 * Smarty自定义函数,选取人与部门插件
 * 使用方法
 * 模板中使用
 * 可使用参数
 * type		可用radio,checkbox,默认为checkbox,可多选
 * input_name_contacts	人员隐藏input的name, 默认为contacts, 实际还会在后面添加上中括号,便于后台获取数据
 * input_name_deps		部分隐藏input的name, 默认为deps, 后面还会加上中括号
 * member	在修改表单中,默认选中人员数据,格式为[{id:1,name:'朱逊'}]
 * deps		默认选中部门,格式同上
 * 
 * 最简用法,在模板的任意位置添加
 * {contact}
 * 复杂用法
 * {contact member=$member deps=$deps input_name_contacts=aaaa input_name_deps=bbbb}
 * 以上用法需要控制器给模板选中人与部门数据,代码类似于:
 *  $member = array(array('id' => 1,'name' => '朱逊'), array('id' => 3,'name' => '深海'));	//假设这是数据库中获取资料
	$deps = array(array('id' => 8,'name' => '研发一部'), array('id' => 27,'name' => '产品部'));//假设这是数据库中获取资料
	$this->view->set('member', json_encode($member));
	$this->view->set('deps', json_encode($deps));
 */
function smarty_function_contact($params, $template)
{
	$type = $params['type'] ? $params['type'] : 'checkbox';
	$input_name_contacts = $params['input_name_contacts'] ? $params['input_name_contacts'] : 'contacts';
	$input_name_deps = $params['input_name_deps'] ? $params['input_name_deps'] : 'deps';
	$input_name_contacts .= '[]';
	$input_name_deps .= '[]';
	$type = $params['type'] ? $params['type'] : 'checkbox';
	
	//默认选定人员
	$member = $params['member'] ? $params['member'] : "[]";
	$member = json_decode($member, true);
	foreach ($member as & $m) {
		$m['input_name'] = $input_name_contacts;
	}
	
	//默认选定部门
	$deps = $params['deps'] ? $params['deps'] : "[]";
	$deps = json_decode($deps, true);
	foreach ($deps as & $d) {
		$d['input_name'] = $input_name_deps;
	}
	//合并人员与部门
	$default = array_merge($member, $deps);
	$default = json_encode($default);
	
	$deps = $params['deps'] ? $params['deps'] : "";
	$args = "window.default_arguments = {
		container: '#contact_container',
		input_type: '{$type}',
		input_name_contacts: '{$input_name_contacts}',
		input_name_deps: '{$input_name_deps}',
		contacts_default_data: $default
	};";
	$html = "
<script> 
window._app = 'contacts_pc';
window.default_view = 'contacts';
{$args}
window._root = '/static/mobile/';
</script>
<script src=\"/static/mobile/lib/requirejs/require.js\" data-main=\"/static/mobile/main.js\"></script>";
	return '<div class="col-sm-9" id="contact_container">'.$html.'</div>';
}
