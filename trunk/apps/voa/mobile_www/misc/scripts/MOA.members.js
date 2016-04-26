var MMember = {
    getBoxItemHTML: function(a, b) {
        return "undefined" == typeof b && (b = !0), [ '<li id="' + a.uid + '">', b ? '<a class="rm" href="javascript:void(0)">删除</a>' : "", '<img src="' + a.face + '" />', a.name, "</li>" ].join("");
    },
    getPanelItemHTML: function(a, b) {
        var c = b.indexOf(a.uid.toString()) > -1 ? " selected " : "", d = b[0] == a.uid.toString() ? " first " : "", e = encodeURI(JSON.stringify(a));
        return [ '<li class="' + c + d + '" id="' + a.uid + '" data-itemdata="' + e + '">', '<img src="' + a.face + '" />', a.name, "<em>" + a.job + "</em>", "</li>" ].join("").replace(/class\=\"\s*\"/, "");
    },
    fillPanelData: function(a, b, c) {
        $each(b, function(b) {
            var d = "<div><h1>" + b.type + "</h1><ul hidden>";
            $each(b.list, function(a) {
                d += MMember.getPanelItemHTML(a, c);
            }), d += "</ul></div>", $append(a, d);
        });
    },
    enablePanel: function(a, b, c, d) {
        "undefined" == typeof b && (b = !1), "undefined" == typeof d && (d = !1);
        var e = $all("div", a), f = $all("li", a);
        MOA.utils.pageToTop();
        var g = function() {
            var b = [];
            return $each($all("li", a), function(a) {
                $hasCls(a, "selected") && b.push(JSON.parse(decodeURI($data(a, "itemdata"))));
            }), b;
        }, h = function() {}, i = function() {
            var b = $one("li.first", a);
            if (b) {
                var c = b.parentNode;
                b.parentNode.removeChild(b), $all("li", c).length || $hide(c.parentNode), b = null, 
                c = null;
            }
        };
        if (b) {
            $addCls(a, "multi"), d && i(), $append(a, [ "<footer>", '<a href="javascript:void(0)" class="mod_button2 cancel">取消</a>', '<a href="javascript:void(0)" class="mod_button1 ok">确定</a>', "</footer>" ].join(""));
            var j = $one("footer", a), k = $one(".cancel", j), l = $one(".ok", j), m = g();
            k.addEventListener("touchstart", function(a) {
                c.call(null, m), h(), a.preventDefault();
            }), l.addEventListener("touchstart", function(a) {
                c.call(null, g()), h(), a.preventDefault();
            });
        } else {
            $rmCls(a, "multi"), i(), $append(a, [ "<footer>", '<a href="javascript:void(0)" class="mod_button2 cancel">取消</a>', "</footer>" ].join(""));
            var j = $one("footer", a), k = $one(".cancel", j), m = g();
            k.addEventListener("touchstart", function(a) {
                c.call(null, m), h(), a.preventDefault();
            });
        }
        $each(e, function(a) {
            b && ($append($one("h1", a), '<a href="javascript:void(0)" class="groupsel"></a>'), 
            $all("li.selected", a).length == $all("li", a).length && $addCls($one("h1", a), "selected"), 
            $one("h1>a.groupsel", a).addEventListener("touchend", function(a) {
                a.stopPropagation(), a.preventDefault();
                var b = a.currentTarget.parentNode, c = b.parentNode, d = $hasCls(b, "selected");
                $each($all("li", c), function(a) {
                    d ? $rmCls(a, "selected") : $addCls(a, "selected");
                }), d ? $rmCls(b, "selected") : $addCls(b, "selected");
            })), $one("h1", a).addEventListener("touchend", function(b) {
                return b.preventDefault(), b.stopPropagation(), $hasCls(a, "opened") ? ($rmCls(a, "opened"), 
                void $hide($one("ul", a))) : ($each(e, function(a) {
                    $rmCls(a, "opened"), $hide($one("ul", a));
                }), $addCls(a, "opened"), void $show($one("ul", a)));
            });
        });
        var n = b ? function(a) {
            a.preventDefault(), a.stopPropagation();
            var b = a.currentTarget.parentNode, c = b.parentNode.parentNode;
            $hasCls(b, "selected") ? $rmCls(b, "selected") : $addCls(b, "selected"), $all("li.selected", c).length == $all("li", c).length ? $addCls($one("h1", c), "selected") : $rmCls($one("h1", c), "selected"), 
            c = null, b = null;
        } : function(a) {
            a.preventDefault(), a.stopPropagation(), h();
            var b = a.currentTarget.parentNode;
            $addCls(b, "selected");
            var d = JSON.parse(decodeURI($data(b, "itemdata")));
            c.call(null, [ d ]), b = null;
        };
        $each(f, function(a) {
            $append(a, '<a href="javascript:void(0)" class="tri"></a>'), $one(".tri", a).addEventListener("touchend", n);
        });
    },
    unique: function(a, b) {
        $each($all("li.newjoin", a), function(a) {
            b.toString() === a.id && a.parentNode.removeChild(a);
        });
    },
    close: function(a) {
        var b = $one("#viewstack"), c = $one("#viewstack>section"), d = $one("#viewstack>menu.mod_members_panel");
        setTimeout(function() {
            $rmCls(b, "view2"), $rmCls(c, "view2"), MOA.event.onTweened(b, function() {
                d.innerHTML = "";
            }), "undefined" != typeof a && a.call(null);
        }, 500);
    },
    open: function(a, b, c, d, e, f) {
        "undefined" == typeof f && (f = !1);
        var g = $one("#viewstack"), h = $one("#viewstack>section"), i = $one("#viewstack>menu.mod_members_panel");
        "undefined" != typeof d && d.call(null), MMember.fillPanelData(i, a, b), $addCls(g, "view2"), 
        MOA.event.onTweened(g, function() {
            $addCls(h, "view2"), MMember.enablePanel(i, c, function(a) {
                "undefined" != typeof e && e.call(null, a);
            }, f);
        });
    }
};

$onload(function() {
    var a = $one("#viewstack");
    if (a) {
        if (!$hasCls(a.parentNode, "center")) {
            var b = window.innerWidth;
            MOA.utils.writeCSS([ "#viewstack{width:" + (b + 1) + "px;}", "#viewstack>section{width:" + b + "px;}", "#viewstack>menu{left:" + b + "px;}", "#viewstack.view2>menu{-webkit-transform:translate3d(-" + b + "px, 0, 0);}", "#viewstack.view2>menu{width:" + b + "px;}", ".mod_members_panel{width:" + b + "px;}" ].join("")), 
            console.log("[MOA.members] alter the width property #viewstack to ", b);
        }
        var c = function() {
            var b = window.innerHeight, d = $one("#viewstack>section");
            b = d ? Math.max(b, d.clientHeight) : b, a.style.height = b + "px", d = null, MOA.utils.setTimeout(c);
        };
        c();
    }
});