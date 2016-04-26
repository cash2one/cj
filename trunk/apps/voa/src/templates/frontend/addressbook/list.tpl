{include file='frontend/header.tpl'}

<body id="">

{include file='frontend/mod_top_search.tpl' iptvalue=$sotext placeholder='搜索 输入人名'}

<div class="view2" id="viewstack">
    <section></section>
    <menu class="mod_members_panel"></menu>
</div>

{literal}
    <script>
        /** 名片管理 */
        require(['addressbook', 'dialog'], function (AddrbookComponent) {
            var type = 0; //对应于php中的define
            var url = '/frontend/member/list?type=0';
            var lhs = (window.location.href).split('?');
            lhs.shift();
            url += '&' + lhs.join('');
            MLoading.show('loading...');
            var ab = new AddrbookComponent(type);
            ab.patch(url, function (data) {
                this.render().open();
                MLoading.hide();
            });
        });
    </script>
{/literal}

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}
