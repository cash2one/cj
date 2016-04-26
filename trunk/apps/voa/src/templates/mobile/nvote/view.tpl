{include file='mobile/header.tpl' navtitle=$data['subject']|escape}
<style>
    .finished{
        position: absolute;
        top:-30px;
        right:20px;
        width:60px;
        height:60px
    }
    .view-wrap{
        white-space:normal;
        word-break:break-all;
    }
</style>
<div id="template"></div>
<ul class="ui-list ui-list-text ui-border-no">
    <li class="ui-border-t">
        <h4>{$data['subject']|escape}</h4>
    </li>
    <li class="ui-border-t">
        {if $data['is_end'] == 0}
            <img src="/misc/images/finished.png" class="finished"/>
        {/if}
        <div class="ui-list-info">
            <p class="ui-text-name">{$data['m_username']|escape}</p>
            <p>截止日期: {$data['end_time']}</p>
            <p>投票人数: {$data['count_mem']}人</p>

        </div>
        <div class="ui-list-action ui-text-realname">{$data['_is_show_name']|escape}</div>

    </li>
    {*是否有附件*}
    {if $data['attachment']}
    <li>
        {$att[0]['aid'] = $data['at_id']}
        {$att[0]['url'] = $data['attachment']}
        {cyoa_view_image attr_id='nvote_image' bigsize="0" thumbsize="640" name='atids2' attachs=$att onlymodule="1"}
    </li>
    {/if}
</ul>
{*判断是否可以投票*}
{if $data['is_can_vote'] == 1}
<form action="/frontend/nvote/vote" id="form_nvote_vote" method="post">
    <input type="hidden" name="nv_id" value="{$data.nv_id}" />
<ul class="ui-list ui-border-tb ui-list-radio-right">
    {foreach $data['options'] as $index=>$option}
    <li class="ui-border-t">
        <span class="ui-list-order">{$index + 1}.</span>
        {if $option.attachment}
            {$att[0]['aid'] = $option['at_id']}
            {$att[0]['url'] = $option['attachment']}
            {cyoa_view_image id=$option['at_id'] bigsize="0" name='atids2' attachs=$att }
        {/if}
        <label class="ui-list-info" for="input_{$option.nvo_id}">
            <h4 class="view-wrap">{$option.option|escape}</h4>
        </label>
        {*判断单选多选*}
        {if $data['is_single'] == voa_d_oa_nvote::SINGLE_YES}
            <label class="ui-radio">
                <input type="radio" id="input_{$option.nvo_id}" name="nvo_id" value="{$option.nvo_id}"></label>
        {else}
            <label class="ui-checkbox">
                <input type="checkbox" id="input_{$option.nvo_id}" name="nvo_id[]" value="{$option.nvo_id}"></label>
        {/if}
    </li>
    {/foreach}
</ul>
{if $data['is_single'] == voa_d_oa_nvote::SINGLE_YES}
<div class="ui-txt-muted">只能选择一个</div>
{/if}
<div class="ui-btn-wrap">
    <button class="ui-btn-lg ui-btn-primary" id="btn_new">投票</button>
</div>
</form>
{elseif $data['is_can_vote'] == 2}
{*显示结果*}
<ul class="ui-list ui-border-tb ui-list-radio-right">
    {foreach $data['options'] as $index=>$option}
    <li class="ui-border-t ui-padding-bottom-0">
        <span class="ui-list-order">{$index+1}.</span>
        {if $option.attachment}
            {$att[0]['aid'] = $option['at_id']}
            {$att[0]['url'] = $option['attachment']}
            {cyoa_view_image id=$option['at_id'] bigsize="0" name='atids2' attachs=$att }
        {/if}
        <div class="ui-list-info ui-padding-top-0">
            <h4>{$option.option|escape}</h4>
        </div>

    </li>
    <li class="ui-list-progress ui-margin-top-0">
        {if $data.is_show_result == voa_d_oa_nvote::SHOW_RESULT_YES}
        <div class="ui-progress">
            <span class="{if ($index+1) % 4 == 3}primary{elseif ($index + 1) % 4 == 2}success{elseif ($index + 1) % 4 == 1}{elseif ($index+1) % 4 == 0}danger{/if}" style="width:{if $option.nvotes > 0}{rintval(($option.nvotes / $data.count_nvotes) * 100)}{else}0{/if}%"></span>
        </div>
        {/if}
        <p class="ui-list-action">{if array_key_exists($option['nvo_id'], $mem_options)}<span class="myvote">我的投票</span>{/if}{if $data.is_show_result == voa_d_oa_nvote::SHOW_RESULT_YES}{$option.nvotes}票  {if $option.nvotes > 0}{rintval(($option.nvotes / $data.count_nvotes) * 100)}{else}0{/if}%{/if}</p>
    </li>
    {/foreach}
