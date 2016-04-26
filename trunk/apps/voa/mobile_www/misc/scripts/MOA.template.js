var MOA;

!function(MOA) {
    !function(mvc) {
        var _env = MOA.env, _isOldAndroid23 = _env.android && _env.version < 2.4, _andReplacer = "=~=and=~=", _rand = function() {
            return _isOldAndroid23 ? "(" + "a,e,i,o,u".split(",")[Math.floor(5 * Math.random())] + Math.round(999999 * Math.random()) + ")?" : "";
        }, _isArray = function(a) {
            return a instanceof Array || "[object Array]" === Object.prototype.toString.call(a);
        }, _toStr = function(a) {
            var b = a;
            if ("string" != typeof b) {
                var c = JSON.stringify(b);
                return c && (b = c.replace(/\"/g, "&quot;")), b;
            }
            return b;
        }, _getData = function(a, b) {
            var c, d, e;
            if (b.indexOf(".") > -1) {
                for (d = b.split("."), e = a[d.shift()]; d.length && void 0 != e; ) e = e[d.shift()] || null;
                c = e;
            } else c = a[b];
            return c = c || null;
        }, _toJSONAttr = function(obj) {
            var str = JSON.stringify(obj);
            return str = str.replace(/\$/g, _andReplacer), str = str.replace(eval('/"' + _rand() + "/g"), "&quot;");
        }, _parseLoopItem = function(loops, layerIdx, layerData, layerTmpl) {
            var prtn = "";
            if (_isArray(layerData)) for (var j = 0; j < layerData.length; j++) {
                var subData = layerData[j];
                if (_isArray(subData)) {
                    var t1 = layerTmpl, v1 = null;
                    try {
                        v1 = layerIdx < loops.length ? _parseLoopItem(loops, layerIdx + 1, subData, loops[layerIdx + 1].tmpl) : _toJSONAttr.call(null, subData);
                    } catch (ex1) {
                        v1 = _toJSONAttr.call(null, subData);
                    }
                    t1 = t1.replace("_field_", v1), prtn += t1;
                } else if ("object" == typeof subData && eval("/_field.([^_]+)_" + _rand() + "/g").test(layerTmpl)) {
                    for (var m2 = layerTmpl.match(eval("/_field.([^_]+)_" + _rand() + "/g")), t2 = layerTmpl, i2 = 0; i2 < m2.length; i2++) try {
                        var k2 = eval("/_field.([^_]+)_" + _rand() + "/").exec(m2[i2])[1], dk2 = _getData.call(null, subData, k2), v2 = null;
                        if ("%me" === k2) t2 = t2.replace("_field.%me_", _toJSONAttr.call(null, subData)); else {
                            if (_isArray(dk2)) try {
                                v2 = layerIdx < loops.length ? _parseLoopItem(loops, layerIdx + 1, dk2, loops[layerIdx + 1].tmpl) : _toJSONAttr.call(null, dk2);
                            } catch (ex2) {
                                v2 = _toJSONAttr.call(null, dk2);
                            } else v2 = _toStr(dk2);
                            t2 = t2.replace("_field." + k2 + "_", v2);
                        }
                    } catch (exFOR) {}
                    prtn += t2;
                } else prtn += layerTmpl.replace("_field_", _toStr(subData));
            }
            return prtn;
        }, _removeEmptyTags = function(a) {
            for (var b = a + "", c = 'data-empty="1"'; b.indexOf(c) > -1; ) {
                var d = MOA.utils.getTagRangeFromHTMLStr(c, b), e = b.substr(0, d.start), f = b.substr(d.end);
                b = e + f;
            }
            return b = b.replace(/\sclass\=\"null\"/g, "");
        }, Template = function() {
            function Template() {}
            return Template.prototype.parse = function(a, b, c, d) {
                "undefined" == typeof c && (c = null), "undefined" == typeof d && (d = !0), c || (c = {});
                var e = this._beginParse(a);
                return e = this._doNesting(e, c), e = this._doSimple(e, b), e = this._doLoop(e, b, c), 
                e = this._doHide(e), e = this._endParse(e), d && (e = _removeEmptyTags(e)), e;
            }, Template.prototype._doNesting = function(a, b) {
                var c = a, d = /\\{\\$([^$]+)\\$\\}/g, e = c.match(d);
                if (null != e) try {
                    for (var f = 0; f < e.length; f++) {
                        var g = e[f].replace(/^\{\${1}/, "").replace(/\${1}\}$/, "");
                        c = b.hasOwnProperty(g) ? c.replace(e[f], _toStr(b[g])) : c.replace(e[f], "");
                    }
                } catch (h) {}
                return c;
            }, Template.prototype._doSimple = function(a, b) {
                var c = a, d = /\\{\\#([^#]+)\\#\\}/g, e = c.match(d);
                if (null != e) for (var f = 0; f < e.length; f++) {
                    var g = e[f].replace(/^\\{\\#{1}/, "").replace(/\\#{1}\\}$/, "");
                    if (g.indexOf(".") > -1) {
                        var h = g.split("."), i = b[h[0]];
                        if (void 0 !== i) for (var j = 1; j < h.length; j++) i = i && i.hasOwnProperty(h[j]) ? i[h[j]] : e[f] + "&nbsp;"; else i = e[f];
                    } else i = "%me" === g ? _toStr(b) : b.hasOwnProperty(g) && "string" == typeof b[g] && !b[g].length ? "" : b[g] || e[f];
                    c = c.replace(e[f], _toStr(i));
                }
                return c;
            }, Template.prototype._doLoop = function(p_tmpl, p_data, p_cache) {
                var rtn = p_tmpl, m0 = rtn.match(eval("/{((#{2}[^#]+#,#[^#]+)+)#{2}}" + _rand() + "/g"));
                if (m0) for (var i = 0; i < m0.length; i++) try {
                    var loops = eval("/{((#{2}[^#]+#,#[^#]+)+)#{2}}" + _rand() + "/g").exec(m0[i])[1].split("##");
                    loops.shift();
                    for (var lpsi = 0; lpsi < loops.length; lpsi++) {
                        var lparr = loops[lpsi].split("#,#");
                        loops[lpsi] = {
                            key: lparr[0],
                            tmpl: lparr[1]
                        }, lparr = null;
                    }
                    var rootItem = loops[0], rootData = _getData(p_data, rootItem.key), rootTmpl = rootItem.tmpl, vlu = _parseLoopItem(loops, 0, rootData, rootTmpl);
                    rtn = rtn.replace(eval("/{((#{2}[^#]+#,#[^#]+)+)#{2}}" + _rand() + "/g").exec(m0[i])[0], vlu);
                } catch (ex) {}
                try {
                    for (var sflag, ssafe = 99; (sflag = rtn.indexOf("data-sub-template")) > -1; ) {
                        var tmpstr = rtn.substr(sflag), tmplname = /^data\-sub\-template\=\"([^"]+)\"/.exec(tmpstr)[1], feature = 'data-sub-template="' + tmplname + '"', range = MOA.utils.getTagRangeFromHTMLStr(feature, rtn), tag = rtn.substring(range.start, range.end), open = rtn.substring(0, range.start), close = rtn.substr(range.end), tmpljson = /data\-sub\-json\=\"([^"]+)\"/.exec(tag)[1];
                        tmpljson = tmpljson.replace(/\&quot\;/g, '"');
                        var tmpljsonobj = {
                            subloop: JSON.parse(tmpljson)
                        }, tmplhtml = p_cache[tmplname] || document.getElementById(tmplname).innerHTML, target = this.parse(tmplhtml, tmpljsonobj);
                        if (tag = tag.replace(/\<\s*\//, target + "</"), tag = tag.replace("data-sub-template", "data-parsed-sub-template"), 
                        rtn = open + tag + close, !ssafe--) break;
                    }
                } catch (ex) {
                    console.log(ex);
                }
                return rtn;
            }, Template.prototype._doHide = function(a) {
                var b = a, c = 'hidden data-empty="1"';
                return b = b.replace(/data-hidden-when-lost=\"undefined\"/g, c), b = b.replace(/data-hidden-when-lost=\"null\"/g, c), 
                b = b.replace(/data-hidden-when-lost=\"\{\#{1}[^\#]+\#{1}\}(\s|\&nbsp\;)*\"/g, c), 
                b = b.replace(/data-hidden-when-lost=\"\_field\.([^\_]+)\_\"/g, c), b = b.replace(/data-hidden-when-lost=\"[^\"]+\"/g, "");
            }, Template.prototype._beginParse = function(a) {
                return a;
            }, Template.prototype._endParse = function(p_tmpl) {
                return p_tmpl = p_tmpl.replace(eval("/_nohref_" + _rand() + "/g"), 'href="javascript:void(0)"'), 
                p_tmpl.replace(eval("/" + _andReplacer + _rand() + "/g"), "$");
            }, Template;
        }();
        mvc.Template = Template;
    }(MOA.mvc || (MOA.mvc = {}));
    var mvc = MOA.mvc;
}(MOA || (MOA = {}));