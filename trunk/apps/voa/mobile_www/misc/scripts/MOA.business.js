function fillCalendarTable(a, b, c) {
    var d = MOA.utils.getDatesOfMonth(b, c);
    for (a.innerHTML = "<tr><th>日</th><th>一</th><th>二</th><th>三</th><th>四</th><th>五</th><th>六</th></tr>"; d.length; ) {
        for (var e = d.shift(), f = "<tr>"; e.length; ) {
            var g = e.shift();
            if (null == g) f += "<td></td>"; else {
                var h = MOA.utils.getFixedIOSDate(g), i = [ h.getFullYear(), h.getMonth() + 1, h.getDate() ].join("-");
                f += '<td id="day-' + i + '">' + h.getDate() + "</td>";
            }
        }
        f += "</tr>", $append(a, f);
    }
}

function parseIOS6styleTimeChooser(a, b) {
    var c = a, d = $prev(c), e = (new Date(), b.startDay), f = function() {
        var a = new Date(e.getTime());
        return a.setHours(0), a.setMinutes(0), a.setSeconds(0), a.setTime(a.getTime() + 24 * b.rangeDays * 60 * 60 * 1e3), 
        a;
    }(), g = MOA.utils.ensureNumberStringLength, h = function() {
        var a = null;
        d.value.length ? (a = MOA.utils.getFixedIOSDate(d.value, true), a.getTime() + 6e4 < e.getTime() ? (a = e, 
        d.value = a.toISOString(), d.blur(), "_hideDTPanel" in window && _hideDTPanel(), 
        alert(b.noticeMin)) : a.getTime() > f.getTime() && (a = f, d.value = a.toISOString(), 
        d.blur(), "_hideDTPanel" in window && _hideDTPanel(), alert(b.noticeMax))) : a = new Date(e.getTime()), 
        $data(d, "selected", 1), $rmCls(c, "ph"), c.innerHTML = [ a.getMonth() + 1, "月", a.getDate(), "日", " ", g(a.getHours()), ":", g(a.getMinutes()) ].join(""), 
        "callback" in b && b.callback.call(null, a);
    };
    c.addEventListener("click", function() {
        var a = d.value ? MOA.utils.getFixedIOSDate(d.value, true) : e;
        _showDTPanel(e, a, b.rangeDays, function(a) {
            d.value = a.toISOString(), h();
        });
    }), d.value && h();
}

function isLeapYear() {
    var a = new Date().getFullYear();
    return a % 4 == 0 && a % 100 != 0 || a % 400 == 0 ? 1 : 0;
}

function parseHiddenSelect(a, b) {
    var c = "string" == typeof a ? $one(a) : a, d = $prev(c);
    MOA.event.listenSelectChange(c, function() {
        $rmCls(d, "init");
        var a = c.options[c.selectedIndex].innerHTML;
        d.innerHTML = a, b && b.call(null, c, d, a);
    });
}

