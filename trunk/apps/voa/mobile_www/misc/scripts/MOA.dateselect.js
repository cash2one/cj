/** 初始化时间选择 */
function init_date_select(b_ymd, e_ymd) {
	var tul = $one('ul.time'),
		bSel = $one('.begin select', tul),
		eSel = $one('.end select', tul),
		bFake = $prev(bSel),
		eFake = $prev(eSel),
		rt1 = $data(tul, 'rangeStartTailstr'),
		rt2 = $data(tul, 'rangeEndTailstr'),
		rstart = $data(tul, 'rangeStart'),
		rleng = parseInt($data(tul, 'rangeLength')),
		rstep = parseInt($data(tul, 'rangeStep'));
	
	function fixNum(n) {
		if (n<10) n='0'+n.toString();
		return n;
	}
	function fmtDate(d, div) {
		return [d.getFullYear(), fixNum(d.getMonth()+1), fixNum(d.getDate())].join(div||'/');
	}
	function fillSel(sel, day) {
		var dstr = fmtDate(day);
		$append(sel, '<option value="'+ dstr +'">'+ dstr +'&nbsp;</option>');
	}
	
	var checkAndRender = function(e) {
		if ('_chkSelsTo' in window) clearInterval(window._chkSelsTo);
		window._chkSelsTo = setTimeout(function() {
			if (bSel.selectedIndex > eSel.selectedIndex) {
				eSel.selectedIndex = bSel.selectedIndex;
			}
			
			$rmCls(bFake, 'init');
			$rmCls(eFake, 'init');
			
			bFake.innerHTML = bSel.options[bSel.selectedIndex].innerHTML + ' ' + rt1;
			eFake.innerHTML = eSel.options[eSel.selectedIndex].innerHTML + ' ' + rt2;
			
			clearInterval(window._chkSelsTo);
			delete window._chkSelsTo;
		}, 500);
	}
	
	var arr = [], flag;
	var day = MOA.utils.string2Date(rstart);
	day.setDate( day.getDate()-1 );
	flag = rleng + rstep;
	while(flag--) {
		day.setDate( day.getDate()+1 );
		arr.push(day.toISOString());
	}
	
	var bArr = arr.slice(0, rleng),
		eArr = arr.slice(rstep);
	
	for (var i=0, lng = rleng; i < lng; i++) {
		var iosd = MOA.utils.getFixedIOSDate(bArr[i]);
		fillSel(bSel, iosd);
		fillSel(eSel, iosd);
		if (b_ymd == fmtDate(iosd)) {
			bSel.selectedIndex = i;
			checkAndRender();
		}
		
		if (e_ymd == fmtDate(iosd)) {
			eSel.selectedIndex = i;
			checkAndRender();
		}
	}
	
	MOA.event.listenSelectChange(bSel, checkAndRender);
	MOA.event.listenSelectChange(eSel, checkAndRender);
}