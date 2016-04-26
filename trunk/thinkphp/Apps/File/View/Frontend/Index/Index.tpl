<include file="Common@Frontend:Header"/>

<form name="add_gb" id="add_gb" action="{$acurl}" method="post" enctype="multipart/form-data">

	附件：<input type="file" name="data" id="data"/><br><br>
	文件夹id：<input type="text" name="folder_id" id="folder_id"/><br><br>

	<input type="submit" value="提交" name="gb_sbt" id="gb_sbt"/><br><br>
</form>



<include file="Common@Frontend:Footer"/>
