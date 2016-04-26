var console = window.console || {
    log: function() {}
}, MOA = function() {
    var a = function() {
        var a = navigator.userAgent, b = null, c = function(a, b) {
            var c = a.split(b);
            return c = c.shift() + "." + c.join(""), 1 * c;
        }, d = {
            ua: a,
            version: null,
            ios: !1,
            android: !1,
            windows: !1,
            blackberry: !1,
            meizu: !1,
            weixin: !1,
            wVersion: null,
            qq: !1,
            qVersion: null,
            touchSupport: "createTouch" in document,
            hashSupport: !!("onhashchange" in window)
        };
        if (b = a.match(/MicroMessenger\/([\.0-9]+)/), null != b && (d.weixin = !0, d.wVersion = c(b[1], ".")), 
        b = a.match(/QQ\/([\d\.]+)$/), null != b && (d.qq = !0, d.qVersion = c(b[1], ".")), 
        b = a.match(/Android(\s|\/)([\.0-9]+)/), null != b) return d.android = !0, d.version = c(b[2], "."), 
        d.meizu = /M030|M031|M032|MEIZU/.test(a), d;
        if (b = a.match(/i(Pod|Pad|Phone)\;?.*\sOS\s([\_0-9]+)/), null != b) return d.ios = !0, 
        d.version = c(b[2], "_"), d;
        if (b = a.match(/Windows\sPhone\sOS\s([\.0-9]+)/), null != b) return d.windows = !0, 
        d.version = c(b[1], "."), d;
        var e = {
            a: a.match(/\(BB1\d+\;\s.*\sVersion\/([\.0-9]+)\s/),
            b: a.match(/\(BlackBerry\;\s.*\sVersion\/([\.0-9]+)\s/),
            c: a.match(/^BlackBerry\d+\/([\.0-9]+)\s/),
            d: a.match(/\(PlayBook\;\s.*\sVersion\/([\.0-9]+)\s/)
        };
        for (var f in e) if (null != e[f]) return b = e[f], d.blackberry = !0, d.version = c(b[1], "."), 
        d;
        return d;
    }(), b = /webkit/i.test(navigator.appVersion) ? "webkit" : /firefox/i.test(navigator.userAgent) ? "Moz" : "opera" in window ? "O" : /MSIE/i.test(navigator.userAgent) ? "ms" : "", c = "WebKitCSSMatrix" in window && "m11" in new WebKitCSSMatrix(), d = "translate" + (c ? "3d(" : "("), e = c ? ",0)" : ")", f = (a.ios && !(!window.history || !window.history.pushState), 
    function(a, b, c, d, e) {
        "undefined" == typeof b && (b = "POST"), "undefined" == typeof c && (c = null), 
        "undefined" == typeof e && (e = !0), b = b.toLowerCase();
        var f = null, g = [];
        if (window.ActiveXObject) f = new ActiveXObject("Microsoft.XMLHTTP"); else {
            if (!window.XMLHttpRequest) return !1;
            f = new XMLHttpRequest();
        }
        if (f.onreadystatechange = function() {
            if (4 == f.readyState && (200 == f.status || 0 == f.status)) {
                var a = f.responseText, b = e ? JSON.parse(a) : a;
                d && d.call(null, b);
            }
        }, c) for (var h in c) g.push(h + "=" + c[h]);
        g = g.length ? g.join("&") : null, "get" == b && null != g && (a += a.indexOf("?") > -1 ? "&" : "?", 
        a += g, g = null), console && console.log && console.log("ajax: ", a, g);
        try {
            f.open(b, a, !0), "post" == b && f.setRequestHeader("content-type", "application/x-www-form-urlencoded"), 
            f.setRequestHeader("X-Requested-With", "XMLHttpRequest"), f.send(g);
        } catch (i) {
            throw "[ajax] request error";
        }
        return !0;
    }), g = function(a, b) {
        if (b && "string" == typeof b) try {
            b = g(b);
        } catch (c) {
            return void console.log(c);
        }
        return (b || document).querySelector(a);
    }, h = function(a, b) {
        if (b && "string" == typeof b) try {
            b = g(b);
        } catch (c) {
            return void console.log(c);
        }
        return (b || document).querySelectorAll(a);
    }, i = function() {
        for (var a = 0, b = arguments.length, c = []; b > a; a++) {
            var d = arguments[a];
            "string" == typeof d ? d = h(d) : "nodeType" in d && 1 === d.nodeType && (d = [ d ]), 
            A(d, function(a) {
                c.push(a);
            });
        }
        return c;
    }, j = function(a) {
        return "string" == typeof a && (a = g(a)), a.previousElementSibling;
    }, k = function(a) {
        return "string" == typeof a && (a = g(a)), a.nextElementSibling;
    }, l = function() {
        function a(a, b) {
            var c, d = encodeURIComponent, e = [], f = b ? b : "&";
            for (c in a) e.push(d(c) + "=" + d(a[c]));
            return e.join(f);
        }
        function b(a, b) {
            var c = a.indexOf(b);
            return -1 == c ? [ a, "" ] : [ a.substring(0, c), a.substring(c + 1) ];
        }
        var c = function(a, c, d) {
            var e = a || window.location.href, f = d || "&", g = b(e, c || "#"), h = (g[0], 
            g[1]);
            if (this.map = {}, this.sign = f, h) for (var i = h.split(f), j = 0; j < i.length; j++) {
                var k = i[j], l = b(k, "=");
                this.map[l[0]] = l[1];
            }
            this.size = function() {
                return this.keys().length;
            }, this.keys = function() {
                var a = [];
                for (var b in this.map) "_hashfoo_" != b && a.push(b);
                return a;
            }, this.values = function() {
                var a = [];
                for (var b in this.map) "_hashfoo_" != b && a.push(this.map[b]);
                return a;
            }, this.put("_hashfoo_", Math.random());
        };
        return c.prototype.get = function(a) {
            return this.map[a] || null;
        }, c.prototype.put = function(a, b) {
            this.map[a] = b;
        }, c.prototype.set = c.prototype.put, c.prototype.putAll = function(a) {
            if ("object" == typeof a) for (var b in a) this.map[b] = a[b];
        }, c.prototype.remove = function(a) {
            if (this.map[a]) {
                var b = {};
                for (var c in this.map) c != a && (b[c] = this.map[c]);
                this.map = b;
            }
        }, c.prototype.toString = function() {
            var b = {};
            for (var c in this.map) "_hashfoo_" != c && (b[c] = this.map[c]);
            return a(b, "&");
        }, c.prototype.clone = function() {
            return new c("foo#" + this.toString(), this.sign);
        }, c;
    }(), m = function() {
        function a(a) {
            var b = new RegExp("\\-([a-z])", "g");
            return b.test(a) ? a.toLowerCase().replace(b, RegExp.$1.toUpperCase()) : a;
        }
        function b(a) {
            return a.replace(/([A-Z])/g, "-$1").toLowerCase();
        }
        function c(a, c, d) {
            a.setAttribute("data-" + b(c), d);
        }
        function d(a, c) {
            var d = a.getAttribute("data-" + b(c));
            return d || void 0;
        }
        return function(b, e, f) {
            if (arguments.length > 2) try {
                b.dataset[a(e)] = f;
            } catch (g) {
                c(b, e, f);
            } else try {
                return b.dataset[a(e)];
            } catch (g) {
                return d(b, e);
            }
        };
    }(), n = window.applicationCache, o = function() {
        var a = !!n;
        a && n.addEventListener("updateready", function() {
            if (n.status == n.UPDATEREADY) {
                try {
                    n.swapCache();
                } catch (a) {}
                location.href = location.origin + location.pathname + "?rnd=" + Math.random() + location.hash;
            }
        }, !1);
    }, p = function(a) {
        var b = document.createElement("style");
        b.innerHTML = a;
        try {
            g("head").appendChild(b);
        } catch (c) {}
    }, q = function() {
        return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || function(a) {
            return window.setTimeout(a, 1e3 / 60);
        };
    }(), r = function() {
        return window.cancelAnimationFrame || window.webkitCancelRequestAnimationFrame || window.mozCancelRequestAnimationFrame || window.oCancelRequestAnimationFrame || window.msCancelRequestAnimationFrame || clearTimeout;
    }(), s = function(b) {
        if (g(".footFix")) {
            "undefined" == typeof b && (b = !0);
            var c = "_needFixedStyle" in window || a.ios && a.version < 4.5 || a.android && a.version < 3 || a.meizu || a.blackberry && a.version < 7 || !u();
            if (c) {
                var d;
                b ? d = t() : r(d);
            }
        }
    }, t = function() {
        return A(h(".footFix"), function(a) {
            var b, c = a, d = window.innerHeight, e = window.scrollY, f = m(c, "ffixTop"), g = m(c, "ffixBottom");
            if (c) try {
                b = c.clientHeight, c.style.position = "absolute", c.style.top = f ? e + 1 * f + "px" : g ? e + d - 1 * g - b + "px" : e + d - b + "px", 
                c.style.bottom = "auto";
            } catch (h) {}
        }), q.call(window, t);
    }, u = function() {
        var a = document.createElement("div"), b = document.createElement("div"), c = !0;
        return a.style.position = "absolute", a.style.top = "200px", b.style.position = "fixed", 
        b.style.top = "100px", a.appendChild(b), document.body.appendChild(a), b.getBoundingClientRect && b.getBoundingClientRect().top == a.getBoundingClientRect().top && (c = !1), 
        document.body.removeChild(a), c;
    }, v = function(a) {
        return a.replace(/(^\s+|\s+$)/g, "");
    }, w = function(a, b) {
        return new RegExp("(^|\\s)+(" + b + ")(\\s|$)+", "g").test(a.className);
    }, x = function(a, b) {
        if ("string" == typeof a) try {
            a = g(a);
        } catch (c) {
            return void console.log(c);
        }
        var d = new RegExp("(^|\\s)+(" + b + ")(\\s|$)+", "g");
        try {
            a.className = a.className.replace(d, "$1$3");
        } catch (c) {}
        d = null;
    }, y = function(a, b) {
        if ("string" == typeof a) try {
            a = g(a);
        } catch (c) {
            return void console.log(c);
        }
        x(a, b), a.className = v(a.className + " " + b);
    }, z = function(a, b) {
        if (a && b) {
            var c = "";
            try {
                c = "undefined" == typeof window.getComputedStyle ? a.currentStyle[b] : window.getComputedStyle(a, null)[b];
            } catch (d) {
                c = a.style[b];
            }
            return c.replace(/px$/, "");
        }
    }, A = function(a, b) {
        if ("string" == typeof a) try {
            a = h(a);
        } catch (c) {
            return void console.log(c);
        }
        Array.prototype.forEach.call(a, b);
    }, B = function() {
        for (var a, b = 0, c = arguments.length; c > b; b++) {
            if (a = arguments[b], "string" == typeof a) try {
                a = g(a);
            } catch (d) {
                return void console.log(d);
            }
            if (void 0 != a.nodeType && 1 == a.nodeType) a.style.display = "", a.removeAttribute("hidden"); else if (a.hasOwnProperty("length")) try {
                var e = [];
                A(a, function(a) {
                    e.push(a);
                }), B.apply(null, e);
            } catch (d) {}
        }
    }, C = function() {
        for (var a, b = 0, c = arguments.length; c > b; b++) {
            if (a = arguments[b], "string" == typeof a) try {
                a = g(a);
            } catch (d) {
                return void console.log(d);
            }
            if (a && void 0 != a.nodeType && 1 == a.nodeType) a.style.display = "none"; else if (a && a.hasOwnProperty("length")) try {
                var e = [];
                A(a, function(a) {
                    e.push(a);
                }), C.apply(null, e);
            } catch (d) {}
        }
    }, D = function(a, b) {
        a.insertAdjacentHTML("beforeEnd", b);
    }, E = function(a, b) {
        a.insertAdjacentHTML("afterBegin", b);
    }, F = function() {
        var a = arguments[0], b = arguments[1], c = Array.prototype.slice.call(arguments, 2);
        return window.setTimeout(function(b) {
            return function() {
                a.apply(null, b);
            };
        }(c), b);
    }, G = function() {
        var a = arguments[0], b = arguments[1], c = Array.prototype.slice.call(arguments, 2);
        return window.setInterval(function(b) {
            return function() {
                a.apply(null, b);
            };
        }(c), b);
    }, H = function() {
        g("body").scrollTop = -1, window.scrollTo(0, -1);
    }, I = function(a, b) {
        return "undefined" == typeof b && (b = 2), a = !isFinite(a) || isNaN(a) ? "" : a.toString(), 
        a.length < b ? "000000000000000000000".substr(0, b - a.length) + a : a;
    }, J = function(a, b) {
        var c = [ [] ], d = function(a, b) {
            "undefined" == typeof b && (b = !1), c[c.length - 1].push(null == a ? null : a.toISOString()), 
            b || 7 != c[c.length - 1].length || (c[c.length] = []);
        }, e = new Date("2000-00-00");
        e.setFullYear(a), e.setMonth(b), e.setDate(1), e.setHours(0), e.setMinutes(0), e.setSeconds(0), 
        e.setMilliseconds(0);
        for (var f = 0, g = e.getDay(); g > f; f++) d(null);
        for (;e.getMonth() == b; ) d(e), e.setDate(e.getDate() + 1);
        for (c[c.length - 1].length || c.pop(); c[c.length - 1].length < 7; ) d(null, !0);
        return c;
    }, K = new Date().getTimezoneOffset(), L = a.ios || /invalid/.test(new Date(new Date().toISOString()).toString().toLowerCase()), M = function(a, b) {
        try {
            if (L || b) {
                var c = a.replace("T", " ").replace("Z", "").replace(/\-/g, "/").replace(/\..*$/, ""), d = new Date(c);
                return d.setHours(d.getHours() - parseInt(K / 60)), d;
            }
        } catch (e) {}
        return new Date(a);
    }, N = function(a, b) {
        var c = parseInt(K / 60);
        "undefined" == typeof b && (b = -c + ":00:00");
        var d = M(a + "T" + b + ".000Z", !0);
        return d.setHours(d.getHours() + c), d;
    }, O = function(a, b) {
        var c = b + "", d = a, e = c.indexOf(d), f = null, g = null, h = null, i = null, j = null;
        if (-1 == e) return null;
        if (g = c.substr(0, e + d.length), e = g.lastIndexOf("<"), g = c.substr(e), h = g.match(/^\<([a-zA-Z]+)[\s\>]/)[1], 
        new RegExp("^\\<" + h + "[^\\>]*\\/\\>").test(g)) i = "/>", f = e + g.indexOf(i) + i.length; else if (i = "</" + h + ">", 
        f = e + g.indexOf(i) + i.length, g = c.substring(e + h.length + g.indexOf(">"), f), 
        j = g.match(new RegExp("\\<" + h + "[^/>]*[^\\/]?\\>", "g")), null != j) for (var k = j.length; k--; ) f += c.substr(f).indexOf(i) + i.length;
        return {
            start: e,
            end: f
        };
    }, P = function() {
        var a = arguments[0], b = arguments[1], c = Array.prototype.slice.call(arguments, 2);
        return 1 == c.length && c[0] instanceof Array && (c = c[0]), function() {
            for (var d = [], e = 0, f = arguments.length; f > e; e++) d[e] = arguments[e];
            d = d.concat(c), a.apply(b, d);
        };
    }, Q = function(a) {
        g("body").insertAdjacentHTML("beforeEnd", '<a href="javascript:void(0)" id="fakeClick" style="opacity:.01"></a>');
        var b, c = g("#fakeClick");
        c.addEventListener("click", function(b) {
            b.preventDefault(), a();
        }), document.createEvent && (b = document.createEvent("MouseEvents"), b.initMouseEvent && (b.initMouseEvent("click", !0, !0, window, 0, 0, 0, 0, 0, !1, !1, !1, !1, 0, null), 
        c.dispatchEvent(b))), c.parentNode.removeChild(c);
    }, R = function(b, c) {
        $data(b, "lisOldselidx", b.selectedIndex);
        var d = function(a) {
            parseInt($data(b, "lisOldselidx")) != b.selectedIndex && ($data(b, "lisOldselidx", b.selectedIndex), 
            c(a));
        };
        b.addEventListener("change", d), b.addEventListener("focus", d), b.addEventListener("blur", d), 
        a.ios && a.version >= 7 && b.addEventListener("click", d);
    }, S = function() {
        var a = "abbr,article,aside,audio,canvas,datalist,details,dialog,eventsource,fieldset,figure,figcaption,footer,header,hgroup,mark,menu,meter,nav,output,progress,section,small,time,video,legend";
        a.split(",").forEach(function(a) {
            document.createElement(a);
        }), p(a + "{display:block;}");
    }, T = function(a, b) {
        a.addEventListener("webkitTransitionEnd", function(a) {
            a.currentTarget.removeEventListener("webkitTransitionEnd", arguments.callee), b.call(null);
        });
    }, U = function(a) {
        return window._pageIsLoadedFlag ? void a.call(null) : void window.addEventListener("DOMContentLoaded", function(b) {
            b.currentTarget.removeEventListener("DOMContentLoaded", arguments.callee), a.call(null);
        });
    }, V = function(a) {
        window.addEventListener("orientationchange", a);
    }, W = function(a, b, c) {
        "undefined" == typeof b && (b = "body"), f(a, "get", {}, function(a) {
            if (D(g(b), a), "undefined" != typeof c) try {
                c.call(null, a);
            } catch (d) {}
        }, !1);
    };
    window._pageIsLoadedFlag = !1, U(function() {
        window._pageIsLoadedFlag = !0;
    });
    try {
        var X = function() {
            var a = window.sessionStorage, b = window.localStorage, c = function(a) {
                var b = X.getData(a);
                return null != b ? b.value : null;
            }, d = function(c) {
                return c in a ? JSON.parse(a.getItem(c)) : c in b ? JSON.parse(b.getItem(c)) : null;
            }, e = function(c, d) {
                var e = {
                    value: d,
                    ts: new Date().getTime()
                };
                e = JSON.stringify(e), a.setItem(c, e), b.setItem(c, e);
            }, f = function() {
                a.clear(), b.clear();
            }, g = function(c) {
                a.removeItem(c), b.removeItem(c);
            }, h = function(c) {
                var d, e = new Date().getTime();
                for (var f in b) d = X.getData(f), e - d.ts > c && (b.removeItem(f), a.removeItem(f));
            };
            return {
                set: e,
                get: c,
                getData: d,
                clear: f,
                remove: g,
                removeExpires: h
            };
        }();
    } catch (Y) {
        var X = {
            set: function() {},
            get: function() {},
            getData: function() {},
            clear: function() {},
            remove: function() {},
            removeExpires: function() {}
        };
    }
    var Z = function(a) {
        a.preventDefault();
    };
    window._disableSafariElastic = function() {
        document.addEventListener("touchmove", Z, !1);
    }, window._enableSafariElastic = function() {
        document.removeEventListener("touchmove", Z, !1);
    };
    var $ = {
        dom: {
            show: B,
            hide: C,
            hasClass: w,
            addClass: y,
            removeClass: x,
            getRealStyle: z,
            appendHTML: D,
            prependHTML: E
        },
        event: {
            onPageLoaded: U,
            onPageRotated: V,
            onTweened: T,
            delegate: P,
            fakeClick: Q,
            listenSelectChange: R
        },
        utils: {
            initFix: function() {
                S(), s();
            },
            checkOffline: o,
            pageToTop: H,
            setTimeout: F,
            setInterval: G,
            trim: v,
            ensureNumberStringLength: I,
            getFixedIOSDate: M,
            getDatesOfMonth: J,
            string2Date: N,
            forEach: A,
            fixStyleFixed: s,
            testFixedSupport: u,
            writeCSS: p,
            include: W,
            getTagRangeFromHTMLStr: O
        },
        translate: {
            vendor: b,
            open: d,
            close: e
        },
        query: {
            one: g,
            all: h,
            prev: j,
            next: k,
            concat: i
        },
        env: a,
        data: m,
        urlHash: l,
        storage: X,
        ajax: f
    };
    return $;
}();

MOA.env.android && MOA.env.version < 4.1 && setTimeout(function() {
    MOA.utils.writeCSS("input:focus, select:focus, textarea:focus{-webkit-user-modify: initial;}");
}, 2e3);

var $one = MOA.query.one, $all = MOA.query.all, $prev = MOA.query.prev, $next = MOA.query.next, $hasCls = MOA.dom.hasClass, $addCls = MOA.dom.addClass, $rmCls = MOA.dom.removeClass, $append = MOA.dom.appendHTML, $prepend = MOA.dom.prependHTML, $show = MOA.dom.show, $hide = MOA.dom.hide, $trim = MOA.utils.trim, $each = MOA.utils.forEach, $data = MOA.data, $ajax = MOA.ajax, $include = MOA.utils.include, $onload = MOA.event.onPageLoaded;

MOA.utils.checkOffline(), MOA.utils.initFix();