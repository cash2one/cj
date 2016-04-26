define([ "Underscore", "text!templates/addressbook/list.html", "text!templates/addressbook/box_item.html" ], function(a, b, c) {
    var d = 0, e = 1, f = 2, g = function(b, c) {
        var d = a.extend({
            isRoot: !1,
            isPerson: !1,
            id: null,
            name: null,
            job: null,
            face: null,
            profileURL: null,
            numNodes: 0
        }, b);
        return c ? d.nodes = a.map(d.nodes, function(a) {
            var b = g(a, !1);
            return b;
        }) : (d.numNodes = "nodes" in d ? d.nodes.length : 0, delete d.nodes), d;
    }, h = function(a) {
        this.model = null, this.currentNode = null, this.nodesCache = {}, this.existIds = [], 
        this.type = parseInt(a), (isNaN(a) || -1 === [ d, f, e ].indexOf(a)) && (a = d), 
        h._selected_nodes = this.type === e ? [] : null, this.$vs = $one("#viewstack"), 
        this.$sec = $one("#viewstack>section"), this.$panel = $one("#viewstack>menu.mod_members_panel");
    };
    return h.TYPE_ADDRESSBOOK_NAVIGATE = d, h.TYPE_ADDRESSBOOK_SINGLESELECT = f, h.TYPE_ADDRESSBOOK_MULTISELECT = e, 
    h.prototype = {
        patch: function(a, b) {
            var c = this;
            return $ajax(a, "GET", {}, function(a) {
                if (c.type !== parseInt(a.type)) throw "[AddressBookComponent] 返回类型错误";
                c.nodesCache = {}, c._preParseNode(a.root), c._onChange(a.root, b);
            }, !0), this;
        },
        render: function(a) {
            return null !== this.model ? ($addCls(this.$vs, "v2"), $addCls(this.$vs, "type_1"), 
            "undefined" == typeof a && (a = []), this.existIds = a, this._renderList(), this) : void 0;
        },
        open: function(b, c) {
            var d = this;
            a.isFunction(b) && b.call(d), $addCls(d.$vs, "view2"), MOA.event.onTweened(d.$vs, function() {
                $addCls(d.$sec, "view2"), MOA.utils.pageToTop(), a.isFunction(c) && c.call(d);
            });
        },
        close: function(b) {
            var c = this;
            setTimeout(function() {
                $rmCls(c.$vs, "view2"), $rmCls(c.$sec, "view2"), MOA.event.onTweened(c.$vs, function() {
                    c.$panel.innerHTML = "";
                }), a.isFunction(b) && b.call(null);
            }, 500), window.clearTimeout(window._addressBookHtTo);
        },
        unique: function(a, b) {
            $each($all("li.newjoin", a), function(a) {
                b.toString() === a.id && a.parentNode.removeChild(a);
            });
        },
        _onChange: function(b, c) {
            this.model = b, this.currentNode = g(b, !0), a.isFunction(c) && c.call(this, b);
        },
        _renderList: function() {
            var c = a.clone(this.currentNode);
            c.t2_selected_nodes = h._selected_nodes, c.existIds = this.existIds;
            var d = a.template(b, c);
            this.$panel.innerHTML = "", $append(this.$panel, d);
            var f = this;
            this.type === e && (this.$okBtn = $one("#adb_okbtn"), this.$okBtn.onclick = function() {
                a.isFunction(f.onSelected) && (f.close(), f.onSelected.call(f, h._selected_nodes));
            }, $show(this.$okBtn.parentNode));
            var g = $one(".pageReturn", this.$panel);
            g && g.addEventListener("click", a.bind(f._onClickPagereturn, f));
            var i = $one(".pageClose", this.$panel);
            i && i.addEventListener("click", a.bind(f.close, f)), a.each($all("li[id^=department]", this.$panel), function(b) {
                b.addEventListener("click", a.bind(f._onClickDepartment, f));
            }), a.each($all("li[id^=person]", this.$panel), function(b) {
                b.addEventListener("click", a.bind(f._onClickPerson, f));
            });
        },
        _onClickPagereturn: function() {
            var a = this.currentNode.path.split(",").slice(0, -1).join(",");
            "" === a && (a = "_root");
            var b = this.nodesCache[a];
            this._onChange(b), this._renderList();
        },
        _onClickDepartment: function(a) {
            var b = $data(a.currentTarget, "path"), c = this.nodesCache[b];
            this._onChange(c), this._renderList();
        },
        _onClickPerson: function(b) {
            switch (this.type) {
              case e:
                var c = $data(b.currentTarget, "path"), g = this.nodesCache[c];
                if ($hasCls(b.currentTarget, "selected")) {
                    $rmCls(b.currentTarget, "selected");
                    for (var i = h._selected_nodes, j = 0; j < i.length; j++) {
                        var k = i[j];
                        if (g.id === k.id) {
                            h._selected_nodes = i.slice(0, j).concat(i.slice(j + 1)), console.log("[AddressBookComponent] remove: ", g.id, g.name);
                            break;
                        }
                    }
                } else $addCls(b.currentTarget, "selected"), h._selected_nodes.push(g), console.log("[AddressBookComponent] add: ", g.id, g.name);
                break;

              case f:
                if ($addCls(b.currentTarget, "selected"), this.close(), a.isFunction(this.onSelected)) {
                    var c = $data(b.currentTarget, "path"), g = this.nodesCache[c];
                    this.onSelected.call(this, g);
                }
                break;

              case d:            }
        },
        _preParseNode: function(b, c, d) {
            if ("undefined" == typeof c && (c = null), "undefined" == typeof d && (d = null), 
            b.path = null === c && null === d ? "_root" : [ c, d ].join(",").replace(/^\,/, "").replace(/^\_root\,/, ""), 
            b._type = this.type, !b.isPerson || this.type !== f && this.type !== e || (b.profileURL = "javascript:void(0)"), 
            this.nodesCache[b.path] = b, b.hasOwnProperty("nodes") && a.isArray(b.nodes)) for (var g = 0; g < b.nodes.length; g++) {
                var h = b.nodes[g];
                this._preParseNode(h, b.path, g);
            }
        }
    }, h.getBoxItemHTML = function(b, d) {
        "undefined" == typeof d && (d = !0);
        var e = a.clone(b);
        return e.needRemove = d, a.template(c, e);
    }, h;
}), $onload(function() {
    var a = $one("#viewstack");
    if (a) {
        if (!$hasCls(a.parentNode, "center")) {
            var b = window.innerWidth;
            MOA.utils.writeCSS([ "#viewstack{width:" + (b + 1) + "px;}", "#viewstack>section{width:" + b + "px;}", "#viewstack>menu{left:" + b + "px;}", "#viewstack.view2>menu{-webkit-transform:translate3d(-" + b + "px, 0, 0);}", "#viewstack.view2>menu{width:" + b + "px;}", ".mod_members_panel{width:" + b + "px;}" ].join("")), 
            console.log("[MOA.members] alter the #viewstack:width to ", b);
        }
        var c = function(a, b) {
            var d = window.innerHeight;
            d = b ? Math.max(d, b.clientHeight) : d, a.style.height = d + "px", window._addressBookHtTo = MOA.utils.setTimeout(c, 1e3, a, b);
        }, d = $one("#viewstack>section");
        c(a, d);
    }
});