function parseStartEndDateSelects(a) {
    function b(a, b) {
        var c = b.match(/^(\d{4}\-\d{2}\-\d{2})/)[1];
        a[c] = a.length++;
    }
    function c(a) {
        return 10 > a && (a = "0" + a.toString()), a;
    }
    function d(a, b) {
        return [ a.getFullYear(), c(a.getMonth() + 1), c(a.getDate()) ].join(b || "/");
    }
    function e(a, b) {
        var c = d(b);
        $append(a, '<option value="' + c + '">' + c + "&nbsp;</option>");
    }
    var f, g = $one(a), h = $one(".begin select", g), i = $one(".end select", g), j = $prev(h), k = $prev(i), l = $data(g, "rangeStartTailstr"), m = $data(g, "rangeEndTailstr"), n = $data(g, "rangeStart"), o = parseInt($data(g, "rangeLength")), p = parseInt($data(g, "rangeStep")), q = {
        length: 0
    }, r = {
        length: 0
    }, s = [], t = MOA.utils.string2Date(n);
    for (t.setDate(t.getDate() - 1), f = o + p; f--; ) t.setDate(t.getDate() + 1), s.push(t.toISOString());
    for (var u = s.slice(0, o), v = s.slice(p), w = 0, x = o; x > w; w++) e(h, MOA.utils.getFixedIOSDate(u[w], true)), 
    e(i, MOA.utils.getFixedIOSDate(v[w], true)), b(q, u[w]), b(r, v[w]);
    var y = function() {
        "_chkSelsTo" in window && clearInterval(window._chkSelsTo), window._chkSelsTo = setTimeout(function() {
            h.selectedIndex > i.selectedIndex && (i.selectedIndex = h.selectedIndex), $rmCls(j, "init"), 
            $rmCls(k, "init"), j.innerHTML = h.options[h.selectedIndex].innerHTML + " " + l, 
            k.innerHTML = i.options[i.selectedIndex].innerHTML + " " + m, clearInterval(window._chkSelsTo), 
            delete window._chkSelsTo;
        }, 500);
    };
    MOA.event.listenSelectChange(h, y), MOA.event.listenSelectChange(i, y);
    var z = $data(g, "startSelected"), A = $data(g, "endSelected");
    if ("undefined" != typeof z || "undefined" != typeof A) {
        var B = 0, C = 0;
        /^\d*$/.test(z) ? (B = parseInt(z), C = parseInt(A), isNaN(B) && (B = 0), isNaN(C) && (C = 0)) : (B = q[z], 
        C = r[A], B || (B = 0), C || (C = 0)), h.selectedIndex = B, i.selectedIndex = C, 
        y();
    }
}

function updateMembersHiddenIpts(a) {
    "undefined" == typeof a && (a = $one(".mod_members"));
    var b = $prev(a), c = $all("li", a), d = [];
    $each(c, function(a) {
        $trim(a.id).length && d.push(a.id);
    }), b.value = d.join(","), b = null, c = null;
}

function onAddMember(a, b, c, d, e, ff) {
    if (!window._ajaxLock) {
        window._ajaxLock = !0;
        var f = a.currentTarget.parentNode.parentNode.parentNode, g = $prev(f), h = $one("ul", f), i = $one("li:last-of-type", h), j = $trim(g.value).length ? g.value.split(",") : [];
        MLoading.show("稍等片刻...");
        var k = new b(ff);
        return k.patch(c, function() {
            this.render(j).open(), MLoading.hide(), window._ajaxLock = !1;
        }), k.onSelected = function(a) {
        	if (b.TYPE_ADDRESSBOOK_SINGLESELECT == ff) {
        		$each($all('li', h), function(li) {
            		li.parentNode.removeChild(li);
            	});
        	}
        	a instanceof Array || (a = [ a ]);
        	for (var c = 0; c < a.length; c++) {
        		var g = a[c];
        		$append(h, b.getBoxItemHTML(g, !0));
        		var j = $one("li:last-of-type", h);
        		$addCls(j, "newjoin"), $one("a.rm", j).addEventListener("click", function(a) {
        			onRemoveMember(a, d);
        		}), h.appendChild(i);
        	}
        	updateMembersHiddenIpts(f), k.close(e);
        }, k;
    }
}

function onAddMemberToAndCc(a, b, c, d, ee) {
    var e = a.currentTarget.parentNode.parentNode.parentNode, f = $one(".mod_members.to"), g = $one(".mod_members.cc");
    $hasCls(e, "to") || (f = $one(".mod_members.cc"), g = $one(".mod_members.to"));
    var h = onAddMember(a, b, c, d, function() {
        if ($all(".mod_members.to li").length && $all(".mod_members.cc li").length) {
            for (var a = $prev(f).value.split(","), b = 0; b < a.length; b++) h.unique(g, a[b]);
            updateMembersHiddenIpts(g);
        }
    }, ee);
}

function onRemoveMember(a, b, c) {
    if (!window._ajaxLock) {
        window._ajaxLock = !0, "undefined" == typeof c && (c = "POST");
        /**var d = a.currentTarget.parentNode.id;
        MLoading.show("稍等片刻..."), $ajax(b, c, {
            uid: d
        }, MOA.event.delegate(function(a, b) {
            if (111 == a.success) {
                var c = a.uid;
                $each($all(".mod_members ul li.newjoin"), function(a) {
                    a.id == c && a.parentNode.removeChild(a);
                }), updateMembersHiddenIpts(b), window._ajaxLock = !1;
            }
            MLoading.hide();
        }, null, [ a.currentTarget.parentNode.parentNode.parentNode ]), !0);*/
        var li = a.currentTarget.parentNode;
        var ul = li.parentNode;
        ul.removeChild(li);
        updateMembersHiddenIpts(ul.parentNode);
        window._ajaxLock = !1;
    }
}

