<style>
div.city::after { content:"" !important; }
div.dist::after { content:"" !important; }
</style>
<fieldset>
	<div class="city" style="display:block;width:auto;margin-right:10px;">
		<label>{$plugin_set['title_city']}</label>
		<select name=""><option selected value="-1">请选择</option></select>
	</div>
	<div class="dist" style="display:block;width:auto;margin-right:10px;">
		<label>{$plugin_set['title_region']}</label>
		<select name=""><option value="-1">请选择</option></select>
	</div>
	<div class="shop">
		<label>店名</label>
		<select name="{$spname}" id="{$spname}"><option value="-1">请选择</option></select>
	</div>
</fieldset>
<textarea name="{$jsonname}" id="{$jsonname}" style="display:none;">{$region2shop}</textarea>
<script>
var _shops_data = eval('(' + $one('#{$jsonname}').value + ')');
{literal}
require(['dialog', 'business'], function() {
	//店名三级联动
	var nullOpt = '<option selected value="-1">请选择</option>';
	$each(_shops_data, function(city, cidx) {
		$append(
			$one('.city select'),
			'<option value="'+city.id+'">'+city.title+'</option>'
		);
	});
	MOA.event.listenSelectChange($one('.city select'), function(e) {
		var $distSel = $one('.dist select');
		var $shopSel = $one('.shop select');
		$distSel.innerHTML = nullOpt;
		$distSel.value = -1;
		$shopSel.innerHTML = nullOpt;
		$shopSel.value = -1;
		var cid = e.currentTarget.value;
		if (cid === -1) return;
		var cdata = null;
		for (var i=0,lng=_shops_data.length; i<lng; i++) {
			if (_shops_data[i].id == cid){
				cdata = _shops_data[i];
				$data($distSel, 'cIdx', i);
				break;
			}
		}
		if (cdata === null) return;
		var dicts = cdata.districts;
		$each(dicts, function(d, didx) {
			$append(
				$distSel,
				'<option value="'+d.id+'">'+d.title+'</option>'
			);
		});
		MOA.event.listenSelectChange($distSel, onDistChg);
	});
	function onDistChg(e) {
		var $distSel = $one('.dist select');
		var $shopSel = $one('.shop select');
		$shopSel.innerHTML = nullOpt;
		$shopSel.value = -1;
		var cIdx = parseInt($data($distSel, 'cIdx'));
		var dicts = _shops_data[cIdx].districts;
		
		var did = e.currentTarget.value;
		if (did === -1) return;
		var ddata = null;
		for (var i=0,lng=dicts.length; i<lng; i++) {
			if (dicts[i].id == did){
				ddata = dicts[i];
				break;
			}
		}
		if (ddata === null) return;
		var shops = ddata.shops;
		$each(shops, function(s, sidx) {
			$append(
				$shopSel,
				'<option value="'+s.id+'">'+s.title+'</option>'
			);
		});
	}
});
{/literal}
</script>
