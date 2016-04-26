!function () {
	function a() {
		for (var a = $G("tabHeads").children, b = 0; b < a.length; b++)domUtils.on(a[b], "click", function (b) {
			var c, d, e = b.target || b.srcElement;
			for (c = 0; c < a.length; c++)d = a[c].getAttribute("data-content-id"), a[c] == e ? (domUtils.addClass(a[c], "focus"), domUtils.addClass($G(d), "focus")) : (domUtils.removeClasses(a[c], "focus"), domUtils.removeClasses($G(d), "focus"))
		})
	}

	function b() {
		l(["videoFloat", "upload_alignment"]), n($G("videoUrl")), c(), function () {
			var a, b = editor.selection.getRange().getClosedNode();
			if (b && b.className) {
				var c = "edui-faked-video" == b.className, e = -1 != b.className.indexOf("edui-upload-video");
				if (c || e) {
					$G("videoUrl").value = a = b.getAttribute("_url"), $G("videoWidth").value = b.width, $G("videoHeight").value = b.height;
					var f = domUtils.getComputedStyle(b, "float"), g = domUtils.getComputedStyle(b.parentNode, "text-align");
					d("center" === g ? "center" : f)
				}
				e && (y = !0)
			}
			o(a)
		}()
	}

	function c() {
		dialog.onok = function () {
			$G("preview").innerHTML = "";
			var a = g("tabHeads", "tabSrc");
			switch (a) {
				case"video":
					return e();
				case"videoSearch":
					return f("searchList");
				case"upload":
					return p()
			}
		}, dialog.oncancel = function () {
			$G("preview").innerHTML = ""
		}
	}

	function d(a) {
		for (var b, c = $G("videoFloat").children, d = 0; b = c[d++];)b.getAttribute("name") == a ? "focus" != b.className && (b.className = "focus") : "focus" == b.className && (b.className = "")
	}

	function e() {
		{
			var a = $G("videoWidth"), b = $G("videoHeight"), c = $G("videoUrl").value;
			g("videoFloat", "name")
		}
		if (!c)return !1;
		if (!j([a, b]))return !1;
		var d = a.value || t, e = b.value || u;
		conUrl = i(c, d, e);
		var f = [];
		f.push('<iframe style="position:relative; z-index:1;" width=' + d + " height=" + e + ' frameborder=0 src="' + conUrl + '" allowfullscreen></iframe><br/>'), editor.execCommand("inserthtml", f.join(""), !0)
	}

	function f(a) {
		for (var b, c = domUtils.getElementsByTagName($G(a), "img"), d = [], e = 0; b = c[e++];)b.getAttribute("selected") && d.push({
			url: b.getAttribute("ue_video_url"),
			width: 420,
			height: 280,
			align: "none"
		});
		editor.execCommand("insertvideo", d)
	}

	function g(a, b) {
		for (var c, d, e = $G(a).children, f = 0; d = e[f++];)if ("focus" == d.className) {
			c = d.getAttribute(b);
			break
		}
		return c
	}

	function h(a, b, c) {
		var d, e = "";
		return (d = a.match(new RegExp("(^|&|\\\\?)vid=([^&]*)(&|$|#)"))) ? (e = encodeURIComponent(d[2]), a = "/frontend/index/redirect?url=" + encodeURIComponent("http://v.qq.com/iframe/player.html?vid=" + e + "&width=" + b + "&height=" + c + "&auto=0")) : (d = a.match(new RegExp("(http://)?v\\.qq\\.com/(.*)/(.*)\\.html"))) && (e = encodeURIComponent(d[3]), a = "/frontend/index/redirect?url=" + encodeURIComponent("http://v.qq.com/iframe/player.html?vid=" + e + "&width=" + b + "&height=" + c + "&auto=0")), a
	}

	function i(a, b, c) {
		return a ? a = h(a, b || 420, c || 280) : ""
	}

	function j(a) {
		for (var b, c = 0; b = a[c++];) {
			var d = b.value;
			if (!k(d) && d)return alert(lang.numError), b.value = "", b.focus(), !1
		}
		return !0
	}

	function k(a) {
		return /(0|^[1-9]\d*$)/.test(a)
	}

	function l(a) {
		for (var b, c = 0; b = a[c++];) {
			var d = $G(b), e = {
				none: lang["default"],
				left: lang.floatLeft,
				right: lang.floatRight,
				center: lang.block
			};
			for (var f in e) {
				var g = document.createElement("div");
				g.setAttribute("name", f), "none" == f && (g.className = "focus"), g.style.cssText = "background:url(images/" + f + "_focus.jpg);", g.setAttribute("title", e[f]), d.appendChild(g)
			}
			m(b)
		}
	}

	function m(a) {
		for (var b, c = $G(a).children, d = 0; b = c[d++];)domUtils.on(b, "click", function () {
			for (var a, b = 0; a = c[b++];)a.className = "", a.removeAttribute && a.removeAttribute("class");
			this.className = "focus"
		})
	}

	function n(a) {
		browser.ie ? a.onpropertychange = function () {
			o(this.value)
		} : a.addEventListener("input", function () {
			o(this.value)
		}, !1)
	}

	function o(a) {
		if (a) {
			var b = i(a);
			$G("preview").innerHTML = "<iframe height=" + w + " width=" + v + ' frameborder=0 src="' + b + '" allowfullscreen></iframe>'
		}
	}

	function p() {
		var a = [], b = editor.getOpt("videoUrlPrefix"), c = $G("upload_width").value || 420, d = $G("upload_height").value || 280, e = g("upload_alignment", "name") || "none";
		for (var f in x) {
			var h = x[f];
			a.push({url: b + h.url, width: c, height: d, align: e})
		}
		var i = s.getQueueCount();
		return i ? ($(".info", "#queueList").html('<span style="color:red;">' + "还有2个未上传文件".replace(/[\d]/, i) + "</span>"), !1) : void editor.execCommand("insertvideo", a, "upload")
	}

	function q() {
		s = new r("queueList")
	}

	function r(a) {
		this.$wrap = $(a.constructor == String ? "#" + a : a), this.init()
	}

	var s, t = 300, u = 200, v = 548, w = 280, x = [], y = !1;
	window.onload = function () {
		$focus($G("videoUrl")), a(), b(), q()
	}, r.prototype = {
		init: function () {
			this.fileList = [], this.initContainer(), this.initUploader()
		}, initContainer: function () {
			this.$queue = this.$wrap.find(".filelist")
		}, initUploader: function () {
			function a(a) {
				var b = h('<li id="' + a.id + '"><p class="title">' + a.name + '</p><p class="imgWrap"></p><p class="progress"><span></span></p></li>'), c = h('<div class="file-panel"><span class="cancel">' + lang.uploadDelete + '</span><span class="rotateRight">' + lang.uploadTurnRight + '</span><span class="rotateLeft">' + lang.uploadTurnLeft + "</span></div>").appendTo(b), d = b.find("p.progress span"), e = b.find("p.imgWrap"), g = h('<p class="error"></p>').hide().appendTo(b), i = function (a) {
					switch (a) {
						case"exceed_size":
							text = lang.errorExceedSize;
							break;
						case"interrupt":
							text = lang.errorInterrupt;
							break;
						case"http":
							text = lang.errorHttp;
							break;
						case"not_allow_type":
							text = lang.errorFileType;
							break;
						default:
							text = lang.errorUploadRetry
					}
					g.text(text).show()
				};
				"invalid" === a.getStatus() ? i(a.statusText) : (e.text(lang.uploadPreview), -1 == "|png|jpg|jpeg|bmp|gif|".indexOf("|" + a.ext.toLowerCase() + "|") ? e.empty().addClass("notimage").append('<i class="file-preview file-type-' + a.ext.toLowerCase() + '"></i><span class="file-title">' + a.name + "</span>") : browser.ie && browser.version <= 7 ? e.text(lang.uploadNoPreview) : f.makeThumb(a, function (a, b) {
					if (a || !b || /^data:/.test(b) && browser.ie && browser.version <= 7)e.text(lang.uploadNoPreview); else {
						var c = h('<img src="' + b + '">');
						e.empty().append(c), c.on("error", function () {
							e.text(lang.uploadNoPreview)
						})
					}
				}, t, u), w[a.id] = [a.size, 0], a.rotation = 0, a.ext && -1 != B.indexOf(a.ext.toLowerCase()) || (i("not_allow_type"), f.removeFile(a))), a.on("statuschange", function (e, f) {
					"progress" === f ? d.hide().width(0) : "queued" === f && (b.off("mouseenter mouseleave"), c.remove()), "error" === e || "invalid" === e ? (i(a.statusText), w[a.id][1] = 1) : "interrupt" === e ? i("interrupt") : "queued" === e ? w[a.id][1] = 0 : "progress" === e && (g.hide(), d.css("display", "block")), b.removeClass("state-" + f).addClass("state-" + e)
				}), b.on("mouseenter", function () {
					c.stop().animate({height: 30})
				}), b.on("mouseleave", function () {
					c.stop().animate({height: 0})
				}), c.on("click", "span", function () {
					var b, c = h(this).index();
					switch (c) {
						case 0:
							return void f.removeFile(a);
						case 1:
							a.rotation += 90;
							break;
						case 2:
							a.rotation -= 90
					}
					y ? (b = "rotate(" + a.rotation + "deg)", e.css({
						"-webkit-transform": b,
						"-mos-transform": b,
						"-o-transform": b,
						transform: b
					})) : e.css("filter", "progid:DXImageTransform.Microsoft.BasicImage(rotation=" + ~~(a.rotation / 90 % 4 + 4) % 4 + ")")
				}), b.insertBefore(n)
			}

			function b(a) {
				var b = h("#" + a.id);
				delete w[a.id], c(), b.off().find(".file-panel").off().end().remove()
			}

			function c() {
				var a, b = 0, c = 0, d = p.children();
				h.each(w, function (a, d) {
					c += d[0], b += d[0] * d[1]
				}), a = c ? b / c : 0, d.eq(0).text(Math.round(100 * a) + "%"), d.eq(1).css("width", Math.round(100 * a) + "%"), e()
			}

			function d(a) {
				if (a != v) {
					var b = f.getStats();
					switch (m.removeClass("state-" + v), m.addClass("state-" + a), a) {
						case"pedding":
							j.addClass("element-invisible"), k.addClass("element-invisible"), o.removeClass("element-invisible"), p.hide(), l.hide(), f.refresh();
							break;
						case"ready":
							o.addClass("element-invisible"), j.removeClass("element-invisible"), k.removeClass("element-invisible"), p.hide(), l.show(), m.text(lang.uploadStart), f.refresh();
							break;
						case"uploading":
							p.show(), l.hide(), m.text(lang.uploadPause);
							break;
						case"paused":
							p.show(), l.hide(), m.text(lang.uploadContinue);
							break;
						case"confirm":
							if (p.show(), l.hide(), m.text(lang.uploadStart), b = f.getStats(), b.successNum && !b.uploadFailNum)return void d("finish");
							break;
						case"finish":
							p.hide(), l.show(), m.text(b.uploadFailNum ? lang.uploadRetry : lang.uploadStart)
					}
					v = a, e()
				}
				g.getQueueCount() ? m.removeClass("disabled") : m.addClass("disabled")
			}

			function e() {
				var a, b = "";
				"ready" === v ? b = lang.updateStatusReady.replace("_", q).replace("_KB", WebUploader.formatSize(r)) : "confirm" === v ? (a = f.getStats(), a.uploadFailNum && (b = lang.updateStatusConfirm.replace("_", a.successNum).replace("_", a.successNum))) : (a = f.getStats(), b = lang.updateStatusFinish.replace("_", q).replace("_KB", WebUploader.formatSize(r)).replace("_", a.successNum), a.uploadFailNum && (b += lang.updateStatusError.replace("_", a.uploadFailNum))), l.html(b)
			}

			var f, g = this, h = jQuery, i = g.$wrap, j = i.find(".filelist"), k = i.find(".statusBar"), l = k.find(".info"), m = i.find(".uploadBtn"), n = (i.find(".filePickerBtn"), i.find(".filePickerBlock")), o = i.find(".placeholder"), p = k.find(".progress").hide(), q = 0, r = 0, s = window.devicePixelRatio || 1, t = 113 * s, u = 113 * s, v = "", w = {}, y = function () {
				var a = document.createElement("p").style, b = "transition"in a || "WebkitTransition"in a || "MozTransition"in a || "msTransition"in a || "OTransition"in a;
				return a = null, b
			}(), z = editor.getActionUrl(editor.getOpt("videoActionName")), A = editor.getOpt("videoMaxSize"), B = (editor.getOpt("videoAllowFiles") || []).join("").replace(/\./g, ",").replace(/^[,]/, "");
			return WebUploader.Uploader.support() ? editor.getOpt("videoActionName") ? (f = g.uploader = WebUploader.create({
				pick: {
					id: "#filePickerReady",
					label: lang.uploadSelectFile
				},
				swf: "../../third-party/webuploader/Uploader.swf",
				server: z,
				fileVal: editor.getOpt("videoFieldName"),
				duplicate: !0,
				fileSingleSizeLimit: A,
				compress: !1
			}), f.addButton({id: "#filePickerBlock"}), f.addButton({
				id: "#filePickerBtn",
				label: lang.uploadAddFile
			}), d("pedding"), f.on("fileQueued", function (b) {
				q++, r += b.size, 1 === q && (o.addClass("element-invisible"), k.show()), a(b)
			}), f.on("fileDequeued", function (a) {
				q--, r -= a.size, b(a), c()
			}), f.on("filesQueued", function () {
				f.isInProgress() || "pedding" != v && "finish" != v && "confirm" != v && "ready" != v || d("ready"), c()
			}), f.on("all", function (a, b) {
				switch (a) {
					case"uploadFinished":
						d("confirm", b);
						break;
					case"startUpload":
						var c = utils.serializeParam(editor.queryCommandValue("serverparam")) || "", e = utils.formatUrl(z + (-1 == z.indexOf("?") ? "?" : "&") + "encode=utf-8&" + c);
						f.option("server", e), d("uploading", b);
						break;
					case"stopUpload":
						d("paused", b)
				}
			}), f.on("uploadBeforeSend", function (a, b, c) {
				c.X_Requested_With = "XMLHttpRequest"
			}), f.on("uploadProgress", function (a, b) {
				var d = h("#" + a.id), e = d.find(".progress span");
				e.css("width", 100 * b + "%"), w[a.id][1] = b, c()
			}), f.on("uploadSuccess", function (a, b) {
				var c = h("#" + a.id);
				try {
					var d = b._raw || b, e = utils.str2json(d);
					"SUCCESS" == e.state ? (x.push({
						url: e.url,
						type: e.type,
						original: e.original
					}), c.append('<span class="success"></span>')) : c.find(".error").text(e.state).show()
				} catch (f) {
					c.find(".error").text(lang.errorServerUpload).show()
				}
			}), f.on("uploadError", function () {
			}), f.on("error", function (b, c) {
				("Q_TYPE_DENIED" == b || "F_EXCEED_SIZE" == b) && a(c)
			}), f.on("uploadComplete", function () {
			}), m.on("click", function () {
				return h(this).hasClass("disabled") ? !1 : void("ready" === v ? f.upload() : "paused" === v ? f.upload() : "uploading" === v && f.stop())
			}), m.addClass("state-" + v), void c()) : void h("#filePickerReady").after(h("<div>").html(lang.errorLoadConfig)).hide() : void h("#filePickerReady").after(h("<div>").html(lang.errorNotSupport)).hide()
		}, getQueueCount: function () {
			var a, b, c, d = 0, e = this.uploader.getFiles();
			for (b = 0; a = e[b++];)c = a.getStatus(), ("queued" == c || "uploading" == c || "progress" == c) && d++;
			return d
		}, refresh: function () {
			this.uploader.refresh()
		}
	}
}();