function configPhotoUpload(a) {
    var b;
    $onload(function() {
        b = new MOA.file.HTML5MUP({
            url: a.upload.url || "samplePictureUpload.php",
            container_id: "mpuAdd",
            multiple: !1,
            autostart: !0,
            max_filesize: 10 * 1024 * 1024,
            max_filecount: 1,
            base64_quality: .5,
            _is_debug_env: !0,
            callback_uploadstart: "H5upJS.uploadStart",
            callback_queueloaded: "H5upJS.queueLoaded",
            callback_listchange: "H5upJS.listChange",
            callback_progress: "H5upJS.setProgress",
            callback_error: "H5upJS.parseErr",
            callback_success: "H5upJS.parseSucc",
            callback_notice: "H5upJS.notice",
            callback_reactive: "H5upJS.reactive",
            i18n_fr_filter: "图片文件(jpg,jpeg,gif,png)",
            i18n_up_upload_ok: "上传成功",
            i18n_up_ioerror: "传输失败",
            i18n_err_type: "格式不符",
            i18n_err_size_pre: "大小超过"
        });
    }), window.H5upJS = {
        listChange: function(a) {
            console.log("---up list has changed", a);
        },
        setProgress: function(a, b, c) {
            console.log("--progress", a.name, b, c);
            var d = $one(".mod_photo_uploader a.photo:first-of-type"), e = $one("span", d);
            e.style.width = parseInt(b * d.clientWidth) + "px", d = null, e = null;
        },
        parseErr: function(a, c, d, e) {
            console.log("---error", a.toString(), c, d, e), setTimeout(function() {
                var a = $one(".mod_photo_uploader a.photo:first-of-type");
                a && a.parentNode.removeChild(a), $show($one(".mod_photo_uploader .add"));
            }, 200), MDialog.alert(d, null, null, "确定"), b.enable(), $show($one(".mod_photo_uploader .add"));
        },
        parseSucc: function(a, c, d) {
            console.log("---success", a, c, d);
            var e = $one(".mod_photo_uploader"), f = parseInt($data(e, "numFinished"));
            isNaN(f) && (f = 0), $data(e, "numFinished", f + 1), b.enable(), $show($one(".mod_photo_uploader .add"));
        },
        notice: function(a, b) {
            console.log(b, a);
        },
        reactive: function() {},
        uploadStart: function() {
            console.log("---uploadStart"), $prepend($one(".mod_photo_uploader"), '<a href="javascript:void(0)" class="photo"><span>xxx</span></a>'), 
            b.enable(!1), $hide($one(".mod_photo_uploader .add"));
        },
        queueLoaded: function(b) {
            var c = $one(".mod_photo_uploader"), d = parseInt($data(c, "numFinished"));
            if (isNaN(d) && (d = 0), d >= a.upload.nummax) {
                var e = $one(".add", c);
                $hide(e);
            }
            console.log("已经成功上传了：%d张", d);
            var f = b[0];
            if (!(f.resultCode > 0)) {
                console.log(f);
                var g = $one("input[type=hidden]", c), h = function() {
                    var a = Array.prototype.map.call($all(".mod_photo_uploader a.photo"), function(a) {
                        var b = JSON.parse($data(a, "result"));
                        return b.id;
                    }).join(",");
                    g.value = a;
                }, i = $one("a.photo:first-of-type", c);
                $data(i, "result", JSON.stringify(f));
                try {
                    $prepend(i, '<img src="' + f.data.thumb + '" id="upimg_' + f.id + '" />'), h();
                } catch (j) {}
                try {
                    var k = $one("span", i);
                    k && k.parentNode.removeChild(k);
                } catch (j) {}
                $append(i, '<i class="rm">-</i>');
                var l = $one("i.rm", i);
                l.onclick = function(b) {
                    if (!window._ajaxLock) {
                        window._ajaxLock = !0;
                        var d = JSON.parse($data(b.currentTarget.parentNode, "result"));
                        MLoading.show("正在删除..."), $ajax(a.remove.url, a.remove.method || "post", {
                            id: d.id
                        }, MOA.event.delegate(function(a, b) {
                            if (window._ajaxLock = !1, MLoading.hide(), 0 == a.resultCode) {
                                b.parentNode.removeChild(b);
                                var d = parseInt($data(c, "numFinished"));
                                $data(c, "numFinished", d - 1), console.log("删除了1张，还有：%d张", d - 1), h(), $show($one(".mod_photo_uploader .add"));
                            } else MDialog.alert(a.resultCode + ", " + a.describe, null, null, "确定");
                        }, null, [ b.currentTarget.parentNode ]), !0), b.stopPropagation();
                    }
                }, showBigPicture($one("img", i), JSON.parse($data(i, "result")).data.photo);
            }
        }
    };
}

