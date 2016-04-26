{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">搜索巡店 <button type="button" class="close"><span class="glyphicon glyphicon"></span></button></div>
	<div class="panel-body">
	<div class="form-group">
        <label class="col-sm-2 control-label">记录人</label>
        <div class="col-sm-4">{$inspect['m_username']}</div>
        <label class="col-sm-2 control-label">提交时间</label>
        <div class="col-sm-4">{$inspect['_updated_u']}</div>
    </div>
	<div class="form-group">
        <label class="col-sm-2 control-label">门店评分（{if 0 < $cache_config['score_rule_diy']}合格率{else}总分{/if}）</label>
        <div class="col-sm-4">{$inspect['ins_score']}{if 0 < $cache_config['score_rule_diy']}%{else}分{/if}</div>
        <label class="col-sm-2 control-label">区域</label>
        <div class="col-sm-4">
        	{if $p_region}{$p_region['cr_name']}{/if}
        	{if $c_region}{$c_region['cr_name']}{/if}
        	{$shop['csp_address']}
        </div>
    </div>
	<div class="form-group">
        <label class="col-sm-2 control-label">门店名称</label>
        <div class="col-sm-10">{if $shop}{$shop['csp_name']|escape}{/if}</div>
    </div>
	<div class="form-group">
		<label class="col-sm-2 control-label">接收人</label>
        <div class="col-sm-10">
        	{foreach $mem_tc['to'] as $_username}
				<span class="label label-success font12">{$_username}</span>
			{/foreach}
        </div>
	</div>
	<div class="form-group">
        <label class="col-sm-2 control-label">抄送人</label>
        <div class="col-sm-10">
        	{foreach $mem_tc['cc'] as $_username}
				<span class="label label-info font12">{$_username}</span>
			{/foreach}
        </div>
    </div>
</div>
</div>

<div class="panel">
	<div class="panel-body">
	<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
	    <div class="form-group">
	        <div class="col-sm-12">
			
			{foreach $items['p2c'][0] as $_insi_id}
				<p class="form-control-static">{$items[$_insi_id]['insi_name']} {if 0 < $cache_config['score_rule_diy']}合格率{else}总分{/if}：{$item2score[$_insi_id]}{if 0 < $cache_config['score_rule_diy']}%{else}分{/if}</p>
	            <table class="table table-bordered table-striped table-hover font12">
	            <thead>
	            <tr>
	                <th>评分项</th>
	                {if $cache_config['score_rule_diy'] == 1}
	                <th>评估结果</th>
	                {else}
	                <th>总分</th>
	                <th>分数</th>
	                {/if}
	                <th>具体问题</th>
	                <th>选项</th>
	                <th>相关图片</th>
	            </tr>
	            </thead>
	            {foreach $items['p2c'][$_insi_id] as $_c_insi_id}
	            <tr>
	                <td style="text-align:left;">{$items[$_c_insi_id]['insi_describe']}</td>
	                {if $cache_config['score_rule_diy'] == 1}
	                <td>{$cache_config['score_rules'][$inspect_score[$_c_insi_id]['isr_score']]}</td>
	                {else}
	               <td>{$items[$_c_insi_id]['insi_score']}</td>
	               <td>{$inspect_score[$_c_insi_id]['isr_score']}</td>
	               {/if}
	               <td style="text-align:left;">{$inspect_score[$_c_insi_id]['isr_message']}</td>
	                <td>{$items[$_c_insi_id]['options'][$inspect_score[$_c_insi_id]['isr_option']]}</td>
	                <td>
	                {foreach $att_list[$inspect_score[$_c_insi_id]['isr_id']] as $_at}
			        <img org="{$_at['orgpicurl']}" class="viewphoto" src="{$_at['picurl']}" />
					{/foreach}
				    </td>
				</tr>
				{/foreach}
				</table>
			{/foreach}
	
	        </div>
	    </div>
	</form>

	</div>
	<div class="panel-footer"> <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a></div>
</div>
{include file="$tpl_dir_base/footer.tpl"}
<script>
var getUsersUrl = "{$getUsersUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";
{literal} 
$(function() {
$('body').on('mouseover', '.viewphoto', function(event){
        
        if (event.type === 'mouseover') {
            var el=$(this);
            var pic = el.attr('org');
            if (pic) {
               
                        html = '<img width="100%" src="'+pic+'"/>';
                        
                        el.removeClass('viewphoto');
                        
                        el.popover({trigger: 'hover', 
                            title: '', 
                            html: true,
                            //delay: { show: 500, hide: 100 },
                            placement: 'left',
                            template: '<div class="popover" style="width: 400px"><div class="popover-content" style="padding:0px"></div></div>',
                            content: html}).popover('show');

               
            }
        }  else {
            $(this).popover('hide');
        }
        return false;
 });
        
});


{/literal} 
</script>
