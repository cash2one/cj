{*
	H5/前端模板 文本输入区
	
	$_textarea_name		string	可选	文本输入区的name
	$_textarea_value	string	可选	默认值
	$_textarea_id		string	可选	文本输入区的ID
	$_textarea_title	string	可选	标题
	$_textarea_placeholder	string	可选	提示文字
	$_textarea_maxlength	int	可选	最多允许输入的字符数，0或为空不限制
	$_textarea_extra	array	可选	扩展属性
*}
{* 初始化变量 *}
{if empty($_textarea_name)}
	{$_textarea_name = ''}
{/if}
{if empty($_textarea_value)}
	{$_textarea_value = ''}
{/if}
{if empty($_textarea_id)}
	{$_textarea_id = ''}
{/if}
{if empty($_textarea_title)}
	{$_textarea_title = ''}
{/if}
{if empty($_textarea_placeholder)}
	{$_textarea_placeholder = '输入文字'}
{/if}
{if empty($_textarea_maxlength)}
	{$_textarea_maxlength = 0}
{/if}

<div class="ui-form-item ui-form-item-textarea ui-border-t clearfix">
	{if $_textarea_title}<label{if $_textarea_id} for="{$_textarea_id}"{/if}>{$_textarea_title}</label>{/if}
	<textarea{if $_textarea_id} id="{$_textarea_id}"{/if}{if $_textarea_name} name="{$_textarea_name}"{/if} placeholder="{$_textarea_placeholder}">{$_textarea_value}</textarea>
</div>
{if $_textarea_maxlength > 0}<div class="remaining-words">剩余字数30</div>{/if}

