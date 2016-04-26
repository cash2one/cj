{*
	H5/公共文本输入框模板

	$type_name			string	必须	文本输入框的name
	$type_title			string	可选	文本输入框的标题
	$type_id			string	可选	文本输入框的ID
	$type_placeholder	string	可选	默认显示的提示文字
	$type_extra			array	可选	其他扩展属性
*}
{* 初始化变量 *}
{if empty($type_name)}
	{$type_name = ''}
{/if}
{if empty($type_title)}
	{$type_title = ''}
{/if}
{if empty($type_id)}
	{$type_id = ''}
{/if}
{if empty($type_placeholder)}
	{$type_placeholder = ''}
{/if}
<div class="ui-form-item">
	<label{if $type_id} for="{$type_id}"{/if}>{if !empty($type_title)}{$type_title}{/if}</label>
	<input type="text"{if $type_id} id="{$type_id}"{/if} name="{$type_name}"{if $type_placeholder} placeholder="{$type_placeholder}"{/if} />
</div>
