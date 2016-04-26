{include file='mobile/header.tpl' }
<div id="bbs-detaile">
<div class="ui-list ui-list-text">
    <li>
        <div class="ui-avatar-s">
            {if $thread['uid'] == 0}
               <img alt="" src="/attachment/read/{$p_sets['offical_img']}">
            {else}
               <img src="{$cinstance->avatar($thread['uid'])}" />
            {/if}
        </div>
        <div class="ui-list-info">
            <h4 class="ui-nowrap">{$thread['username']}</h4>
            <p clas="clearfix">{$thread['_created']}</p>
        </div>
    </li>
    
    <li class="ui-padding-top-0 ui-border-b ui-main-title">{$thread['_subject']}</li>
    <li>
	    <div class="ui-main-content">
	        {$thread['_message']}
 	        <p>
	        {foreach $attachs as $ach}
                 <img alt="" src="/attachment/read/{$ach.aid}">
             {/foreach}
	        </p> 
	    </div>
    </li>
</div>

{if !empty($likes)}
<ul class="ui-list ui-border-no ui-bbs-box">
    <li class="ui-txt-muted {if $thread['likes'] > 4} ui-arrowlink {/if} ui-border-b " id="likes_list"  data-likes="{$thread['likes']}" data-lkname="{$thread['likename']}">
            <span class="name ui-nowrap" id="likes_name">
             {foreach $likes as $like name=foo}
                 {$like['username']}{if !$smarty.foreach.foo.last}、{/if}
             {/foreach}
            </span>
            {if $thread['likes'] > 4}
            <span class="ui-text-follow">等{$thread['likes']}人赞过</span>
            {/if}
    </li>
</ul>
{/if}


<ul class="ui-list ui-border-no ui-bbs-box" id="plist"></ul>

    {literal}
    <script id="plist_tpl" type="text/template">
        <%if (_.isEmpty(data)) {
        %>
        <%} else {%>
        <%_.each(data, function(dr, index) {
        %>
        <li>
            <div class="ui-avatar-s">
                <img src="<%=dr.face%>" />
            </div>
            <div class="ui-list-info <% if (data.length>index+1) {%> ui-border-b <%}%>" >
                <h4>
                    <%=dr.username%>
                    <% if(dr.p_uid > 0){%>
                        <span>回复</span>  <%=dr.p_username%>
                    <%}%>
                </h4>
             <p class="ui-nowrap"><%=dr._created%></p>
             <p class="ui-bbs-conten"><%=dr._message%></p>
            </div>
            <div class="ui-icon ui-icon-reply-more post_reply" data-pid="<%=dr.pid%>" data-uid="<%=dr.uid%>" data-username="<%=dr.username%>"></div>
        </li>
    <%});}%>
    </script>
    {/literal}

<div class="ui-tab-nav ui-tab-nav-footer ui-border-t">
    <div class="ui-btn-group-tiled ui-btn-wrap">
        <button id="likes" class="ui-btn ui-btn-outline" data-likes="{$thread['likes']}" > 
        	{if empty($islike)}
            <i class="ui-icon ui-icon-follow" id="like_i"></i> 
        	{else}
        	<i class="ui-icon ui-icon-follow-ok" id="like_i"></i>
        	{/if}
                              赞
        </button>
        <button class="ui-btn ui-btn-outline" id="reply" rel="/frontend/thread/reply?handlekey=post&tid={$thread['tid']}&p_uid={$uid}&p_username={$username}">
            <i class="ui-icon ui-icon-reply"></i>
                              评论
        </button>
    </div>
</div>

</div>

<!--弹出框-->
<div class="ui-dialog">
    <div class="ui-dialog-cnt">
    	<form name="frmpost" id="frmpost" method="post" action="">
        <div class="ui-dialog-bd">
				<input type="hidden" name="formhash" value="{$formhash}" />
				<textarea name="message" placeholder="评论" id="message" ></textarea>
        </div>
        <div class="ui-dialog-ft ui-btn-group">
            <button type="button"   class="select" id="message_cancel">取消</button> 
            <button type="submit"  class="select" id="message_sure">确定</button>
        </div>
        </form>
    </div>        
</div>

<script>
var listurl = "/api/thread/get/postlist?tid={$thread['tid']}";
{literal}
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': listurl}, {
		"dist": $('#plist'), 
		"datakey": "data",
		"cb": function(dom) {
			
		}
	});
});

{/literal}
</script>

<script>
var tid = '{$thread['tid']}';
{literal}
function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}


function show_dialog(content){
    var dia=$.dialog({
        title:'温馨提示',
        content:content,
        button:["确认","取消"]
    });

    dia.on("dialog:action",function(e){
        console.log(e.index)
    });
    dia.on("dialog:hide",function(e){
        console.log("dialog hide")
    });
}

require(["zepto", "underscore", "submit", "frozen"], function($, _, submit, fz) {
	
	//点赞
	$("#likes").on('click',function(e){
		var el = $(e.target);
		if(_.isUndefined(el.data('flag'))){
			el.data('flag', 'no'); 
			var url = "/frontend/thread/likes?tid="+tid;
			fun_ajx(url);
			el.html('<i class="ui-icon ui-icon-follow-ok" ></i> 赞'); 
		}
 		
	}); 
	
	
 	function fun_ajx(url){

 	    $.ajax({
	        type: 'post',
	        url: url,
	        success: function(data){
	        	$.get('/qywxmsg/send');
	        },
	        error: function(xhr, type){}
	        }); 
	} 
	
	//点赞列表
	$("#likes_list").on('click',function(e){
		var likes = $('#likes_list').data('likes');
		if(likes>4){
			   var url = "/frontend/thread/likeslist?tid="+tid;
			   window.location.href=url; 
		}
		return false;
	
	}); 
	
	
	

	function _show_form(e,tips,required) {
		var res = $(e).attr('rel');
		$('#frmpost').attr('action',res);
		$('#message').attr('placeholder',tips);
		$(".ui-dialog").dialog("show");
		$('#message').data('required', required);
		$('#message').val('');
		$('#message').focus();
	}
	
	
	//评论
	$('#reply').bind('click',function(){
		_show_form(this, '请填写评论内容',0);
	});
	
	//取消
	$('#message_cancel').bind('click',function(){
		$(".ui-dialog").dialog("hide");
	});
	
	//确定
	$('#message_sure').bind('click',function(){ 
		var message = $('#message').val();
		var required = $('#message').data('required');
		if (message == '' && required > 0) {
			$.tips({ content:'请填写评论内容!',stayTime:2000,type:"warn"});
			return false;
		}
		$(".ui-dialog").dialog("hide");
	});

	$('#plist').on('click','.post_reply',function(e){
		var pid = $(this).data('pid');
		var p_uid = $(this).data('uid');
		var p_username = $(this).data('username');
		_show_reply(pid,p_uid,p_username, '请填写评论内容',0);
	});

	
	function _show_reply(pid,p_uid,p_username,tips,required) {
		var url = '/frontend/thread/reply?handlekey=post&tid='+tid+"&pid="+pid+"&p_uid="+p_uid+"&p_username="+p_username;
		$('#frmpost').attr('action',url);
		$('#message').attr('placeholder',tips);
		$(".ui-dialog").dialog("show");
		$('#message').data('required', required);
		$('#message').val('');
		$('#message').focus();
	}
 	
     var sbt = new submit();//评论提交
	sbt.init({"form": $("#frmpost")});   
	
});



{/literal}
</script>


{include file='mobile/footer.tpl'}