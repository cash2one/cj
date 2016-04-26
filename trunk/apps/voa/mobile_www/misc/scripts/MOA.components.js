function showBigPicture(a, b) {
    a.addEventListener("click", function() {
        var a = MDialog.popupImage(b, window.innerWidth - 18, !1, !0, function(b) {
            if (b.width > b.height) {
                var c = (window.innerHeight - 50) / b.width, d = (window.innerWidth - 18) / b.height, e = Math.min(c, d);
                a.style[MOA.translate.vendor + "Transform"] = "rotate(90deg) scale(" + e + ")";
            }
            b.onclick = function() {
                MDialog.close();
            };
        });
        $data(a, "closeByModal", 1);
    });
}

function _parseReadOnlyBigViewPhotos() {
    var a = function(a) {
        var b = $one(".rm", a);
        b && b.addEventListener("click", function(a) {
            "removeReadonlyPhotoCallback" in window && (a.stopPropagation(), window.removeReadonlyPhotoCallback.call(null, a.currentTarget.parentNode, a.currentTarget));
        });
    };
    MOA.env.weixin ? document.addEventListener("WeixinJSBridgeReady", function() {
        $each(".mod_photo_uploader.readonly .photo", function(b) {
            "yes" !== $data(b, "bigviewParsed") && ($data(b, "bigviewParsed", "yes"), b.addEventListener("click", function(b) {
                var c = b.currentTarget, d = Array.prototype.map.call($all(".photo", c.parentNode), function(a) {
                    return $data($one("img", a), "big");
                });
                WeixinJSBridge.invoke("imagePreview", {
                    current: $data($one("img", c), "big"),
                    urls: d
                }), a(c);
            }));
        });
    }) : $each(".mod_photo_uploader.readonly .photo", function(b) {
        "yes" !== $data(b, "bigviewParsed") && ($data(b, "bigviewParsed", "yes"), showBigPicture(b, $data($one("img", b), "big")), 
        a(b));
    });
}

