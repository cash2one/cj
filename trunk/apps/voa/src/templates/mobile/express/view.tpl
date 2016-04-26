{include file='mobile/header.tpl' }

<div class="kdzs-detail-title">
    <label>快递领取单</label>
    <span class="ui-icon kdzs-detail-received kdzs-detail-not-received"></span>
</div>
<div class="ui-form kdzs-ui-margin-top kdzs-ui-margin-bottom">
    <div class="ui-form-item ">
        <label for="#">快递代收人</label>
        <span class="ui-form-item-unit">{$express['r_username']}</span>
    </div>
    <div class="ui-form-item ui-border-t">
        <label for="#">代收时间</label>
        <span class="ui-form-item-unit">{$express['_created']}</span>
    </div>
    {if !empty($express['c_username'])}
    <div class="ui-form-item ui-border-t">
        <label for="#">代领人</label>
        <span class="ui-form-item-unit">{$express['c_username']}</span>
    </div>
    {/if}
    {if !empty($attachs)}
    <div class="ui-form-item ui-border-t">
        <label for="#">快递信息</label>
    </div>
    <div class="upload clearfix">
        <div class="ui-badge-wrap">
        {foreach $attachs as $ach}
           <img alt="" src="/attachment/read/{$ach.aid}">
        {/foreach}
        </div>
    </div>
    {/if}

    {if empty($express['c_username'])}
    <div class="ui-form-item">
    <a href="/frontend/express/newexpress?eid={$express['eid']}" class="kdzs-ui-instead">
             本人不在公司？点击找人代领
    </a>
    </div>
    {/if}
    <div class="ui-border-t">
        <div class="ui-barcode">
            <img src="/frontend/express/view?act=qrcode&eid={$express['eid']}" class="kdzs-img-size">
            <p class="ui-barcode-text">向前台人员出示二维码，即可快速领取快递。</p>
        </div>
    </div>
</div>
<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0 kdzs-ui-margin-bottom">
    <button id="cancle" class="ui-btn-lg">返回列表</button>
</div>
<br />

<script>
{literal}

require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$(document).ready(function() {

		//取消
		$('#cancle').on('click', function(e) {
			 var url = "/frontend/express/list";
	 	     window.location.href=url;  
		});
		
	});
});

{/literal}
</script>

{include file='mobile/footer.tpl'}