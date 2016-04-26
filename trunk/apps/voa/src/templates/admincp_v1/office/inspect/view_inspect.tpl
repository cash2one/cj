{include file='admincp/header.tpl'}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
    <div class="form-group">
        <label  class="col-sm-2 control-label">记录人</label>
        <div class="col-sm-5">
            {$inspect['m_username']}
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">提交时间</label>
        <div class="col-sm-5">
               {$inspect['ins_created']}
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">门店名称</label>
        <div class="col-sm-5">
               {$inspect['csp_name']}
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">门店评份（{if $cache_config['score_rule_diy'] == 1}合格率{else}总分{/if}）</label>
        <div class="col-sm-5">
            {if $cache_config['score_rule_diy'] == 1}
            {$inspect['score']}%
            {else}
            {$inspect['score']}
            {/if}
               
        </div>
    </div>
    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">门店评分（分项）</label>
        <div class="col-sm-5">
                    {if $inspect['inspect_lists']}
                    {foreach $inspect['inspect_lists'] as $key=>$item}
                    {if $cache_config['score_rule_diy'] == 1}
                    <label>{$item['insi_name']} 合格率：{$item['total_score']}%</label>
                    {else}
					<label>{$item['insi_name']} 总分：{$item['total_score']}</label>
					{/if}
                    <table class="table table-bordered table-striped table-hover font12">
                    <thead>
                    <tr>
                        <th>评分项</th>
                        {if $cache_config['score_rule_diy'] == 1}
                        <th>评估结果</th>
                        <th>现像描述</th>
                        {else}
                        <th>总分</th>
                        <th>分数</th>
                        <th>具体问题</th>
                        {/if}
                        <th>相关图片</th>
                    </tr>
                    </thead>
                    {foreach $item['childs'] as $child}
                    <tr>
                        <td>{$child['insi_describe']}</td>
                        
                        {if $cache_config['score_rule_diy'] == 1}
                        <td>{$child['score']['diy']}</td>
                        {else}
                        <td>{$child['insi_score']}</td>
                        <td>{$child['score']['isr_score']}</td>
                        {/if}
                        <td>{$child['score']['isr_message']}</td>
                        <td>
                       <!-- <img width="100" class="viewphoto" src="http://img5.cache.netease.com/cnews/2014/8/25/2014082508453768117.png" org="http://img2.cache.netease.com/cnews/2014/8/25/2014082508203011594.jpg" />-->
                            {if $child['score']['pic']}
                            {foreach $child['score']['pic'] as $pic}
                            <img org="{$pic['orgpicurl']}" class="viewphoto" src="{$pic['picurl']}" />
                            {/foreach}
                            {/if}
                        </td>
                    </tr>
                    {/foreach}
                    </table>
                    {/foreach}
                    {/if}

        </div>
    </div>
     
    <div class="form-group">
        
        <label class="col-sm-2 control-label">接收人</label>
        <div class="col-sm-5">
            {$mem['receiver']}
         </div>
    </div>
    <div class="form-group">
        
        <label class="col-sm-2 control-label">抄送人</label>
        <div class="col-sm-5">
            {$mem['bbc']}
         </div>
    </div>
    <!--
   <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label"></label>
        <div class="col-sm-5">
                    {if $data['it_csp_id_list']}
                    <table class="table table-striped table-hover font12">
                    {foreach $data['it_csp_id_list'] as $key=>$val}
                    <tr><td>{$val['csp_name']}</td><td>{$val['ins_status_text']}</td></tr>
                    {/foreach}
                    </table>
                    {/if}

        </div>
    </div> -->
    <div class="text-center">
        <p class="bg-info">
            <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a></p>
    
    </div>
    <br />
</form>


{include file='admincp/footer.tpl'}
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
                           // placement: 'bottom',
                            template: '<div class="popover" style="width: 400px"><div class="arrow"></div><div class="popover-content" style="padding:0px"></div></div>',
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
