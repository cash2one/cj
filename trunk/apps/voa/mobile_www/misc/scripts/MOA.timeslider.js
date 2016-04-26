function _showDTPanel(a, b, c, d) {
    var e, f = $one("#DTPanel"), g = {}, h = new Date(), i = function() {
        try {
            f.parentNode.removeChild(f), _enableSafariElastic();
        } catch (a) {}
    }, j = MOA.utils.ensureNumberStringLength, k = function(a) {
        return [ a.getMonth() + 1, "月", a.getDate(), "日", " <i>", [ "周日", "周一", "周二", "周三", "周四", "周五", "周六" ][a.getDay()], "</i>" ].join("");
    }, l = function(a) {
        return [ a.getFullYear(), a.getMonth() + 1, a.getDate() ].join("-");
    }, m = function() {
        var b = new Date(a.getTime()), d = c + 1, f = "", i = "<li></li>", j = [];
        for (j.push(i), j.push(i), b.setDate(b.getDate() - 1); d--; ) {
            b.setDate(b.getDate() + 1);
            var m = l(b), n = 'data-humanday="' + m + '" rel="' + b.getTime() + '"';
            g[m] = c - d, f = b.getMonth() === h.getMonth() && b.getDate() === h.getDate() ? "<li " + n + ' class="today">今天</li>' : "<li " + n + ">" + k(b) + "</li>", 
            j.push(f);
        }
        j.push(i), j.push(i), e = e.replace("{#day#}", "<ol>" + j.join("") + "</ol>");
    }, n = function() {
        var a, b, c = [], d = 0;
        for (a = 12, b = 24; b > a; a++) c.push('<li id="hour_li' + d++ + '" rel="' + a + '">' + a + "</li>");
        for (a = 0, b = 24; b > a; a++) c.push('<li id="hour_li' + d++ + '" rel="' + a + '">' + a + "</li>");
        for (a = 0, b = 12; b > a; a++) c.push('<li id="hour_li' + d++ + '" rel="' + a + '">' + a + "</li>");
        e = e.replace("{#hour#}", "<ol>" + c.join("") + "</ol>");
    }, o = function() {
        var a, b, c = [], d = 0;
        for (a = 45, b = 60; b > a; a++) {
            var f = j(a);
            c.push('<li id="minute_li' + d++ + '" rel="' + f + '">' + f + "</li>");
        }
        for (a = 0, b = 60; b > a; a++) {
            var f = j(a);
            c.push('<li id="minute_li' + d++ + '" rel="' + f + '">' + f + "</li>");
        }
        for (a = 0, b = 15; b > a; a++) {
            var f = j(a);
            c.push('<li id="minute_li' + d++ + '" rel="' + f + '">' + f + "</li>");
        }
        e = e.replace("{#minute#}", "<ol>" + c.join("") + "</ol>");
    }, p = null, q = null, r = null, s = function() {
        p = parseInt(t.getRange()[2].getAttribute("rel")), q = parseInt(u.getRange()[2].getAttribute("rel")), 
        r = parseInt(v.getRange()[2].getAttribute("rel"));
        var a = new Date(p);
        a.setHours(q), a.setMinutes(r), a.setSeconds(0), a.setMilliseconds(0), d.call(null, a);
    };
    f && i(), e = [ '<div class="footFix" id="DTPanel">', '<div class="hd"><a class="finishBtn">完成</a></div><div class="bd">', '<div class="win">', '<div class="wbk day"></div><div class="wline"></div><div class="wbk hour"></div>', '<div class="wline"></div><div class="wbk minute"></div><div class="wglass"></div>', '<div class="wmain">', '<div class="wbox day">{#day#}</div>', '<div class="wbox hour">{#hour#}</div>', '<div class="wbox minute">{#minute#}</div>', "</div></div></div></div>" ].join(""), 
    m(), n(), o(), document.body.insertAdjacentHTML("beforeEnd", e), f = $one("#DTPanel"), 
    $one(".finishBtn", f).addEventListener("click", i);
    var t = new MOA.ui.DatetimeVSlider({
        containerContext: "#DTPanel .wbox.day",
        innerContext: "ol",
        itemContext: "li",
        itemCount: 5,
        defaultIndex: function() {
            var a = l(b);
            return g[a];
        }(),
        startDay: a,
        callback: function() {
            s();
        }
    }), u = new MOA.ui.DatetimeVSlider({
        containerContext: "#DTPanel .wbox.hour",
        innerContext: "ol",
        itemContext: "li",
        itemCount: 5,
        defaultIndex: 10 + b.getHours(),
        startDay: a,
        callback: function(a) {
            var b = parseInt(a[2].id.replace("hour_li", ""));
            12 > b ? this.setCurr(b + 22, !1) : b > 35 && this.setCurr(b - 26, !1), s();
        }
    }), v = new MOA.ui.DatetimeVSlider({
        containerContext: "#DTPanel .wbox.minute",
        innerContext: "ol",
        itemContext: "li",
        itemCount: 5,
        defaultIndex: 13 + b.getMinutes(),
        startDay: a,
        callback: function(a) {
            var b = parseInt(a[2].id.replace("minute_li", ""));
            15 > b ? this.setCurr(b + 58, !1) : b > 74 && this.setCurr(b - 62, !1), s();
        }
    });
    _disableSafariElastic(), s(), window._hideDTPanel = i;
}

