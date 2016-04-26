{include file='mobile/header.tpl'}

<div class="ui-bbs-my">
	{if $ac == 'mine'}
	<ul class="ui-list ui-list-top ui-border-b">
		<li class="">
			<div class="ui-avatar-one">
				<img src="{$cinstance->avatar($uid)}" height="60" width="60" />
			</div>
			<div class="ui-list-info">
				<h4 class="ui-nowrap">{$username}</h4>
			</div>
			<div class="ui-list-action ui-label-box">
				<span class="ui-label ui-label-default">话题&nbsp;{$total}</span>
			</div>
		</li>
	</ul>
	{/if}
	<div id="plist"></div>


	{literal}
	<script id="plist_tpl" type="text/template">
<%if (_.isEmpty(data)) {
$('#plist').removeClass();
%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无话题</p>
</section>
<%} else {%>
<%_.each(data, function(dr, index) {
%>
<ul class="ui-list ui-border-tb status-list ul_href" data-tid="<%=dr.tid%>">
       <li>
          <div class="ui-avatar-s">
               <img src="<%=dr.face%>" />
          </div>
          <div class="ui-list-info">
               <h4><%=dr.username%></h4>
               <p class="ui-nowrap"><%=dr._created%></p>
          </div>
          <div class="ui-list-action ui-label-box">
               <% if(dr.good){%>
                   <span class="ui-label ui-label-hot">热门</span>
               <%}%>
               <% if(dr.choice){%>
                   <span class="ui-label ui-label-top">精选</span>
               <%}%>
         </div>
       </li>

       <li class="ui-border-b ui-padding-top-0"><%=dr._subject%></li>
       <li><%=dr.message%></li>
  
       <li class="upload clearfix ui-border-b  ui-padding-top-0">
          <div>
            <% if(!_.isEmpty(dr.attachs)){%>
                  <%_.each(dr.attachs, function(at) {%>
                     <div class="ui-badge-wrap"><img src="/attachment/read/<%=at.aid%>" /></div>
                  <%});%>
            <%}%>
          </div>
       </li>

       <li>
          <div class="ui-follow-box" >
              <span data-tid="<%=dr.tid%>" data-ac="<%=dr.ac%>" <% if(_.isEmpty(dr.islike)){%> data-flag="yes" <%}else{%> data-flag="no" <%}%>   data-likes="<%=dr.likes%>"> 
                 <% if(_.isEmpty(dr.islike)){%>
                    <i class="ui-icon ui-icon-follow"  data-likes="<%=dr.likes%>" data-flag="yes"></i>
                    <% if (dr.likes >0){%>
                         <%=dr.likes%>
                    <%}else {%>                                           
                                                                 赞
                    <%}%>
                 <% }else {%>
                     <i class="ui-icon ui-icon-follow-ok"  data-flag="no" data-likes="<%=dr.likes%>"></i>
                     <% if (dr.likes >0){%>
                          <%=dr.likes%>
                     <%} %>
                 <%}%>
              </span>
              <span class="post_reply" data-tid="<%=dr.tid%>">
                   <i class="ui-icon ui-icon-reply" ></i>
                        <% if (dr.replies >0){%>
                            <%=dr.replies%>
                        <%} else {%>
                                                                     评论
                        <%}%>
              </span>
            </div>
         </li>
   </ul>

<%});}%>
</script>
	{/literal}


</div>


<script>
var ac = '{$ac}';
var listurl = "/api/thread/get/index?ac={$ac}";
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
	
	//点赞
	/**$('#plist').on('click', '.post_likes', function(e) {
		
	  
 		window.location.href=url; 
	}); */
	
	//评论
	$('#plist').on('click', '.post_reply', function(e) {
		 var url = "/frontend/thread/viewthread/?&tid="+$(this).data('tid');
	 	 window.location.href=url; 
	}); 
	
	//话题详情
	$('#plist').on('click', '.ul_href', function(e) {
		var el = $(e.target);
		if (!_.isUndefined(el.data('likes'))) {
			var url = "/frontend/thread/likes?tid="+$(this).data('tid');
			var likes = el.data('likes') + 1;
			if(e.target.tagName == 'SPAN'){
				if(el.data('flag') == 'yes'){
					fun_ajx(url);
					el.html('<i class="ui-icon ui-icon-follow-ok" data-flag="no" data-likes="' + likes + '"></i>' + likes);
					el.data('flag', 'no'); 
				}
			
			}else{
				if(el.data('flag') == 'yes'){
					fun_ajx(url);
				    el.parent().html('<i class="ui-icon ui-icon-follow-ok" data-flag="no" data-likes="' + likes + '"></i>' + likes);
				}
			}
			return false;
		}else{
			if(e.target.tagName == 'DIV'){
				return false;
			}
 			 var url = "/frontend/thread/viewthread/?&tid="+$(this).data('tid');
		 	 window.location.href=url;  
		}
		
	}); 
	
	
 	function fun_ajx(url){

 	    $.ajax({
	        type: 'post',
	        url: url,
	        success: function(data){
	        	$.get('/qywxmsg/send');
	        },
	        error: function(xhr, type){
	        }
	        }); 
	} 
	
});

{/literal}
</script>


{include file='mobile/footer.tpl'}
