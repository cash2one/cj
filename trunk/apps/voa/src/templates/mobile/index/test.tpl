{include file='mobile/header.tpl' navtitle='Template test'}

<section id="seca" class="section_container">
	<!--
	<ul class="ui-tab-nav ui-border-b">
		<li>用户</li>
		<li>部门</li>
	</ul>
	<ul id="userdp" class="ui-tab-content" style="width:300%">
		<li data-dist="user_ul"><ul class="ui-list ui-list-text ui-list-link ui-border-b content" id="user_ul"></ul></li>
		<li data-dist="dp_ul"><ul class="ui-list ui-list-text ui-list-link ui-border-b content" id="dp_ul"></ul></li>
	</ul>
	-->
	<h2 class="title ui-border-b"><a href="index.html"  class="ui-arrowlink">Frozen UI</a>弹窗 </h2>

	<div id="src">
		<input type="hidden" id="uids1" value="2,4" />
		<input type="hidden" id="dpids1" value="" />
		<div class="ui-form-item ui-border-t ui-form-contacts">
			<label>接收人</label>
			<p>&nbsp;</p>
			<a href="javascript:;" class="ui-icon-add ui-icon"></a>
		</div>
		<div class="ui-form-item ui-form-contacts ui-border-t clearfix _addrbook_list"></div>
		<div class="ui-form-item ui-form-contacts ui-border-t clearfix _dpname_list"></div>
	</div>


	<div id="src_1">
		<input type="hidden" id="uids2" value="2,4" />
		<input type="hidden" id="dpids2" value="" />
		<div class="ui-form-item ui-border-t ui-form-contacts">
			<label>接收人</label>
			<p>&nbsp;</p>
			<a href="javascript:;" class="ui-icon-add ui-icon"></a>
		</div>
		<div class="ui-form-item ui-form-contacts ui-border-t clearfix _addrbook_list"></div>
		<div class="ui-form-item ui-form-contacts ui-border-t clearfix _dpname_list"></div>
	</div>

	 <div class="ui-form-item ui-form-contacts ui-border-t clearfix">
         <div class="ui-badge-wrap">
             <div class="ui-badge-cornernum"></div>
             <div class="ui-avatar-s">
                 <span style="background-image:url(http://placehold.sinaapp.com/?80*80)"></span>
             </div>
             <div class="name">hhh</div>
         </div>
         <div class="ui-badge-wrap">
             <div class="ui-badge-cornernum"></div>
             <div class="ui-avatar-s">
                 <span style="background-image:url(http://placehold.sinaapp.com/?80*80)"></span>
             </div>
             <div class="name">hhh</div>
         </div>
         <div class="ui-badge-wrap">
             <div class="ui-badge-cornernum"></div>
             <div class="ui-avatar-s">
                 <span style="background-image:url(http://placehold.sinaapp.com/?80*80)"></span>
             </div>
             <div class="name">hhh</div>
         </div>
     </div>

    <div class="ui-form-item ui-form-contacts ui-border-t clearfix">
        <div class="ui-badge-wrap ui-border ui-contact-part">
            <div class="ui-badge-cornernum"></div>
            <span>内容</span>
        </div>
        <div class="ui-badge-wrap ui-border ui-contact-part">
            <div class="ui-badge-cornernum"></div>
            <span>内容</span>

        </div>
        <div class="ui-badge-wrap ui-border ui-contact-part">
            <div class="ui-badge-cornernum"></div>
            <span>内容</span>

        </div>
    </div>

	<div class="ui-center">
	    <div class="ui-btn" id="btn1">模板创建弹窗</div>
	    <div class="ui-btn" id="btn2">DOM创建弹窗</div>
	</div>
	<div class="ui-dialog">
	    <div class="ui-dialog-cnt">
	        <div class="ui-dialog-bd">
	            <div>
	            <h4>标题</h4>
	            <div>内容</div></div>
	        </div>
	        <div class="ui-dialog-ft ui-btn-group">
	            <button type="button" data-role="button"  class="select" id="dialogButton<%=i%>">关闭</button>
	        </div>
	    </div>
	</div>
	<form id="sbtfrm" action="/frontend/askoff/new" method="post">
		<div class="ui-form">
			{include file="mobile/h5mod/input_text.tpl" type_id='ddd' type_name="abc" type_title="ABC"}
			{include file="mobile/h5mod/input_text.tpl" type_name="abcd" type_title="ABCD2"}
			{include file="mobile/h5mod/input_text.tpl" type_name="abcd2" type_title="ABCD3"}
			{include file="mobile/h5mod/input_text.tpl" type_name="abcd3" type_title="ABCD4"}
			{include file="mobile/h5mod/input_text.tpl" type_name="abcd4" type_title="ABCD5"}
		</div>
		<button>submit</button>
	</form>
	<ul id="test"></ul>