</ul>
{elseif $data['is_can_vote'] == 4}
{*重复投票*}
<form action="/frontend/nvote/vote" id="form_nvote_vote" method="post">
    <input type="hidden" name="nv_id" value="{$data.nv_id}" />
    <ul class="ui-list ui-border-tb ui-list-radio-right">
        {foreach $data['options'] as $index=>$option}
            <li class="ui-border-t">
                <span class="ui-list-order">{$index + 1}.</span>
                {if $option.attachment}
                    {$att[0]['aid'] = $option['at_id']}
                    {$att[0]['url'] = $option['attachment']}
                    {cyoa_view_image id=$option['at_id'] bigsize="0" name='atids2' attachs=$att }
                {/if}
                <label class="ui-list-info" for="input_{$option.nvo_id}">
                    <h4>{$option.option|escape}</h4>
                </label>
                {*判断单选多选*}
                {if $data['is_single'] == voa_d_oa_nvote::SINGLE_YES}
                    <label class="ui-radio">
                        <input type="radio" id="input_{$option.nvo_id}" name="nvo_id" value="{$option.nvo_id}"></label>
                {else}
                    <label class="ui-checkbox">
                        <input type="checkbox" id="input_{$option.nvo_id}" name="nvo_id[]" value="{$option.nvo_id}"></label>
                {/if}
            </li>
            <li class="ui-list-progress ui-margin-top-0">
                {if $data.is_show_result == voa_d_oa_nvote::SHOW_RESULT_YES}
                    <div class="ui-progress">
                        <span class="{if ($index+1) % 4 == 3}primary{elseif ($index + 1) % 4 == 2}success{elseif ($index + 1) % 4 == 1}{elseif ($index+1) % 4 == 0}danger{/if}" style="width:{if $option.nvotes > 0}{rintval(($option.nvotes / $data.count_nvotes) * 100)}{else}0{/if}%"></span>
                    </div>
                {/if}
                <p class="ui-list-action">{if array_key_exists($option['nvo_id'], $mem_options)}<span class="myvote">我的投票</span>{/if}{if $data.is_show_result == voa_d_oa_nvote::SHOW_RESULT_YES}{$option.nvotes}票  {if $option.nvotes > 0}{rintval(($option.nvotes / $data.count_nvotes) * 100)}{else}0{/if}%{/if}</p>
            </li>
        {/foreach}
    </ul>
    {if $data['is_single'] == voa_d_oa_nvote::SINGLE_YES}
        <div class="ui-txt-muted">只能选择一个</div>
    {/if}
    {if $data['close_status'] != 2 || $data['is_end'] != 0}
    <div class="ui-btn-wrap">
        <button class="ui-btn-lg ui-btn-primary" id="btn_new">重新投票</button>
    </div>
    {/if}
</form>
{/if}

{if $data['is_can_close'] == 1}
    <form action="/frontend/nvote/close" id="form_nvote_close" method="post">
        <input name="nv_id" value="{$data['nv_id']}" type="hidden"/>
        <div class="ui-btn-wrap">
            <button class="ui-btn-lg" id="btn_close">结束此投票</button>
        </div>
    </form>
{/if}
<div id="templates" class="ui-form ui-margin-0 ui-border-no ">

</div>
{literal}
<script type="text/template" id="templates_tpl">

</script>
<script type="text/javascript">
    require(["zepto", "underscore", "submit" ,"showlist", "frozen"], function($, _, submit, showlist) {
        //关闭投票
        var sbt_close = new submit();
        sbt_close.init({"form": $("#form_nvote_close")});

        //投票
        var sbt_vote = new submit();
        sbt_vote.init({"form": $("#form_nvote_vote")});

        //投票判断
        $('#btn_new').on('click', function(){
            {/literal}
            {if $data['is_single'] == voa_d_oa_nvote::SINGLE_YES}
                var option_id = $('#form_nvote_vote input[type=radio]:checked').val();
            {else}
                var option_id = $('#form_nvote_vote input[type=checkbox]:checked').val();
            {/if}
            {literal}
            if (!option_id || option_id == undefined) {
                $.tips({content:'请选择选项'});
                return false;
            }
        });
        {/literal}
        var nv_id = {$data['nv_id']};

        {if $data['is_show_result'] == voa_d_oa_nvote::SHOW_RESULT_YES &&
            $data['is_show_name'] == voa_d_oa_nvote::SHOW_NAME_YES &&
            ($data['is_can_vote'] == 2 || $data['is_can_vote'] == 4) }
        {literal}
        var st = new showlist();
        st.show_ajax({'url': '/api/nvote/get/memoptions?nv_id=' + nv_id}, {
            'dist': $('#templates'),
            "tpl": $("#templates_tpl"),
            "datakey" : "data"
        });
        {/literal}
        {/if}
        {literal}
    });
</script>
{/literal}
<script type="text/javascript">
    require(["zepto","wxshare"], function($, WXShare) {

        {* 调用分享接口 *}
        {$_cyoa_jsapi_[] = 'onMenuShareTimeline'}
        {$_cyoa_jsapi_[] = 'onMenuShareAppMessage'}
        {$_cyoa_jsapi_[] = 'onMenuShareQQ'}
        {$_cyoa_jsapi_[] = 'onMenuShareWeibo'}
        var wxshare = new WXShare();
        
        wxshare.load({rjson_encode($share_data)});
    });
</script>
{include file='mobile/footer.tpl'}