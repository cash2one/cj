{include file='admincp/header.tpl'}
<script>
var getUsersUrl = "{$getUsersUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";

{literal} 

{/literal} 
</script>
<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
    <div class="form-group">
        <label  class="col-sm-2 control-label">记录人</label>
        <div class="col-sm-5">
            {$productive['m_username']}
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">提交时间</label>
        <div class="col-sm-5">
               {$productive['pt_created']}
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">门店名称</label>
        <div class="col-sm-5">
               {$productive['csp_name']}
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">门店评份（{if $cache_config['score_rule_diy'] == 1}合格率{else}总分{/if}）</label>
        <div class="col-sm-5">
            {if $cache_config['score_rule_diy'] == 1}
            {$productive['score']}%
            {else}
            {$productive['score']}
            {/if}
               
        </div>
    </div>
    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">门店评分（分项）</label>
        <div class="col-sm-5">
                    {if $productive['productive_lists']}
                    {foreach $productive['productive_lists'] as $key=>$item}
                    {if $cache_config['score_rule_diy'] == 1}
                    <label>{$item['pti_name']} 合格率：{$item['total_score']}%</label>
                    {else}
					<label>{$item['pti_name']} 总分：{$item['total_score']}</label>
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
                        <td>{$child['pti_describe']}</td>
                        
                        {if $cache_config['score_rule_diy'] == 1}
                        <td>{$child['score']['diy']}</td>
                        {else}
                        <td>{$child['pti_score']}</td>
                        <td>{$child['score']['ptsr_score']}</td>
                        {/if}
                        <td>{$child['score']['ptsr_message']}</td>
                        <td>
                            {if $child['score']['pic']}
                            {foreach $child['score']['pic'] as $pic}
                            <a href="{$pic['orgpicurl']}" target="_blank"><img src="{$pic['picurl']}" /></a>
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
                    {if $data['ptt_csp_id_list']}
                    <table class="table table-striped table-hover font12">
                    {foreach $data['ptt_csp_id_list'] as $key=>$val}
                    <tr><td>{$val['csp_name']}</td><td>{$val['pt_status_text']}</td></tr>
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
