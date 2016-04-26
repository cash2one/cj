{include file='mobile/header.tpl' navtitle='红包' css_file='app_redpack.css'}

<section class="red-avatar red-receive">
    <div class="ui-avatar-one red-avatar-one">
        <span style="background-image:url({$cinstance->avatar($wbs_uid)})"></span>
    </div>
    <p>{if 0 < $year}{$year}{/if}共收到</p>
    <h1>{$redpack_total['_money']}<span>元</span></h1>
</section>
<div class="red-receive-float">
    <h2>{$redpack_total['rp_count']}</h2>
    <p>收到的红包</p>
</div>
<div class="red-receive-float">
    <h2>{$redpack_total['highest_count']}</h2>
    <p>手气最佳</p>
</div>
<div class="clearfix"></div>

<ul id="rp_list" class="ui-list ui-list-text red-ui-list"></ul>

{literal}
<script id="rp_list_tpl" type="text/template">
<%if(_.isEmpty(list)){%>
<section class="ui-notice ui-notice-norecord">
    <i></i>
    <p>暂无数据</p>
</section>
<%$('#templates').removeClass('ui-list');%>
<%}else{%>
<%_.each(list, function(item, index) {%>
<li class="ui-border-t li_redpack" data-href="/frontend/redpack/view/redpack_id/<%=item.redpack_id%>">
    <div class="ui-list-info">
        <h4 class="ui-nowrap"><%=item.from_username%></h4>
        <p><%=item._created%></p>
    </div>
    <div class="ui-list-right">
        <h4><%=item._money%>元</h4>
    </div>
</li>
<%});%>
<%}%>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "frozen", "showlist"], function($, _, fz, showlist) {
    var st = new showlist();
    st.show_ajax({'url': '/api/redpack/get/receivedall'}, {
        'dist': $('#rp_list')
    });

    // 列表点击时间
    $("#rp_list").on('click', '.li_redpack', function(e) {
        location.href = $(this).data("href");
    });
});
</script>
{/literal}

{include file='mobile/footer.tpl'}