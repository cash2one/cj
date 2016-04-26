{include file='mobile_v1/header_fz.tpl'}


<div class="ui-list-material">
    <div class="ui-selector ui-selector-line">
        <div class="ui-selector-content">
            <ul>
                <li class="ui-selector-item ui-border-b ui-center">
                    <a href="/frontend/travel/cpfodders" class="ui-back-a"> <i class="ui-back"></i>
                    </a>
   					         素材库
                </li>
            </ul>
        </div>
    </div>
    <div class="ui-list">
        <div class="ui-main-content">{$fodders['subject']}</div>
        <div class="ui-border-t ui-txt-muted ui-padding-bottom-0 ui-margin-bottom-0">
               	 素材图片(长按可保存到本地)
        </div>
        <ul class="ui-grid-halve ui-padding-top-0">
        {if empty($attachs)}
        <section class="ui-notice ui-notice-norecord"> <i class="ui-margin-top-10"></i>
                    <p class="ui-margin-top-10  ui-margin-bottom-10">暂无素材内容</p>
                    <p class="ui-margin-bottom-10"><small>请联系企业管理进行添加</small></p>
        </section>
        {else}
    	 {foreach $attachs as $ach}
    	     <li>
                <div class="ui-grid-halve-img ui-border">
                     <img src="{$ach.url}">
                </div>
             </li>
         {/foreach}
        {/if}
        </ul>
    </div>
    <div class="ui-txt-muted">产品方案&短链(长按选择复制)</div>
    <div class="ui-list">
        <div class="ui-main-content"  style="post">{$fodders['fodder_sub']}<br />{$fodders['fodder_link']}</div>
    </div>
</div>

<script type="text/javascript">
require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$(document).ready(function() {
       $('body').addClass('user-select');
	});
});
</script>

{include file='mobile_v1/footer_fz.tpl'}