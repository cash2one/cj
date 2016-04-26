{include file='mobile/header.tpl' navtitle='投票列表'}

<div class="ui-tab">
    <ul class="ui-tab-nav ui-border-b">
        <li class="current">进行中</li>
        <li>已结束</li>
    </ul>
    <ul class="ui-tab-content" style="width:300%" id="list_container">
        <li data-dist="voting_ul"><ul class="ui-list ui-list-text" id="voting_ul"></ul></li>
        <li data-dist="voted_ul"><ul class="ui-list ui-list-text" id="voted_ul"></ul></li>
    </ul>
</div>
{literal}
<script type="text/template" id="list_tpl">
    <%if(_.isEmpty(data)){%>
    <section class="ui-notice ui-notice-norecord" style="padding-bottom: 80px;"> <i></i>
    <p>暂无投票数据</p>
    </section>
    <%}else{%>
        <%_.each(data, function(item) {%>
            <li class="ui-border-t" data-href="/frontend/nvote/view?nv_id=<%=item.nv_id%>">
                <div class="ui-list-info">
                    <h4 class="ui-nowrap"><%=item.subject%></h4>
                    <p><%=item.m_username%> &nbsp;&nbsp;<%=item.created%></p>
                </div>
                <div class="ui-list-right">
                    <h4>&nbsp;</h4>
                    <p class="ui-nowrap"><%=item.is_show_name%></p>

                </div>
            </li>
        <%});%>
    <%}%>
</script>
<script type="text/javascript">
    require(["zepto", "underscore", "showtabs", "frozen"], function($, _, showtabs) {
        $(function($){
            var st = new showtabs();
            st.show({
                "dist": $('#list_container'),
                "tabs": [
                    {
                        "name": "进行中",
                        "dist": "voting_ul",
                        "tpl" : '#list_tpl',
                        "datakey" : "data",
                        "ajax": {"url": "/api/nvote/get/list","data": {'action': 'receive', 'vote_status': 1}},
                        "cb": function(dom){
                            //点击跳转到投票详情页
                            dom.find('.ui-list-text li').bind('click',function(){
                                location.href= $(this).data('href');
                            });
                        }
                    },
                    {
                        "name": "已结束",
                        "dist": "voted_ul",
                        "tpl" : '#list_tpl',
                        "datakey" : "data",
                        "ajax": {"url": "/api/nvote/get/list","data": {'action': 'receive', 'vote_status': 2}},
                        "cb": function(dom){
                            //点击跳转到投票详情页
                            dom.find('.ui-list-text li').bind('click',function(){
                                location.href= $(this).data('href');
                            });
                        }
                    }
                ]
            });

        });

    });
</script>
{/literal}
{include file='mobile/footer.tpl'}