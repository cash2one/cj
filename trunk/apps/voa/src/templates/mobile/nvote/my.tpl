{include file='mobile/header.tpl' navtitle='我的投票'}

<div class="ui-top-border"></div>
<div id="templates"></div>

<script type="text/javascript">
    var action = '{$action}';
</script>


{literal}

<script id="templates_tpl" type="text/template">
    <%if(_.isEmpty(data)){%>
    <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
        <p>暂无投票数据</p>
    </section>
    <%}else{%>
        <ul class="ui-list ui-border-tb ui-vote-list">
        <%_.each(data, function(item,index) {%>
        <li class="ui-border-b" data-href="/frontend/nvote/view?nv_id=<%=item.nv_id%>">
            <div class="ui-list-info">
                <h4><%=item.subject%></h4>
                <p class="ui-nowrap"><%=item.created%></p>
            </div>
            <div class="ui-list-action">
                <h4 class="ui-circle-<%if( item.nv_status == '进行中' ) {%>finish<%}else{%>underway<%}%>"><%=item.nv_status%></h4>
                <p><%=item.is_show_name%></p>
            </div>
        </li>
        <%});%>
    <%}%>
</ul>
</script>

<script type="text/javascript">
    require(["zepto", "underscore", "showlist"], function($, _, showlist) {
        //载入投票列表数据
        var st = new showlist();
        st.show_ajax({'url': '/api/nvote/get/my',"data": {'action': action}}, {
            'dist': $('#templates'),
            'cb': function(dom) {
                //点击投票跳转到投票详情页
                dom.find('.ui-vote-list li').bind('click',function(){
                    location.href= $(this).data('href');
                });
            },
            "datakey" : "data",
            "tpl": $("#templates_tpl")
        });


    });
</script>
{/literal}
{include file='mobile/footer.tpl'}