function parseFormEditMode(a, b, c) {
    var d = function(a) {
        return $trim(a.value);
    }, e = function(a) {
        var b = a.currentTarget;
        if (b.hasAttribute("readonly")) {
            var c = $prev(b);
            b.removeAttribute("readonly"), $addCls(c, "show"), b.focus();
            var e = d(b);
            try {
                b.setSelectionRange(e.length, e.length);
            } catch (f) {}
            $data(b, "oldvalue", e);
        }
    }, f = function(a) {
        var e = a.currentTarget, f = $next(e);
        if (!f.hasAttribute("readonly")) {
            var g = e.parentNode.classList, h = g[g.length - 1], i = $one("label", e.parentNode).innerHTML.replace("：", "").replace(":", ""), j = f.hasAttribute("required"), k = f.hasAttribute("pattern") ? new RegExp(f.pattern) : null, l = d(f);
            if (j && "" === l) return void alert(b.requiredNotice.replace("{0}", i));
            if (k && !k.test(l)) return void alert(b.patternNotice.replace("{0}", i));
            l !== $data(f, "oldvalue") && c(h, l), f.setAttribute("readonly", !0), $rmCls(e, "show");
        }
    }, g = MOA.query.concat($all("input[type=text]", a), $all("input[type=number]", a), $all("input[type=tel]", a), $all("input[type=email]", a), $all("textarea", a));
    $each(g, function(a) {
        a.addEventListener("click", e), $prev(a).addEventListener("click", f);
    }), $each($all("select", a), function(a) {
        a.addEventListener("change", function(a) {
            var b = a.currentTarget, d = b.parentNode.classList, e = d[d.length - 1];
            c(e, b.options[b.selectedIndex].value);
        });
    });
}

$onload(function() {
    $each(".mod_steps_slider", function(a) {
        var b = 3, c = MOA.translate, d = ($one(".sinner", a), $one("ul", a)), e = $all("li", d), f = e.length, g = f - 1, h = e[0].clientWidth, i = f, j = null, k = null, l = !1, m = function() {
            return parseInt($data(d, "currpage"));
        }, n = function(a) {
            $data(d, "currpage", a);
        }, o = function(a, b) {
            "undefined" == typeof b && (b = !0), d.style[c.vendor + "TransitionDuration"] = b ? ".618s" : 0;
            var e = -h * a, f = i % 3;
            f && (e -= -h * f), d.style[c.vendor + "Transform"] = c.open + e + "px, 0px" + c.close;
        }, p = function(a) {
            l || (l = !0, o(a), MOA.event.onTweened(d, function() {
                n(a), q(), l = !1;
            }));
        }, q = function() {
            $show(j, k), m() <= i % b && $hide(j), m() == i - 1 && $hide(k);
        };
        d.style.width = h * f + "px", b >= f || ($prepend(a, '<a href="javascript:void(0)" class="prev"><span></span></a>'), 
        $append(a, '<a href="javascript:void(0)" class="next"><span></span></a>'), j = $one("a.prev", a), 
        k = $one("a.next", a), j.addEventListener("click", function() {
            var a = m() - 1;
            i % b > a || p(a);
        }), k.addEventListener("click", function() {
            var a = m() + 1;
            a > i - 1 || p(a);
        }), o(g, !1), n(g), q());
    });
});
