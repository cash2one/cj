var MOA;

!function(a) {
    !function(b) {
        function c(a) {
            if (a.hasOwnProperty("code") && (a.issuccess = 1 * a.code == 0), a.hasOwnProperty("result")) {
                for (var b in a.result) a[b] = a.result[b];
                a.result = null;
            }
            return a;
        }
        function d(a) {
            var b = location.href.replace(/#.*$/, "").replace(/\??ioswx\=[\d|\.]+/, ""), c = "&", d = [];
            -1 == b.indexOf("?") && (c = "?");
            for (var e in a) d.push(e + "=" + a[e]);
            b = b + c + "ioswx=" + Math.random() + "#" + d.join("&"), b = b.replace(/\?+/, "?").replace(/\&+/, "&").replace(/\#+/, "#"), 
            location.href = b, console.log("directJumpInIOSWeixin", b);
        }
        var e = function() {
            function b() {
                var e = this;
                this._cfg = {}, this._ecache = [], this._isReqesting = !1, this._externalIter = {}, 
                this._currPage = "", this.facade = this._facade, this._utils = {
                    ajax: function(a, d, f) {
                        if ("undefined" == typeof f && (f = !0), !e._isReqesting) {
                            var g, h = a.url, i = a.method, j = a.params, k = a.callback, l = [];
                            if (window.ActiveXObject) g = new window.ActiveXObject("Microsoft.XMLHTTP"); else {
                                if (!window.XMLHttpRequest) return !1;
                                g = new window.XMLHttpRequest();
                            }
                            if (g.onreadystatechange = function() {
                                if (4 == g.readyState && (200 == g.status || 0 == g.status)) {
                                    e._isReqesting = !1;
                                    var h = g.responseText;
                                    h = h.replace(/&apos;/g, "&amp;apos;"), h = h.replace(/&quot;/g, "&amp;quot;");
                                    var i = JSON.parse(h);
                                    if (!i) throw "[ajax] result error";
                                    if (i.result.hasOwnProperty("appVersion") && 1 * i.result.appVersion !== b._appVersion) return console.log("force reload, waiting for update"), 
                                    void setTimeout(function() {
                                        try {
                                            window.applicationCache.swapCache();
                                        } catch (a) {}
                                        location.href = location.origin + location.pathname + "?rnd=" + Math.random() + location.hash;
                                    }, 6180);
                                    k && k.call(a, i), f && (i = c(i)), d && d.call(null, i), e._cfg.callback_loadingOff && e._cfg.callback_loadingOff.call(null);
                                }
                            }, i || (i = "post"), i = i.toLowerCase(), j) for (var m in j) l.push(m + "=" + j[m]);
                            l.push("majaxr=" + Math.random()), l.push("appVer=" + b._appVersion);
                            var n = l.join("&");
                            "get" == i && null != n && (h += h.indexOf("?") > -1 ? "&" : "?", h += n, l = null), 
                            console.log("ajax: ", h, n);
                            try {
                                g.open(i, h, !0), "post" == i && g.setRequestHeader("content-type", "application/x-www-form-urlencoded"), 
                                g.setRequestHeader("X-Requested-With", "XMLHttpRequest"), g.send(n);
                            } catch (o) {
                                throw "[ajax] request error";
                            }
                            return e._isReqesting = !0, e._cfg.callback_loadingOn && e._cfg.callback_loadingOn.call(null), 
                            !0;
                        }
                    },
                    parseTmpl: function(b, c, d, f) {
                        return "undefined" == typeof b && (b = ""), "undefined" == typeof c && (c = {}), 
                        "undefined" == typeof d && (d = void 0), "undefined" == typeof f && (f = !0), "undefined" == typeof d && (d = e._v), 
                        new a.mvc.Template().parse(b, c, d, f);
                    },
                    clearStage: function() {
                        e._body.id = "", e._body.className = "", e._stage.innerHTML = "", $each(e._ecache, function(a) {
                            a.obj.removeEventListener(a.type, a.func);
                        }), e._ecache = [], e._cfg.callback_loadingOff && e._cfg.callback_loadingOff.call(null);
                    },
                    addToStage: function(b) {
                        e._body.id = "", e._body.className = "", e._stage.innerHTML = b, e._utils.fixPageHeight(), 
                        a.utils.fixStyleFixed(!0);
                    },
                    listenEvt: function(a, b, c) {
                        try {
                            a.addEventListener(b, c), e._ecache.push({
                                obj: a,
                                type: b,
                                func: c
                            });
                        } catch (d) {}
                    },
                    pageChg: function() {
                        e._cfg.onPageChange && e._cfg.onPageChange.call(null, e._currPage), setTimeout(a.utils.pageToTop, 0);
                    },
                    setCurrPage: function(a) {
                        e._currPage = a, console.log("setCurrPage", a);
                    },
                    updateHash: function(b) {
                        if (a.env.ios) return void d(b);
                        var c, e, f = new a.urlHash("foo#");
                        for (e in b) f.put(e, b[e]);
                        c = f.toString(), location.hash = c, console.log("updateHash", c);
                    },
                    bindHashListener: function() {
                        a.env.hashSupport ? (window.hasOwnProperty("_oldurlflag") || (window._oldurlflag = location.href), 
                        window.setInterval(function() {
                            var a = location.href;
                            a != window._oldurlflag && (console.log("fake hash", a), e._e.hashchgEvt.call(null, {
                                newURL: a,
                                oldURL: window._oldurlflag
                            }), window._oldurlflag = a);
                        }, 500)) : window.addEventListener("hashchange", e._e.hashchgEvt);
                    },
                    unbindHashListener: function() {
                        window.removeEventListener("hashchange", e._e.hashchgEvt);
                    },
                    fixPageHeight: function() {
                        var b = $one("#" + e._stage.id + " > " + e._cfg.rootElem || ".root"), c = a.dom.getRealStyle;
                        b && (b.style.height = "auto", setTimeout(function() {
                            var a = window.innerHeight - 1 * c(b, "paddingTop") - 1 * c(b, "paddingBottom") - 1 * c(e._body, "paddingTop") - 1 * c(e._body, "paddingBottom");
                            a = Math.max(a, b.scrollHeight), b.style.height = a + "px";
                        }, 0));
                    },
                    changeDocTitle: function(a) {
                        document.title = a || "";
                    },
                    saveTmpls: function() {
                        var a = $all("script");
                        $each(a, function(a) {
                            if (a.type == this._cfg.tmplType) {
                                this._v[a.id] = a.innerHTML.replace(/\n/g, "").replace(/\r/g, "").replace(/\t/g, "").replace(/\s\s+/g, " ");
                                try {
                                    a.parentNode.removeChild(a);
                                } catch (b) {}
                            }
                        });
                        for (var b = $one("body").childNodes, c = b.length - 1; c >= 0; c--) 8 == b[c].nodeType && $one("body").removeChild(b[c]);
                        a = null, b = null;
                    },
                    logicMap: function(a, b) {
                        var c = {};
                        for (var d in b) c[a[d]] = b[d];
                        return c;
                    },
                    initCommonPage: function(a, b) {
                        e._utils.clearStage(), e._utils.changeDocTitle(a.docTitle);
                        var c = e._v[b];
                        c = e._utils.parseTmpl(c, a), e._utils.addToStage(c), e._body.id = b;
                    }
                }, this._e = {
                    hashchgEvt: function(b) {
                        var c = new a.urlHash(b.newURL), d = new a.urlHash(b.oldURL), f = 1 * c.get("act"), g = 1 * d.get("act");
                        console.log("hashchange", "from " + g + " to " + f), e._c.RouteCommand();
                    },
                    commonAjaxClick: function(a) {
                        var b = a.currentTarget;
                        if (b.hasAttribute("data-ajax-act")) {
                            var c, d, f = {
                                act: $data(b, "ajaxAct")
                            };
                            if (/(^|\s)disable(\s|$)/.test(b.className)) return a.preventDefault(), void a.stopPropagation();
                            if ($data(b, "ajaxParams")) {
                                c = $data(b, "ajaxParams"), c = JSON.parse(c);
                                for (d in c) f[d] = c[d];
                            }
                            if ("string" == typeof f.act && /^js\:/.test(f.act)) {
                                var g = f.act.replace(/^js\:/, "");
                                if (e._cfg.jsbridge.hasOwnProperty(g)) return delete f.act, void e._cfg.jsbridge[g].call(null, f);
                            }
                            return "string" == typeof f.act && /^(https?|weixin)\:\/{2}/.test(f.act) ? (b.setAttribute("href", f.act), 
                            b.hasAttribute("data-ajax-act") && b.removeAttribute("data-ajax-act"), b.hasAttribute("data-ajax-params") && b.removeAttribute("data-ajax-params"), 
                            !0) : void e._utils.updateHash(f);
                        }
                    }
                }, this._m = {
                    ReqObject: function(a, b, c, d, e) {
                        this.url = a, this.method = b, this.params = c, this.callback = d, this.set = function(a, b) {
                            (!e || e && !this.params.hasOwnProperty(a)) && (this.params[a] = b);
                        };
                    }
                }, this._v = {}, this._c = {
                    ErrorCommand: function(a) {
                        window.alert(a.message);
                    },
                    RouteCommand: function(c) {
                        e._hash = c || new a.urlHash();
                        var d = e._hash.get("act"), f = null;
                        console.log("RouteCommand", e._hash.get("act")), d || (console.log("采用默认动作 act=_PAGE_GETROUTE"), 
                        d = b._PAGE_GETROUTE), d += "", d = d.indexOf("PAGE_") > -1 ? b[d] : 1 * d, f = e._cfg.ajaxMap[d] || e._cfg.defaultAjax, 
                        f = e._cfg.ajax[f], f && $each(e._hash.keys(), function(a) {
                            f.set(a, e._hash.get(a));
                        }), e._utils.ajax(f, function(a) {
                            if (1 == a.issuccess) {
                                if ("preJSAction" in a) {
                                    var b = new Function(a.preJSAction);
                                    if (b() === !1) return;
                                }
                                var c = a.pagetype, d = e._cfg.commandMap[c];
                                d = e._cfg.commands[d], d && d.call(null, a), e._utils.pageChg();
                            } else e._c.ErrorCommand(a);
                        });
                    }
                }, this._facade = {
                    startup: this._init,
                    $m: this._m,
                    $v: this._v,
                    $c: this._c,
                    $e: this._e,
                    config: this._cfg,
                    utils: this._utils,
                    body: this._body,
                    stage: this._stage,
                    hash: this._hash
                };
            }
            return b.prototype.config = function(a) {
                this._cfg = a, this._facade.config = this._cfg;
                for (var b in this._cfg.commands) this._c[b] = this._cfg.commands[b];
            }, b.prototype.getConfigItem = function(a) {
                return this._cfg[a];
            }, b.prototype.getCurrPage = function() {
                return this._currPage;
            }, b.prototype.getHandler = function(a) {
                return this._externalIter[a];
            }, b.prototype._init = function() {
                this._stage = document.createElement("div"), this._stage.id = "mappContainer", this._body = $one("body"), 
                this._facade.stage = this._stage, this._facade.body = this._body, this._body.appendChild(this._stage), 
                this._hash = new a.urlHash(), this._hash.toString().length || (location.hash = "act=" + b._PAGE_GETROUTE), 
                this._utils.bindHashListener(), this._utils.saveTmpls();
                for (var c in this._cfg.commands) this._c[c] || (this._c[c] = this._cfg.commands[c]);
                window.addEventListener("orientationchange", this._utils.fixPageHeight), this._c.RouteCommand(), 
                "initialize" in this._cfg && this._cfg.initialize.call(null);
            }, b._PAGE_GETROUTE = 0, b._appVersion = 1, b;
        }();
        b.MVCBuilder = e;
    }(a.mvc || (a.mvc = {}));
    a.mvc;
}(MOA || (MOA = {}));