</section>
<section id="secb" class="section_container">
	<div id="addrbook" class="ui-tab"></div>
</section>

{literal}
<script id="user_ul_tpl" type="text/template">
	<%_.each(list, function(user) {%>
	<li class="ui-border-t">
		<h4 class="ui-nowrap"><%=user.realname%></h4>
	</li>
	<%});%>
</script>
<script id="user_dp_tpl" type="text/template">
	<%_.each(lists, function(dp) {%>
	<li class="ui-border-t">
		<h4 class="ui-nowrap"><%=dp.name%></h4>
	</li>
	<%});%>
</script>
<script id="test_tpl" type="text/template">
	<ul class="ui-list ui-list-text ui-list-link ui-border-b">
		<%_.each(data, function(item) {%>
		<li class="ui-border-t">
			<h4 class="ui-nowrap"><%=item.subject%></h4>
		</li>
		<%});%>
	</ul>
</script>
<script id="addr_book_tpl" type="text/template">
	<ul class="ui-tab-nav ui-border-b">
		<li class="current">热门推荐</li>
		<li>全部表情</li>
		<li>表情</li>
	</ul>
	<ul class="ui-tab-content" style="width:300%">
		<li class="current"><p>内容</p><p>内容</p><p>内容</p><p>内容</p></li>
		<li>
			<ul class="ui-list ui-list-text ui-list-link ui-border-b">
				<%_.each(data, function(item) {%>
				<li class="ui-border-t">
					<h4 class="ui-nowrap"><%=item.subject%></h4>
				</li>
				<%});%>
			</ul>
		</li>
		<li><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p><p>内容</p></li>
	</ul>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "submit", "frozen"], function($, _, submit, fz) {
	// 返回格式, 和 api 接口保持一致
	// 如果需要跳转, 则 result = array('url' => 'https://...', 'message' => 'succeed');
	// 如果只是提示, 则 result = 'succeed';
	var sbt = new submit();
	sbt.init({"form": $("#sbtfrm"), "src": $("#btn1"), "src_event": "tap"});
});
require(["zepto", "underscore", "showtabs", "showlist", "addrbook", "frozen"], function($, _, showtabs, showlist, addrbook, fz) {
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': '/api/project/get/list'}, {
		"dist": $('#test'),
		"datakey": "data",
		"tpl": $("#test_tpl"),
		"cb": function(dom) {
			//alert(dom.html());
		}
	});
	$("#btn1").on('click', function(e) {
		sl.reinit({'url': '/api/project/get/list', 'data': {'a': 'b', 'c': 10}});
	});
	$('#btn2').on('click', function(e) {
		sl.reinit({'url': '/api/project/get/list'});
	});
	/**var st = new showtabs();
	st.show({
		"dist": $('#userdp'),
		"tabs": [
			{
				"dist": "user_ul",
				"ajax": {"url": "/api/addressbook/get/list"},
				"cb": function(dom) {
					alert(dom.html());
				}
			},
			{
				"dist": "dp_ul",
				"ajax": {"url": "/api/addressbook/get/departments"}
			}
		]
	});*/

    // 通讯录
	var ab = new addrbook();
	ab.show({
		"dist": $('#addrbook'),
		"ac": '',
		"src": $('#src'),
		"tabs": {
			"user": {
			"name":"选择用户","max":-1,"input":$('#uids1')
			},
			"dp": {
			"name":"选择部门","max":-1,"input":$('#dpids1'),"datakey":"lists"
			}
		},
		"selectall": true
	});
	var ab = new addrbook();
	ab.show({
		"dist": $('#addrbook'),
		"ac": '',
		"src": $('#src_1'),
		"tabs": {
			"user": {
			"name":"选择用户","max":-1,"input":$('#uids2')
			}
		},
		"selectall": false
	});

	/**var ab_1 = new addrbook();
	ab_1.show({
		"dist": $('#addrbook'),
		"src": $('#btn2'),
		"tabs": {
			"user": {
				"name": "选择用户",
				// uid: 当前操作的用户, checked: 选中(true)还是剔除(false)
				"cb": function(uid, checked) {
					//alert(uid + "," + checked);
				}
			}
		},
		"cb": function(ab) {
			//alert("AAAAAA");
		}
	});*/

});
</script>
{/literal}

{include file='mobile/footer.tpl'}
