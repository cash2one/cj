{include file='mobile/header.tpl' navtitle='审批详情'}

<div class="ui-top-box">
    <h2>{$askfor['af_subject']}</h2>
   <!-- <p>申请人：{$askfor['m_username']}</p> -->
</div>

<div class="ui-form ui-border-t">
	<div class="ui-form-item ui-form-item-show ui-border-b ui-conten-more">
        <label for="#">申请时间</label>
        <p>{$askfor['created']}</p>
    </div>
    <div class="ui-form-item ui-form-item-show ui-border-b ui-conten-more">
        <label for="#">审批内容</label>
        <p>{$askfor['af_message']}</p>
    </div>
{if $colsdata}
    {foreach $colsdata as $col}
    	{if $col['value'] != ''}
	    <div class="ui-form-item ui-form-item-show ui-border-b ui-conten-more">
	        <label for="#">{$col['name']|escape}</label>
	        <p>{$col['value']|escape}</p>
	     </div>
     	{/if}
    {/foreach}
{/if}
{if $carbon_copies!= ''}
<div class="ui-form-item ui-form-item-show ui-border-b ui-conten-more">
    <label for="#">抄送人</label>
    <p>{$carbon_copies}</p>
</div>	
{/if}
   
{if $attachs}
    {cyoa_view_image
    	attachs = $attachs
    	bigsize = 0
    }
{/if}
   
</div>

<!--审批进度-->
{if $procs}

	{foreach $procs as $proc}
	<ul class="ui-list ui-border-tb status-list">
    <li>
        {if !empty($proc['m_uid'])}
        <div class="ui-avatar-s">
            <span  style="background-image:url({$cinstance->avatar($proc['m_uid'])}//?100*100)"></span>
        </div>
        {/if}
        <div class="ui-list-info ui-border-t">
            <h4>{if !empty($proc['m_username'])}{$proc['m_username']}{else}{$proc['mp_name']}{/if}</h4>
            <p class="ui-nowrap">{$proc['_updated']}</p>
            <p>{$proc['afp_note']}</p>
            
        </div>
        <div class="{$proc['_class']}">{$proc['_status']}</div>
    </li>
    </ul>
    {/foreach}

{/if}

<!--自己操作-->
{if $askfor['m_uid'] == startup_env::get('wbs_uid')}
<div class="{if voa_d_oa_askfor::STATUS_NORMAL == $askfor['af_status']}ui-btn-group-tiled{/if} ui-btn-wrap">
	{if voa_d_oa_askfor::STATUS_NORMAL == $askfor['af_status']}
    <button class="ui-btn-lg" id="af_cancel" rel="/askfor/cancel/{$af_id}?handlekey=post">撤消</button>
    {/if}
    {if voa_d_oa_askfor::STATUS_NORMAL == $askfor['af_status'] || voa_d_oa_askfor::STATUS_REMINDER == $askfor['af_status'] || voa_d_oa_askfor::STATUS_APPROVE_APPLY == $askfor['af_status']}
    <button class="ui-btn-lg ui-btn-primary" id="af_reminder" rel="/askfor/reminder/{$af_id}?handlekey=post">催办</button>
    {/if}
</div>
{/if}
<!--审批人操作-->
{if !empty($cur_proc['m_uid'])}
    {if $cur_proc['m_uid'] == $wbs_user.m_uid && voa_d_oa_askfor_proc::STATUS_NORMAL == $cur_proc['afp_status']}
    <div class="ui-btn-group-tiled ui-btn-wrap">
        <button class="ui-btn-lg" id="af_refuse" rel="/askfor/refuse/{$af_id}?handlekey=post">驳回</button>
        <button class="ui-btn-lg ui-btn-primary" id="af_approve" rel="/askfor/approve/{$af_id}?handlekey=post">同意</button>
    </div>
    {/if}
{else}
    {if $cur_proc['mp_uid']}
        {foreach $cur_proc['mp_uid'] as $val}
            {if $val['m_uid'] == $wbs_user.m_uid && voa_d_oa_askfor_proc::STATUS_NORMAL == $cur_proc['afp_status']}
            <div class="ui-btn-group-tiled ui-btn-wrap">
                <button class="ui-btn-lg" id="af_refuse" rel="/askfor/refuse/{$af_id}?handlekey=post">驳回</button>
                <button class="ui-btn-lg ui-btn-primary" id="af_approve" rel="/askfor/approve/{$af_id}?handlekey=post">同意</button>
            </div>
            {/if} 
        {/foreach}
    {/if}
{/if}

<!--弹出框-->
<div class="ui-dialog">
    <div class="ui-dialog-cnt">
    	 <form name="frmpost" id="frmpost" method="post" action="">
        <div class="ui-dialog-bd">
				<input type="hidden" name="formhash" value="{$formhash}" />
				<textarea name="message" placeholder="" id="message"></textarea>
        </div>
        <div class="ui-dialog-ft ui-btn-group">
            <button type="button"   class="select" id="message_cancel">取消</button> 
            <button type="submit"  class="select" id="message_sure">确定</button>
        </div>
        </form>
    </div>        
</div>

{literal}
<script type="text/javascript">
require(["zepto", "underscore", "submit", "frozen"], function($, _, submit) {	
	//撤销审批
	$('#af_cancel').bind('click',function(){
		_show_form(this, '请输入撤销理由！',1);
	});
	//催办审批
	$('#af_reminder').bind('click',function(){
		_show_form(this, '请输入催办理由！',1);
	});
	//驳回审批
	$('#af_refuse').bind('click',function(){
		_show_form(this, '请输入驳回理由！',1);
	});
	//同意审批
	$('#af_approve').bind('click',function(){
		_show_form(this, '可选备注！',0);
	});
	
	function _show_form(e,tips,required) {
		var res = $(e).attr('rel');
		$('#frmpost').attr('action',res);
		$('#message').attr('placeholder',tips);
		$(".ui-dialog").dialog("show");
		$('#message').data('required', required);
		$('#message').val('');
	}

	$('#message_cancel').bind('click',function(){
		$(".ui-dialog").dialog("hide");
	});
	
	$('#message_sure').bind('click',function(){ 
		var message = $('#message').val();
		var required = $('#message').data('required');
		if (message == '' && required > 0) {
			$.tips({ content:'理由不能为空',stayTime:2000,type:"warn"});
			return false;
		}
		$(".ui-dialog").dialog("hide");
	});
	var sbt = new submit();
	sbt.init({"form": $("#frmpost")});
	
});
</script>
{/literal}

{include file='mobile/footer.tpl'}