$onload(function() {
    var a = $all(".mod_score_starsbar");
    if (a) {
        var b = function(a, b, c) {
            var d = c / f;
            b.style.width = parseInt(a.clientWidth * d) + "px", $data(a, "num", c);
        }, c = function(a, b) {
            var c = a.getBoundingClientRect();
            return {
                x: b.clientX - c.left,
                y: b.clientY - c.top
            };
        }, d = function(a, b) {
            var d = null;
            d = "touchend" === b.type ? b.changedTouches[0] : b.touches[0];
            var e = c(a, d), f = e.x / a.clientWidth * 100;
            return 0 > f && (f = 0), f > 100 && (f = 100), f = Math.round(f / 20);
        }, e = !1, f = 5;
        $each(a, function(a) {
            var c = $all("li", a);
            $each(c, function(c) {
                var f = $one("em", c), g = $one("i", f), h = parseInt($data(f, "num"));
                isNaN(h) && (h = 0), b(f, g, h), $hasCls(a, "editable") && (f.addEventListener("touchstart", function() {
                    e || (e = !0, _disableSafariElastic());
                }), f.addEventListener("touchmove", function(a) {
                    b(f, g, d(f, a));
                }), f.addEventListener("touchend", function(h) {
                    _enableSafariElastic(), e = !1, b(f, g, d(f, h));
                    var i = $data(a, "callback");
                    i && i in window && window[i].call(null, c, parseInt($data(f, "num")));
                }));
            });
        });
    }
}), $onload(function() {
    var a = $one(".mod_top_search");
    if (a) {
        var b = $one(".sinner", a), c = $one("input", a), d = function() {
            var a, d, e = !!c.value;
            e ? (a = 10, d = b.clientWidth - 20) : (a = 55, d = 190), c.style.marginLeft = a + "px", 
            c.style.width = d - 20 + "px";
        };
        d(), c.addEventListener("focus", d), c.addEventListener("blur", d), c.addEventListener("keyup", d);
    }
}), $onload(function() {
    var a = $one(".mod_profile_header");
    if (a) {
        var b = function() {
            var b = $one("ul", a), c = $all("li", b).length, d = parseInt((window.innerWidth - c) / c);
            MOA.utils.writeCSS([ ".mod_profile_header ul li{	width:" + d + "px;	}" ].join(""));
        };
        b(), setTimeout(b, 1e3);
    }
}), $onload(function() {
    var a = $one(".mod_list_nav");
    if (a) {
        var b = function() {
            var b = $all("a", a).length, c = parseInt((window.innerWidth - b) / b);
            MOA.utils.writeCSS([ ".mod_list_nav>a{	width:" + c + "px;	}" ].join(""));
        };
        b(), setTimeout(b, 1e3);
    }
}), window._parseModFootbar = function() {
    var a = $one(".mod_footbar"), b = MOA.query.concat($all("input[type=text]"), $all("input[type=search]"), $all("input[type=password]"), $all("input[type=number]"), $all("input[type=url]"), $all("input[type=tel]"), $all("input[type=email]"), $all("textarea")), c = function() {
        a.style.display = "block";
    }, d = function() {
        a.style.display = "none";
    };
    $each(b, function(a) {
        a.addEventListener("focus", d), a.addEventListener("blur", c);
    }), $each($all("menu a", a), function(b) {
        b.addEventListener("click", function() {
            $each($all("menu", a), function(a) {
                $rmCls(a, "open"), $hide(a);
            });
        }, !1);
    }), $each($all(".inner>a", a), function(b) {
        var c = $one(".m" + b.className, b.parentNode);
        c && b.addEventListener("click", function() {
            if ($hasCls(c, "open")) $rmCls(c, "open"), $hide(c); else {
                $addCls(c, "open"), $show(c);
                var d = b.getBoundingClientRect().left + .5 * (b.clientWidth - c.clientWidth - 4);
                0 >= d && (d = 5), d + c.clientWidth + 2 >= window.innerWidth && (d = window.innerWidth - c.clientWidth - 5), 
                c.style.left = d + "px", setTimeout(function() {
                    window.addEventListener("click", function(b) {
                        window.removeEventListener("click", arguments.callee), /^a$/i.test(b.target.nodeName) && b.target.parentNode && b.target.parentNode.parentNode && /mod_footbar/.test(b.target.parentNode.parentNode.className) || /^a$/i.test(b.target.nodeName) && b.target.parentNode && /^menu$/i.test(b.target.parentNode.nodeName) && b.target.parentNode.parentNode && /^inner$/i.test(b.target.parentNode.parentNode.className) || ($each($all("menu", a), function(a) {
                            $rmCls(a, "open"), $hide(a);
                        }), b.preventDefault(), b.stopPropagation());
                    });
                }, 0);
            }
            $each($all("menu", a), function(a) {
                c.className !== a.className && ($rmCls(a, "open"), $hide(a));
            });
        });
    });
}, $onload(function() {
    var a = $one(".mod_footbar");
    a && window._parseModFootbar();
}), $onload(function() {
    var a = 0, b = 30, c = document.body.clientHeight, d = $one("#viewstack section");
    d && (c = Math.max(c, d.clientHeight), b = 10, a = 1e3), setTimeout(function() {
        var a = $one(".mod_copyright");
        if (a) {
            var c = window.innerHeight - document.body.clientHeight;
            0 >= c ? $rmCls(a, "footFix") : (b = c, $rmCls(a, "footFix"));
            var d = MOA.query.concat($all("input[type=text]"), $all("input[type=password]"), $all("input[type=search]"));
            $each(d, function(b) {
                b.addEventListener("focus", function() {
                    $hide(a);
                }), b.addEventListener("blur", function() {
                    $show(a);
                });
            }), a.style.marginTop = b + "px", a.style.opacity = 1;
        }
    }, a);
}), $onload(function() {
    _parseReadOnlyBigViewPhotos();
}), $onload(function() {
    function a() {
        $each(".mod_circlestyle_list a.item", function(a) {
            $hasCls(a, "parsed") || ($addCls(a, "parsed"), a.addEventListener("click", function(a) {
                var b = a.currentTarget.parentNode, c = $next(a.currentTarget);
                c && ($hasCls(b, "dl-open") ? ($hide(c), $rmCls(b, "dl-open")) : ($show(c), $addCls(b, "dl-open")));
            }));
        });
    }
    window._toggleCircleStyleListItemDetail = a, $one(".mod_circlestyle_list") && a();
}), $onload(function() {
    $each(".mod_members.slider", function(a) {
        var b = 4, c = MOA.translate, d = ($one(".sinner", a), $one("ul", a)), e = $all("li", d), f = e.length, g = e[0].clientWidth, h = Math.ceil(f / b), i = null, j = null, k = !1, l = function() {
            return parseInt($data(d, "currpage"));
        }, m = function(a) {
            $data(d, "currpage", a);
        }, n = function(a) {
            k || (k = !0, d.style[c.vendor + "Transform"] = c.open + -g * b * a + "px, 0px" + c.close, 
            MOA.event.onTweened(d, function() {
                m(a), o(), k = !1;
            }));
        }, o = function() {
            $show(i, j), 0 == l() && $hide(i), l() == h - 1 && $hide(j);
        };
        d.style.width = g * f + "px", b >= f || (console.log(a, ": slider"), $prepend(a, '<a href="javascript:void(0)" class="prev"><span></span></a>'), 
        $append(a, '<a href="javascript:void(0)" class="next"><span></span></a>'), i = $one("a.prev", a), 
        j = $one("a.next", a), i.addEventListener("click", function() {
            var a = l() - 1;
            0 > a || n(a);
        }), j.addEventListener("click", function() {
            var a = l() + 1;
            a > h - 1 || n(a);
        }), m(0), o());
    });
});