var MOA;

!function(a) {
    !function(b) {
        function c() {
            return new Date().getTime();
        }
        function d(a, b, c, d, f) {
            var g = d ? -c * b : c, h = d ? "undefined" != typeof f ? f : .2472 : 0;
            a.style[e.vendor + "TransitionDuration"] = h + "s", a.style[e.vendor + "Transform"] = e.open + "0," + g + "px" + e.close;
        }
        var e = a.translate, f = a.event.delegate, g = function() {
            function b(b) {
                var e = this;
                this.config = b, this.topIdx = 0, this._e_ts = function(b) {
                    a.env.touchSupport || b.preventDefault();
                    var d = e.container.getBoundingClientRect().top, f = e.inner.getBoundingClientRect().top;
                    e.dinfo_start = {
                        time: c(),
                        localY: b.touches[0].clientY - d,
                        stageY: b.touches[0].clientY,
                        innerTop: f - d
                    }, b.currentTarget.addEventListener("touchmove", e._ontouchmove), b.currentTarget.addEventListener("touchend", e._ontouchend), 
                    b.currentTarget.addEventListener("touchcancel", e._ontouchend);
                }, this._e_tm = function(b) {
                    a.env.touchSupport || b.preventDefault();
                    var c = b.touches[0].clientY - e.dinfo_start.stageY;
                    c += e.dinfo_start.innerTop, d(e.inner, e.itemH, c);
                    var f = b.currentTarget;
                    (void 0 === $data(f, "touching") || 1 * $data(f, "touching") != 1) && $data(f, "touching", 1);
                }, this._e_te = function(b) {
                    a.env.touchSupport || b.preventDefault(), b.currentTarget.removeEventListener("touchmove", e._ontouchmove), 
                    b.currentTarget.removeEventListener("touchend", e._ontouchend), b.currentTarget.removeEventListener("touchcancel", e._ontouchend);
                    var d = b.currentTarget;
                    $data(d, "touching", 0);
                    var f = e.container.getBoundingClientRect().top, g = e.inner.getBoundingClientRect().top;
                    e.dinfo_end = {
                        time: c(),
                        innerTop: g - f
                    };
                    var h = e.topIdx, i = e.dinfo_end.time - e.dinfo_start.time, j = e.dinfo_end.innerTop - e.dinfo_start.innerTop, k = Math.abs(j) < 5, l = i > 200;
                    if (l || k) {
                        if (Math.abs(j) > .5 * e.itemH) {
                            var m = Math.abs(Math.round(j / e.itemH));
                            0 > j ? h += m : h -= m;
                        }
                    } else 0 > j ? h += 5 : h -= 5;
                    0 > h && (h = 0), h >= e.childs.length - e.config.itemCount && (h = e.childs.length - e.config.itemCount), 
                    e.inner.addEventListener("webkitTransitionEnd", e._ontransitionend), e.setCurr(h);
                }, this._e_tre = function(a) {
                    a.currentTarget.removeEventListener("webkitTransitionEnd", e._ontransitionend), 
                    "callback" in e.config && e.config.callback && e.config.callback.call(e, e.getRange());
                }, this.container = $one(this.config.containerContext), this.inner = $one(this.config.innerContext, this.container), 
                this.w = this.container.clientHeight, this.childs = $all(this.config.itemContext, this.inner), 
                this.itemH = Math.round(this.w / this.config.itemCount), this.inner.style.height = this.itemH * this.childs.length + "px", 
                this.setCurr(this.config.defaultIndex || 0, !1), this._ontouchstart = f(this._e_ts, this), 
                this._ontouchmove = f(this._e_tm, this), this._ontouchend = f(this._e_te, this), 
                this._ontransitionend = f(this._e_tre, this), this.inner.addEventListener("touchstart", this._ontouchstart);
            }
            return b.prototype.setCurr = function(a, b) {
                "undefined" == typeof b && (b = !0), this.topIdx = a;
                var c = [ this.inner, this.itemH, a, !0 ];
                b || c.push(0), d.apply(null, c);
            }, b.prototype.getRange = function() {
                return Array.prototype.slice.call(this.childs, this.topIdx, this.topIdx + this.config.itemCount);
            }, b;
        }();
        b.DatetimeVSlider = g;
    }(a.ui || (a.ui = {}));
    a.ui;
}(MOA || (MOA = {}));