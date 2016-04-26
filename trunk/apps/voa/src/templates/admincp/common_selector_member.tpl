{*
	后台人员/部门选择器
	$input_type 选择类型 radio=单选，checkbox=多选，默认：radio=单选
	$input_name 选择“人员”控件的名字，默认：uid
	$input_name_department 选择“部门”控件的名字，默认：department
	$selector_box_id 选择器所在控件ID，默认：id-search-uid
	$default_data 默认初始化的人员/部门json，默认：[]
	array(
		array('id' => m_uid|cd_id, 'name' => username | department, 'input_name' => 'contacts[]'|'deps[]'),
		array('id' => m_uid|cd_id, 'name' => username | department, 'input_name' => 'contacts[]'|'deps[]'),
		... ...
	)
	$allow_member 是否允许选择人员，true=是，false=否，默认：true
	$allow_department 是否允许选择部门，true=是，false=否，默认：false
*}

<!--
{if !defined('__selector_member__')}
	{define('__selector_member__', 1)}
	{$__selector_member_first = 1}
{else}
	{$__selector_member_first = 0}
{/if}
-->

{* 这段代码是为了替换给定的默认数据内input名称的，主要是解决js不识别 *}
{if !empty($default_data)}
	{$tmp = $default_data}
	{if is_string($default_data)}
		{$tmp = json_decode($default_data, true)}
	{/if}
	{foreach $tmp as $_key =>$_val}
	   {if isset($tmp[$_key]['input_name'])}
	       {if $allow_member}
	           {$tmp[$_key]['input_name'] = $input_name}
	       {/if}
	   {else}
           {if $allow_department}
               {$tmp[$_key]['input_name'] = $input_name_department}
           {/if}
       {/if}
	{/foreach}
	{$default_data = rjson_encode($tmp)}
{/if}

{if empty($input_type)}
	{$input_type = 'radio'}
{/if}
{if empty($input_name)}
	{$input_name = 'uid'}
{/if}
{if empty($input_name_department)}
	{$input_name_department = 'department'}
{/if}
{if empty($selector_box_id)}
	{$selector_box_id = 'id-search-uid'}
{/if}
{if empty($allow_member) && $allow_member !== false}
	{$allow_member = 1}
{/if}
{if empty($allow_department) && $allow_department !== false}
	{$allow_department = 0}
{/if}

<div id="{$selector_box_id}"></div>

<script type="text/javascript">
{if $__selector_member_first}
window._app = "contacts_pc";
window._root = '{$FM_JSFRAMEWORK}';
window.version = 0;
var view_member = [];
{/if}
</script>
{if $__selector_member_first}
<script type="text/javascript" src="{$FM_JSFRAMEWORK}lib/requirejs/require.js"></script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}config.js"></script>
{/if}

<script type="text/javascript">
requirejs(["jquery", "views/contacts"], function($, contacts) {
	$(function () {
		view_member['{$selector_box_id}'] = new contacts();
		view_member['{$selector_box_id}'].render({
			"input_type": "{$input_type}",
			"input_name_contacts": "{$input_name}",
			"input_name_deps": "{$input_name_department}",
			"contacts_default_data": {if empty($default_data)}[]{else}{$default_data}{/if},
			"container": "#{$selector_box_id}",
			"deps_enable": {if $allow_department}true{else}false{/if},
			"contacts_enable": {if $allow_member}true{else}false{/if}
		});
	});
});
</script>
