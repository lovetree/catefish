(function() {
	var m = this,
		aa = function(a) {
			var b = typeof a;
			if("object" == b)
				if(a) {
					if(a instanceof Array) return "array";
					if(a instanceof Object) return b;
					var c = Object.prototype.toString.call(a);
					if("[object Window]" == c) return "object";
					if("[object Array]" == c || "number" == typeof a.length && "undefined" != typeof a.splice && "undefined" != typeof a.propertyIsEnumerable && !a.propertyIsEnumerable("splice")) return "array";
					if("[object Function]" == c || "undefined" != typeof a.call && "undefined" != typeof a.propertyIsEnumerable && !a.propertyIsEnumerable("call")) return "function"
				} else return "null";
			else if("function" == b && "undefined" == typeof a.call) return "object";
			return b
		},
		ba = function(a) {
			return "number" == typeof a
		},
		ca = function(a) {
			var b = typeof a;
			return "object" == b && null != a || "function" == b
		},
		da = function(a, b, c) {
			return a.call.apply(a.bind, arguments)
		},
		ea = function(a, b, c) {
			if(!a) throw Error();
			if(2 < arguments.length) {
				var d = Array.prototype.slice.call(arguments, 2);
				return function() {
					var c = Array.prototype.slice.call(arguments);
					Array.prototype.unshift.apply(c, d);
					return a.apply(b, c)
				}
			}
			return function() {
				return a.apply(b,
					arguments)
			}
		},
		p = function(a, b, c) {
			p = Function.prototype.bind && -1 != Function.prototype.bind.toString().indexOf("native code") ? da : ea;
			return p.apply(null, arguments)
		},
		r = function(a, b) {
			var c = Array.prototype.slice.call(arguments, 1);
			return function() {
				var b = c.slice();
				b.push.apply(b, arguments);
				return a.apply(this, b)
			}
		},
		fa = Date.now || function() {
			return +new Date
		},
		t = function(a, b) {
			function c() {}
			c.prototype = b.prototype;
			a.ca = b.prototype;
			a.prototype = new c;
			a.prototype.constructor = a;
			a.Pa = function(a, c, e) {
				for(var d = Array(arguments.length -
						2), f = 2; f < arguments.length; f++) d[f - 2] = arguments[f];
				return b.prototype[c].apply(a, d)
			}
		};
	var ga = (new Date).getTime();
	var v = function(a) {
			a = parseFloat(a);
			return isNaN(a) || 1 < a || 0 > a ? 0 : a
		},
		z = function(a) {
			a = parseFloat(a);
			return isNaN(a) ? 0 : a
		},
		ha = function(a, b) {
			a = parseInt(a, 10);
			return isNaN(a) ? b : a
		},
		ia = function(a, b) {
			return /^true$/.test(a) ? !0 : /^false$/.test(a) ? !1 : b
		},
		ja = /^([\w-]+\.)*([\w-]{2,})(\:[0-9]+)?$/,
		ka = function(a, b) {
			return a ? (a = a.match(ja)) ? a[0] : b : b
		};
	var la = v("0.0"),
		ma = v("0.10"),
		na = v("0.05"),
		oa = ha("468", 0),
		qa = z("0.55"),
		ra = z("2.72"),
		sa = v("0.05"),
		ta = ha("468",
			0),
		ua = z("0.5"),
		va = z("2.65"),
		wa = ha("468", 0),
		xa = z("0.82"),
		ya = z("3.82"),
		za = z("0.96"),
		Aa = z("3.74"),
		Ba = v("0.02"),
		Ca = v("0.0"),
		Da = v("0.1"),
		Ea = v("0.02"),
		Fa = v("0.001"),
		Ga = v("0.01");
	var Ha = function() {
			return "r20161205"
		},
		Ia = ia("false", !1),
		Ja = ia("true", !1),
		Ka = ia("false", !1),
		La = Ka || !Ja,
		Ma = ia("true", !0);
	var B = document,
		C = window;
	var Na = String.prototype.trim ? function(a) {
			return a.trim()
		} : function(a) {
			return a.replace(/^[\s\xa0]+|[\s\xa0]+$/g, "")
		},
		Va = function(a) {
			if(!Oa.test(a)) return a; - 1 != a.indexOf("&") && (a = a.replace(Pa, "&amp;")); - 1 != a.indexOf("<") && (a = a.replace(Qa, "&lt;")); - 1 != a.indexOf(">") && (a = a.replace(Ra, "&gt;")); - 1 != a.indexOf('"') && (a = a.replace(Sa, "&quot;")); - 1 != a.indexOf("'") && (a = a.replace(Ta, "&#39;")); - 1 != a.indexOf("\x00") && (a = a.replace(Ua, "&#0;"));
			return a
		},
		Pa = /&/g,
		Qa = /</g,
		Ra = />/g,
		Sa = /"/g,
		Ta = /'/g,
		Ua = /\x00/g,
		Oa = /[\x00&<>"']/,
		Wa = {
			"\x00": "\\0",
			"\b": "\\b",
			"\f": "\\f",
			"\n": "\\n",
			"\r": "\\r",
			"\t": "\\t",
			"\x0B": "\\x0B",
			'"': '\\"',
			"\\": "\\\\",
			"<": "<"
		},
		Xa = {
			"'": "\\'"
		},
		Ya = function(a, b) {
			return a < b ? -1 : a > b ? 1 : 0
		},
		Za = function(a) {
			return String(a).replace(/\-([a-z])/g, function(a, c) {
				return c.toUpperCase()
			})
		};
	var $a = Array.prototype.forEach ? function(a, b, c) {
			Array.prototype.forEach.call(a, b, c)
		} : function(a, b, c) {
			for(var d = a.length, f = "string" == typeof a ? a.split("") : a, e = 0; e < d; e++) e in f && b.call(c, f[e], e, a)
		},
		ab = Array.prototype.map ? function(a, b, c) {
			return Array.prototype.map.call(a, b, c)
		} : function(a, b, c) {
			for(var d = a.length, f = Array(d), e = "string" == typeof a ? a.split("") : a, g = 0; g < d; g++) g in e && (f[g] = b.call(c, e[g], g, a));
			return f
		},
		bb = function(a) {
			return Array.prototype.concat.apply(Array.prototype, arguments)
		};
	var cb = function(a, b, c) {
		this.label = a;
		this.type = 4;
		this.eventId = b;
		this.value = c
	};
	var eb = function(a) {
			this.l = db();
			this.m = Math.random() < a;
			this.events = [];
			this.j = {}
		},
		db = m.performance && m.performance.now ? p(m.performance.now, m.performance) : fa;
	eb.prototype.install = function(a) {
		a = a || window;
		a.google_js_reporting_queue = a.google_js_reporting_queue || [];
		this.events = a.google_js_reporting_queue
	};
	var fb = function(a, b, c) {
			var d = db();
			c = c();
			d = db() - a.l - (d - a.l);
			if(a.m) {
				var f = a.j[b] || 0,
					e = f + 1;
				e > f && (a.j[b] = e);
				a.events.push(new cb(b, e, d))
			}
			return c
		},
		hb = function(a, b) {
			return p(function() {
				for(var c = [], d = 0; d < arguments.length; ++d) c[d] = arguments[d];
				return fb(this, a, function() {
					return b.apply(void 0, c)
				})
			}, gb)
		};
	var ib = function(a) {
		ib[" "](a);
		return a
	};
	ib[" "] = function() {};
	var kb = function(a, b) {
		var c = jb;
		Object.prototype.hasOwnProperty.call(c, a) || (c[a] = b(a))
	};
	var D = function(a) {
			try {
				var b;
				if(b = !!a && null != a.location.href) a: {
					try {
						ib(a.foo);
						b = !0;
						break a
					} catch(c) {}
					b = !1
				}
				return b
			} catch(c) {
				return !1
			}
		},
		lb = function(a, b) {
			return b.getComputedStyle ? b.getComputedStyle(a, null) : a.currentStyle
		},
		mb = function(a, b) {
			if(!(1E-4 > Math.random())) {
				var c = Math.random();
				if(c < b) {
					b = window;
					try {
						var d = new Uint32Array(1);
						b.crypto.getRandomValues(d);
						c = d[0] / 65536 / 65536
					} catch(f) {
						c = Math.random()
					}
					return a[Math.floor(c * a.length)]
				}
			}
			return null
		},
		nb = function(a, b) {
			for(var c in a) Object.prototype.hasOwnProperty.call(a,
				c) && b.call(void 0, a[c], c, a)
		},
		ob = function(a) {
			var b = a.length;
			if(0 == b) return 0;
			for(var c = 305419896, d = 0; d < b; d++) c ^= (c << 5) + (c >> 2) + a.charCodeAt(d) & 4294967295;
			return 0 < c ? c : 4294967296 + c
		},
		pb = /^([0-9.]+)px$/,
		qb = /^(-?[0-9.]{1,30})$/,
		rb = function(a) {
			return qb.test(a) && (a = Number(a), !isNaN(a)) ? a : null
		},
		sb = function(a) {
			return(a = pb.exec(a)) ? +a[1] : null
		};
	var tb = function(a, b) {
			this.j = a;
			this.l = b
		},
		ub = function(a, b, c) {
			this.url = a;
			this.j = b;
			this.M = !!c;
			this.depth = ba(void 0) ? void 0 : null
		};
	var vb = function() {
			var a = !1;
			try {
				var b = Object.defineProperty({}, "passive", {
					get: function() {
						a = !0
					}
				});
				window.addEventListener("test", null, b)
			} catch(c) {}
			return a
		}(),
		wb = function(a, b, c) {
			a.addEventListener ? a.addEventListener(b, c, vb ? void 0 : !1) : a.attachEvent && a.attachEvent("on" + b, c)
		},
		xb = function(a, b, c) {
			a.removeEventListener ? a.removeEventListener(b, c, vb ? void 0 : !1) : a.detachEvent && a.detachEvent("on" + b, c)
		};
	var yb = function(a, b, c, d, f) {
			this.w = c || 4E3;
			this.m = a || "&";
			this.A = b || ",$";
			this.o = void 0 !== d ? d : "trn";
			this.G = f || null;
			this.v = !1;
			this.l = {};
			this.F = 0;
			this.j = []
		},
		zb = function(a, b) {
			var c = {};
			c[a] = b;
			return [c]
		},
		F = function(a, b, c, d) {
			a.j.push(b);
			a.l[b] = zb(c, d)
		},
		Cb = function(a, b, c, d) {
			b = b + "//" + c + d;
			var f = Ab(a) - d.length - 0;
			if(0 > f) return "";
			a.j.sort(function(a, b) {
				return a - b
			});
			d = null;
			c = "";
			for(var e = 0; e < a.j.length; e++)
				for(var g = a.j[e], h = a.l[g], k = 0; k < h.length; k++) {
					if(!f) {
						d = null == d ? g : d;
						break
					}
					var l = Bb(h[k], a.m, a.A);
					if(l) {
						l = c + l;
						if(f >= l.length) {
							f -= l.length;
							b += l;
							c = a.m;
							break
						} else a.v && (c = f, l[c - 1] == a.m && --c, b += l.substr(0, c), c = a.m, f = 0);
						d = null == d ? g : d
					}
				}
			e = "";
			a.o && null != d && (e = c + a.o + "=" + (a.G || d));
			return b + e + ""
		},
		Ab = function(a) {
			if(!a.o) return a.w;
			var b = 1,
				c;
			for(c in a.l) b = c.length > b ? c.length : b;
			return a.w - a.o.length - b - a.m.length - 1
		},
		Bb = function(a, b, c, d, f) {
			var e = [];
			nb(a, function(a, h) {
				(a = Db(a, b, c, d, f)) && e.push(h + "=" + a)
			});
			return e.join(b)
		},
		Db = function(a, b, c, d, f) {
			if(null == a) return "";
			b = b || "&";
			c = c || ",$";
			"string" == typeof c && (c = c.split(""));
			if(a instanceof Array) {
				if(d = d || 0, d < c.length) {
					for(var e = [], g = 0; g < a.length; g++) e.push(Db(a[g], b, c, d + 1, f));
					return e.join(c[d])
				}
			} else if("object" == typeof a) return f = f || 0, 2 > f ? encodeURIComponent(Bb(a, b, c, d, f + 1)) : "...";
			return encodeURIComponent(String(a))
		};
	var Fb = function(a, b, c, d, f, e) {
			if((d ? a.v : Math.random()) < (f || a.j)) try {
				var g;
				c instanceof yb ? g = c : (g = new yb, nb(c, function(a, b) {
					var c = g,
						d = c.F++;
					a = zb(b, a);
					c.j.push(d);
					c.l[d] = a
				}));
				var h = Cb(g, a.o, a.l, a.m + b + "&");
				h && ("undefined" === typeof e ? Eb(h) : Eb(h, e))
			} catch(k) {}
		},
		Eb = function(a, b) {
			m.google_image_requests || (m.google_image_requests = []);
			var c = m.document.createElement("img");
			if(b) {
				var d = function(a) {
					b(a);
					xb(c, "load", d);
					xb(c, "error", d)
				};
				wb(c, "load", d);
				wb(c, "error", d)
			}
			c.src = a;
			m.google_image_requests.push(c)
		};
	var Gb = function(a, b, c) {
			this.v = a;
			this.A = b;
			this.m = c;
			this.l = null;
			this.w = this.j;
			this.o = !1
		},
		Hb = function(a, b, c) {
			this.message = a;
			this.j = b || "";
			this.l = c || -1
		},
		Jb = function(a, b, c, d, f, e) {
			var g;
			try {
				g = c()
			} catch(l) {
				var h = a.m;
				try {
					var k = Ib(l),
						h = (e || a.w).call(a, b, k, void 0, d)
				} catch(n) {
					a.j("pAR", n)
				}
				if(!h) throw l;
			} finally {
				if(f) try {
					f()
				} catch(l) {}
			}
			return g
		},
		Kb = function(a, b, c, d, f, e) {
			return function() {
				for(var g = [], h = 0; h < arguments.length; ++h) g[h] = arguments[h];
				return Jb(a, b, function() {
					return c.apply(void 0, g)
				}, d, f, e)
			}
		};
	Gb.prototype.j = function(a, b, c, d, f) {
		try {
			var e = f || this.A,
				g = new yb;
			g.v = !0;
			F(g, 1, "context", a);
			b instanceof Hb || (b = Ib(b));
			F(g, 2, "msg", b.message.substring(0, 512));
			b.j && F(g, 3, "file", b.j);
			0 < b.l && F(g, 4, "line", b.l.toString());
			b = {};
			if(this.l) try {
				this.l(b)
			} catch(u) {}
			if(d) try {
				d(b)
			} catch(u) {}
			d = [b];
			g.j.push(5);
			g.l[5] = d;
			var h;
			f = m;
			d = [];
			var k, l = null;
			do {
				b = f;
				D(b) ? (k = b.location.href, l = b.document && b.document.referrer || null) : (k = l, l = null);
				d.push(new ub(k || "", b));
				try {
					f = b.parent
				} catch(u) {
					f = null
				}
			} while (f && b != f);
			k = 0;
			for(var n =
					d.length - 1; k <= n; ++k) d[k].depth = n - k;
			b = m;
			if(b.location && b.location.ancestorOrigins && b.location.ancestorOrigins.length == d.length - 1)
				for(k = 1; k < d.length; ++k) {
					var q = d[k];
					q.url || (q.url = b.location.ancestorOrigins[k - 1] || "", q.M = !0)
				}
			for(var w = new ub(m.location.href, m, !1), x = d.length - 1, n = x; 0 <= n; --n) {
				var y = d[n];
				if(y.url && !y.M) {
					w = y;
					break
				}
			}
			var y = null,
				A = d.length && d[x].url;
			0 != w.depth && A && (y = d[x]);
			h = new tb(w, y);
			h.l && F(g, 6, "top", h.l.url || "");
			F(g, 7, "url", h.j.url || "");
			Fb(this.v, e, g, this.o, c)
		} catch(u) {
			try {
				Fb(this.v, e, {
					context: "ecmserr",
					rctx: a,
					msg: Lb(u),
					url: h.j.url
				}, this.o, c)
			} catch(O) {}
		}
		return this.m
	};
	var Ib = function(a) {
			return new Hb(Lb(a), a.fileName, a.lineNumber)
		},
		Lb = function(a) {
			var b = a.toString();
			a.name && -1 == b.indexOf(a.name) && (b += ": " + a.name);
			a.message && -1 == b.indexOf(a.message) && (b += ": " + a.message);
			if(a.stack) {
				a = a.stack;
				var c = b;
				try {
					-1 == a.indexOf(c) && (a = c + "\n" + a);
					for(var d; a != d;) d = a, a = a.replace(/((https?:\/..*\/)[^\/:]*:\d+(?:.|\n)*)\2/, "$1");
					b = a.replace(/\n */g, "\n")
				} catch(f) {
					b = c
				}
			}
			return b
		};
	var G;
	a: {
		var Mb = m.navigator;
		if(Mb) {
			var Nb = Mb.userAgent;
			if(Nb) {
				G = Nb;
				break a
			}
		}
		G = ""
	}
	var H = function(a) {
		return -1 != G.indexOf(a)
	};
	var Ob = H("Opera"),
		I = H("Trident") || H("MSIE"),
		Pb = H("Edge"),
		Qb = H("Gecko") && !(-1 != G.toLowerCase().indexOf("webkit") && !H("Edge")) && !(H("Trident") || H("MSIE")) && !H("Edge"),
		Rb = -1 != G.toLowerCase().indexOf("webkit") && !H("Edge"),
		Sb = function() {
			var a = m.document;
			return a ? a.documentMode : void 0
		},
		Tb;
	a: {
		var Ub = "",
			Vb = function() {
				var a = G;
				if(Qb) return /rv\:([^\);]+)(\)|;)/.exec(a);
				if(Pb) return /Edge\/([\d\.]+)/.exec(a);
				if(I) return /\b(?:MSIE|rv)[: ]([^\);]+)(\)|;)/.exec(a);
				if(Rb) return /WebKit\/(\S+)/.exec(a);
				if(Ob) return /(?:Version)[ \/]?(\S+)/.exec(a)
			}();Vb && (Ub = Vb ? Vb[1] : "");
		if(I) {
			var Wb = Sb();
			if(null != Wb && Wb > parseFloat(Ub)) {
				Tb = String(Wb);
				break a
			}
		}
		Tb = Ub
	}
	var Xb = Tb,
		jb = {},
		Yb = function(a) {
			kb(a, function() {
				for(var b = 0, c = Na(String(Xb)).split("."), d = Na(String(a)).split("."), f = Math.max(c.length, d.length), e = 0; 0 == b && e < f; e++) {
					var g = c[e] || "",
						h = d[e] || "";
					do {
						g = /(\d*)(\D*)(.*)/.exec(g) || ["", "", "", ""];
						h = /(\d*)(\D*)(.*)/.exec(h) || ["", "", "", ""];
						if(0 == g[0].length && 0 == h[0].length) break;
						b = Ya(0 == g[1].length ? 0 : parseInt(g[1], 10), 0 == h[1].length ? 0 : parseInt(h[1], 10)) || Ya(0 == g[2].length, 0 == h[2].length) || Ya(g[2], h[2]);
						g = g[3];
						h = h[3]
					} while (0 == b)
				}
				return 0 <= b
			})
		},
		Zb;
	var $b = m.document;
	Zb = $b && I ? Sb() || ("CSS1Compat" == $b.compatMode ? parseInt(Xb, 10) : 5) : void 0;
	var ac = H("Safari") && !((H("Chrome") || H("CriOS")) && !H("Edge") || H("Coast") || H("Opera") || H("Edge") || H("Silk") || H("Android")) && !(H("iPhone") && !H("iPod") && !H("iPad") || H("iPad") || H("iPod"));
	var bc = null,
		cc = null,
		dc = Qb || Rb && !ac || Ob || "function" == typeof m.btoa,
		ec = function(a, b) {
			var c;
			if(dc && !b) c = m.btoa(a);
			else {
				c = [];
				for(var d = 0, f = 0; f < a.length; f++) {
					for(var e = a.charCodeAt(f); 255 < e;) c[d++] = e & 255, e >>= 8;
					c[d++] = e
				}
				if(!bc)
					for(bc = {}, cc = {}, a = 0; 65 > a; a++) bc[a] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".charAt(a), cc[a] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.".charAt(a);
				b = b ? cc : bc;
				a = [];
				for(d = 0; d < c.length; d += 3) {
					var g = c[d],
						h = (f = d + 1 < c.length) ? c[d + 1] : 0,
						k = (e =
							d + 2 < c.length) ? c[d + 2] : 0,
						l = g >> 2,
						g = (g & 3) << 4 | h >> 4,
						h = (h & 15) << 2 | k >> 6,
						k = k & 63;
					e || (k = 64, f || (h = 64));
					a.push(b[l], b[g], b[h], b[k])
				}
				c = a.join("")
			}
			return c
		};
	var fc = function(a) {
		return eval("(" + a + ")")
	};
	var J = function() {},
		gc = "function" == typeof Uint8Array,
		ic = function(a, b, c) {
			a.j = null;
			b || (b = []);
			a.A = void 0;
			a.o = -1;
			a.l = b;
			a: {
				if(a.l.length) {
					b = a.l.length - 1;
					var d = a.l[b];
					if(d && "object" == typeof d && "array" != aa(d) && !(gc && d instanceof Uint8Array)) {
						a.v = b - a.o;
						a.m = d;
						break a
					}
				}
				a.v = Number.MAX_VALUE
			}
			a.w = {};
			if(c)
				for(b = 0; b < c.length; b++) d = c[b], d < a.v ? (d += a.o, a.l[d] = a.l[d] || hc) : a.m[d] = a.m[d] || hc
		},
		hc = [],
		K = function(a, b) {
			if(b < a.v) {
				b += a.o;
				var c = a.l[b];
				return c === hc ? a.l[b] = [] : c
			}
			c = a.m[b];
			return c === hc ? a.m[b] = [] : c
		},
		jc = function(a,
			b, c) {
			a.j || (a.j = {});
			if(!a.j[c]) {
				var d = K(a, c);
				d && (a.j[c] = new b(d))
			}
			return a.j[c]
		},
		kc = function(a) {
			if(a.j)
				for(var b in a.j) {
					var c = a.j[b];
					if("array" == aa(c))
						for(var d = 0; d < c.length; d++) c[d] && kc(c[d]);
					else c && kc(c)
				}
		};
	J.prototype.toString = function() {
		kc(this);
		return this.l.toString()
	};
	var lc;
	if(!(lc = !Qb && !I)) {
		var mc;
		if(mc = I) mc = 9 <= Number(Zb);
		lc = mc
	}
	lc || Qb && Yb("1.9.1");
	I && Yb("9");
	var nc = Object.prototype.hasOwnProperty,
		oc = function(a, b) {
			for(var c in a) nc.call(a, c) && b.call(void 0, a[c], c, a)
		},
		pc = function(a) {
			return !(!a || !a.call) && "function" === typeof a
		},
		qc = function(a, b) {
			for(var c = 1, d = arguments.length; c < d; ++c) a.push(arguments[c])
		},
		L = function(a, b) {
			if(a.indexOf) return a = a.indexOf(b), 0 < a || 0 === a;
			for(var c = 0; c < a.length; c++)
				if(a[c] === b) return !0;
			return !1
		},
		rc = function(a) {
			a.google_unique_id ? ++a.google_unique_id : a.google_unique_id = 1
		},
		sc = !!window.google_async_iframe_id,
		tc = sc && window.parent ||
		window,
		uc = /(^| )adsbygoogle($| )/,
		vc = {
			"http://googleads.g.doubleclick.net": !0,
			"http://pagead2.googlesyndication.com": !0,
			"https://googleads.g.doubleclick.net": !0,
			"https://pagead2.googlesyndication.com": !0
		},
		wc = /\.google\.com(:\d+)?$/,
		xc = function(a) {
			a = Ia && a.google_top_window || a.top;
			return D(a) ? a : null
		};
	var yc, M, gb = new eb(1);
	yc = new function() {
		this.o = "http:" === C.location.protocol ? "http:" : "https:";
		this.l = "pagead2.googlesyndication.com";
		this.m = "/pagead/gen_204?id=";
		this.j = .01;
		this.v = Math.random()
	};
	M = new Gb(yc, "jserror", !0);
	gb.install(function() {
		if(sc && !D(tc)) {
			var a = "." + B.domain;
			try {
				for(; 2 < a.split(".").length && !D(tc);) B.domain = a = a.substr(a.indexOf(".") + 1), tc = window.parent
			} catch(b) {}
			D(tc) || (tc = window)
		}
		return tc
	}());
	var Bc = function() {
			var a = [zc, Ac];
			M.l = function(b) {
				$a(a, function(a) {
					a(b)
				})
			}
		},
		Dc = function(a, b, c, d) {
			Cc(a, c, d, void 0, b)()
		},
		Cc = function(a, b, c, d, f) {
			a = a.toString();
			return Kb(M, a, hb(a, b), c, d, f)
		},
		Ec = M.j,
		Fc = function(a, b, c, d) {
			return Cc(a.toString(), b, c, d)
		},
		Gc = function(a, b, c, d) {
			return Cc(a.toString(), b, c, d)
		};
	var Hc = function(a, b, c) {
			Dc("files::getSrc", Ec, function() {
				if("https:" == C.location.protocol && "http" == c) throw c = "https", Error("Requested url " + a + b);
			});
			return [c, "://", a, b].join("")
		},
		Ic = function(a, b, c) {
			c || (c = La ? "https" : "http");
			return Hc(a, b, c)
		};
	var Kc = function(a) {
		ic(this, a, Jc)
	};
	t(Kc, J);
	var Jc = [4],
		Lc = function(a) {
			ic(this, a, null)
		};
	t(Lc, J);
	var Mc = function(a) {
		ic(this, a, null)
	};
	t(Mc, J);
	var Oc = function(a) {
		ic(this, a, Nc)
	};
	t(Oc, J);
	var Nc = [6, 7, 9];
	var Qc = function(a) {
		ic(this, a, Pc)
	};
	t(Qc, J);
	var Pc = [1, 2, 5],
		Rc = function(a) {
			ic(this, a, null)
		};
	t(Rc, J);
	var Sc = {
		google_tag_origin: "qs"
	};
	var Tc = null,
		Uc = function() {
			if(!Tc) {
				for(var a = window, b = a, c = 0; a && a != a.parent;)
					if(a = a.parent, c++, D(a)) b = a;
					else break;
				Tc = b
			}
			return Tc
		};
	var Vc = {
		overlays: 1,
		interstitials: 2,
		vignettes: 2,
		inserts: 3,
		immersives: 4,
		list_view: 5,
		full_page: 6
	};
	var Wc = function(a) {
		a = a.document;
		return("CSS1Compat" == a.compatMode ? a.documentElement : a.body) || {}
	};
	var Xc = function() {
		return !(H("iPad") || H("Android") && !H("Mobile") || H("Silk")) && (H("iPod") || H("iPhone") || H("Android") || H("IEMobile"))
	};
	var Yc = function(a) {
		var b = ["adsbygoogle-placeholder"];
		a = a.className ? a.className.split(/\s+/) : [];
		for(var c = {}, d = 0; d < a.length; ++d) c[a[d]] = !0;
		for(d = 0; d < b.length; ++d)
			if(!c[b[d]]) return !1;
		return !0
	};
	var Zc = function(a, b) {
		for(var c = 0; c < b.length; c++) {
			var d = b[c],
				f = Za(d.Qa);
			a[f] = d.value
		}
	};
	var $c = function(a, b) {
		var c = a.length;
		if(null != c)
			for(var d = 0; d < c; d++) b.call(void 0, a[d], d)
	};
	var ad = function(a, b, c, d) {
			this.o = a;
			this.l = b;
			this.m = c;
			this.j = d
		},
		bd = function(a, b) {
			if(null == a.j) return b;
			switch(a.j) {
				case 1:
					return b.slice(1);
				case 2:
					return b.slice(0, b.length - 1);
				case 3:
					return b.slice(1, b.length - 1);
				case 0:
					return b;
				default:
					throw Error("Unknown ignore mode: " + a.j);
			}
		},
		dd = function(a) {
			var b = [];
			$c(a.getElementsByTagName("p"), function(a) {
				100 <= cd(a) && b.push(a)
			});
			return b
		},
		cd = function(a) {
			if(3 == a.nodeType) return a.length;
			if(1 != a.nodeType || "SCRIPT" == a.tagName) return 0;
			var b = 0;
			$c(a.childNodes, function(a) {
				b +=
					cd(a)
			});
			return b
		},
		ed = function(a) {
			return 0 == a.length || isNaN(a[0]) ? a : "\\" + (30 + parseInt(a[0], 10)) + " " + a.substring(1)
		};
	var fd = {
			1: 1,
			2: 2,
			3: 3,
			0: 0
		},
		gd = function(a) {
			return null != a ? fd[a] : a
		},
		hd = {
			1: 0,
			2: 1,
			3: 2,
			4: 3
		};
	var id = function(a, b) {
			if(!a) return !1;
			a = lb(a, b);
			if(!a) return !1;
			a = a.cssFloat || a.styleFloat;
			return "left" == a || "right" == a
		},
		jd = function(a) {
			for(a = a.previousSibling; a && 1 != a.nodeType;) a = a.previousSibling;
			return a ? a : null
		},
		kd = function(a) {
			return !!a.nextSibling || !!a.parentNode && kd(a.parentNode)
		};
	var md = function() {
			this.l = new ld(this);
			this.j = 0
		},
		nd = function(a) {
			if(0 != a.j) throw Error("Already resolved/rejected.");
		},
		ld = function(a) {
			this.j = a
		};
	ld.prototype.then = function(a, b) {
		if(this.l) throw Error("Then functions already set.");
		this.l = a;
		this.m = b;
		od(this)
	};
	var od = function(a) {
		switch(a.j.j) {
			case 0:
				break;
			case 1:
				a.l && a.l(a.j.o);
				break;
			case 2:
				a.m && a.m(a.j.m);
				break;
			default:
				throw Error("Unhandled deferred state.");
		}
	};
	var pd = function(a) {
			this.exception = a
		},
		qd = function(a, b, c, d) {
			this.l = a;
			this.m = b;
			this.v = c;
			this.j = d
		};
	qd.prototype.start = function() {
		this.o()
	};
	qd.prototype.o = function() {
		try {
			switch(this.l.document.readyState) {
				case "complete":
				case "interactive":
					rd(this.m, !0);
					sd(this);
					break;
				default:
					var a = this.m;
					rd(a, !1);
					a.l ? sd(this) : this.l.setTimeout(p(this.o, this), this.v)
			}
		} catch(b) {
			sd(this, b)
		}
	};
	var sd = function(a, b) {
		try {
			var c = a.j,
				d = new pd(b);
			nd(c);
			c.j = 1;
			c.o = d;
			od(c.l)
		} catch(f) {
			a = a.j, nd(a), a.j = 2, a.m = f, od(a.l)
		}
	};
	var td = function(a, b) {
		a.location.href && a.location.href.substring && (b.url = a.location.href.substring(0, 200));
		Fb(yc, "ama", b, !0, .01, void 0)
	};
	var ud = function(a, b) {
			this.j = m;
			this.v = a;
			this.m = b;
			this.o = Sc;
			this.l = !1
		},
		rd = function(a, b) {
			if(!a.l) {
				var c;
				c = a.m;
				c.j || (c.j = {});
				if(!c.j[1]) {
					for(var d = K(c, 1), f = [], e = 0; e < d.length; e++) f[e] = new Oc(d[e]);
					c.j[1] = f
				}
				d = c.j[1];
				d == hc && (d = c.j[1] = []);
				c = d;
				for(d = 0; d < c.length; d++)
					if(f = c[d], 1 == K(f, 8) && (e = jc(f, Mc, 4)) && 2 == K(e, 1) && vd(a, f, b)) {
						a.l = !0;
						(window.google_ama_state = window.google_ama_state || {}).placement = d;
						break
					}
			}
		},
		vd = function(a, b, c) {
			var d, f;
			if(1 != K(b, 8)) return !1;
			var e = jc(b, Kc, 1);
			if(!e) return !1;
			var g;
			g = K(e, 7);
			if(K(e,
					1) || K(e, 3) || 0 < K(e, 4).length) {
				var h = K(e, 3),
					k = K(e, 1),
					l = K(e, 4);
				g = K(e, 2);
				d = K(e, 5);
				f = gd(K(e, 6));
				var n = "";
				k && (n += k);
				h && (n += "#" + ed(h));
				if(l)
					for(h = 0; h < l.length; h++) n += "." + ed(l[h]);
				g = (l = n) ? new ad(l, g, d, f) : null
			} else g = g ? new ad(g, K(e, 2), K(e, 5), gd(K(e, 6))) : null;
			if(!g) return !1;
			d = [];
			try {
				d = a.j.document.querySelectorAll(g.o)
			} catch(x) {}
			if(d.length) {
				f = d.length;
				if(0 < f) {
					l = Array(f);
					for(n = 0; n < f; n++) l[n] = d[n];
					d = l
				} else d = [];
				ba(g.l) && (f = g.l, 0 > f && (f += d.length), d = 0 <= f && f < d.length ? [d[f]] : []);
				if(ba(g.m)) {
					f = [];
					for(l = 0; l < d.length; l++) n =
						dd(d[l]), h = g.m, 0 > h && (h += n.length), 0 <= h && h < n.length && f.push(n[h]);
					d = f
				}
				g = d = bd(g, d)
			} else g = [];
			if(0 == g.length) return !1;
			g = g[0];
			d = K(e, 2);
			e = K(b, 2);
			e = hd[e];
			e = void 0 !== e ? e : null;
			if(!(f = null == e)) {
				a: {
					f = a.j;
					if(null != d) switch(e) {
						case 0:
							d = id(jd(g), f);
							break a;
						case 3:
							d = id(g, f);
							break a;
						case 2:
							d = g.lastChild;
							d = id(d ? 1 == d.nodeType ? d : jd(d) : null, f);
							break a
					}
					d = !1
				}
				if(c = !d && !(!c && 2 == e && !kd(g))) c = 1 == e || 2 == e ? g : g.parentNode,
				c = !(c && (1 != c.nodeType || "INS" != c.tagName || !Yc(c)) && 0 >= c.offsetWidth);f = !c
			}
			if(f) return !1;
			b = jc(b, Lc, 3);
			d = {};
			b && (d.O = K(b, 1), d.L = K(b, 2), d.Z = !!K(b, 3));
			b = a.j;
			c = a.o;
			a = a.v;
			l = b.document;
			f = l.createElement("div");
			n = f.style;
			n.textAlign = "center";
			n.width = "100%";
			n.height = "auto";
			n.clear = d.Z ? "both" : "none";
			d.aa && Zc(n, d.aa);
			l = l.createElement("ins");
			n = l.style;
			n.display = "block";
			n.margin = "auto";
			n.backgroundColor = "transparent";
			d.O && (n.marginTop = d.O);
			d.L && (n.marginBottom = d.L);
			d.Y && Zc(n, d.Y);
			f.appendChild(l);
			d = f;
			f = l;
			f.setAttribute("data-ad-format", "auto");
			l = [];
			d.className = "googlepublisherpluginad";
			n = f;
			n.className = "adsbygoogle";
			n.setAttribute("data-ad-client", a);
			l.length && n.setAttribute("data-ad-channel", l.join("+"));
			var q;
			a: {
				try {
					a = d;
					switch(e) {
						case 0:
							g.parentNode && g.parentNode.insertBefore(a, g);
							break;
						case 3:
							if(q = g.parentNode) {
								var w = g.nextSibling;
								if(w && w.parentNode != q)
									for(; w && 8 == w.nodeType;) w = w.nextSibling;
								q.insertBefore(a, w)
							}
							break;
						case 1:
							g.insertBefore(a, g.firstChild);
							break;
						case 2:
							g.appendChild(a)
					}
					if(1 != g.nodeType ? 0 : "INS" == g.tagName && Yc(g)) g.style.display = "block";
					q = f;
					q.setAttribute("data-adsbygoogle-status", "reserved");
					q = {
						element: q
					};
					c && (q.params = c);
					(b.adsbygoogle = b.adsbygoogle || []).push(q)
				} catch(x) {
					(q = d) && q.parentNode && q.parentNode.removeChild(q);
					q = !1;
					break a
				}
				q = !0
			}
			return q ? !0 : !1
		};
	var wd = function(a) {
			td(a, {
				atf: 1
			})
		},
		xd = function(a, b) {
			(a.google_ama_state = a.google_ama_state || {}).exception = b;
			td(a, {
				atf: 0
			})
		};
	var yd = function() {
			this.wasPlaTagProcessed = !1;
			this.wasReactiveAdConfigReceived = {};
			this.wasReactiveAdCreated = {};
			this.wasReactiveAdVisible = {};
			this.stateForType = {};
			this.reactiveTypeEnabledByReactiveTag = {};
			this.isReactiveTagFirstOnPage = this.wasReactiveAdConfigHandlerRegistered = this.wasReactiveTagRequestSent = !1;
			this.reactiveTypeDisabledByPublisher = {};
			this.debugCard = null;
			this.messageValidationEnabled = this.floatingNmoOrientationExperimentAndEligible = this.floatingNmoOrientationExperiment = this.debugCardRequested = !1;
			this.floatingAdsFillMessage = this.grappleTagStatusService = null
		},
		zd = function(a) {
			a.google_reactive_ads_global_state || (a.google_reactive_ads_global_state = new yd);
			return a.google_reactive_ads_global_state
		};
	var Ad = !1;
	var Bd = {
			s: "388900710",
			C: "388900711",
			B: "388900712",
			D: "388900713"
		},
		Cd = {
			s: "10583695",
			u: "10583696"
		},
		Dd = {
			s: "10573695",
			u: "10573696"
		},
		Ed = {
			s: "4089030",
			u: "4089031"
		},
		Fd = {
			s: "4089035",
			u: "4089036"
		},
		N = {
			ja: {},
			Na: {
				s: "453848102",
				u: "453848103"
			},
			La: {
				s: "137237720",
				u: "137237721"
			},
			ua: {
				s: "24819308",
				u: "24819309",
				ga: "24819320",
				la: "24819321"
			},
			ta: {
				s: "24819330",
				u: "24819331"
			},
			qa: {
				s: "86724438",
				u: "86724439"
			},
			V: {
				s: "388900700",
				C: "388900701",
				B: "388900702",
				D: "388900703"
			},
			ka: {
				s: "26835105",
				u: "26835106"
			},
			I: {
				s: "20040000",
				u: "20040001"
			},
			ha: {
				s: "20040016",
				u: "20040017"
			},
			ia: {
				da: "314159230",
				sa: "314159231"
			},
			ra: {
				Aa: "27285692",
				Ca: "27285712",
				Ba: "27285713"
			},
			wa: {
				s: "27415010",
				u: "27415011"
			},
			oa: {
				s: "90091300",
				u: "90091301",
				na: "90091302",
				ma: "90091303"
			},
			W: {
				s: "62710000",
				u: "62710001"
			},
			X: {
				s: "62710002",
				u: "62710003"
			},
			xa: {
				s: "20040060",
				va: "20040061",
				pa: "20040062"
			},
			ya: {
				Ja: 389613E3,
				Ka: 389613001,
				Ha: 389613002,
				Ia: 389613003,
				Fa: 389613004,
				Ga: 389613005,
				Da: 389613006,
				Ea: 389613007
			},
			ea: {
				s: "20040063",
				u: "20040064"
			},
			J: {
				s: "62710010",
				VIEWPORT: "62710011",
				H: "62710012"
			},
			Oa: {
				s: "20040065",
				u: "20040066"
			}
		},
		Ad = !1;
	var P = function(a) {
		this.name = "TagError";
		this.message = a ? "adsbygoogle.push() error: " + a : "";
		Error.captureStackTrace ? Error.captureStackTrace(this, P) : this.stack = Error().stack || ""
	};
	t(P, Error);
	var R = function(a, b) {
		this.o = a;
		this.m = b
	};
	R.prototype.minWidth = function() {
		return this.o
	};
	R.prototype.height = function() {
		return this.m
	};
	R.prototype.j = function(a) {
		return 300 < a && 300 < this.m ? this.o : Math.min(1200, Math.round(a))
	};
	R.prototype.l = function(a) {
		return this.j(a) + "x" + this.height()
	};
	var Gd = {
			rectangle: 1,
			horizontal: 2,
			vertical: 4
		},
		S = function(a, b, c) {
			R.call(this, a, b);
			this.ba = c
		};
	t(S, R);
	var Hd = function(a) {
			return function(b) {
				return !!(b.ba & a)
			}
		},
		T = [new S(970, 90, 2), new S(728, 90, 2), new S(468, 60, 2), new S(336, 280, 1), new S(320, 100, 2), new S(320, 50, 2), new S(300, 600, 4), new S(300, 250, 1), new S(250, 250, 1), new S(234, 60, 2), new S(200, 200, 1), new S(180, 150, 1), new S(160, 600, 4), new S(125, 125, 1), new S(120, 600, 4), new S(120, 240, 4)],
		Id = [T[6], T[12], T[3], T[0], T[7], T[14], T[1], T[8], T[10], T[4], T[15], T[2], T[11], T[5], T[13], T[9]];
	var Jd = function(a, b) {
			for(var c = ["width", "height"], d = 0; d < c.length; d++) {
				var f = "google_ad_" + c[d];
				if(!b.hasOwnProperty(f)) {
					var e;
					e = sb(a[c[d]]);
					e = null === e ? null : Math.round(e);
					null != e && (b[f] = e)
				}
			}
		},
		Kd = function(a, b) {
			do {
				var c = lb(a, b);
				if(c && "fixed" == c.position) return !1
			} while (a = a.parentElement);
			return !0
		},
		Ld = function(a) {
			var b = 0,
				c;
			for(c in Gd) - 1 != a.indexOf(c) && (b |= Gd[c]);
			return b
		};
	var Md = function(a, b, c) {
			if(a.style) {
				var d = sb(a.style[c]);
				if(d) return d
			}
			if(a = lb(a, b))
				if(d = sb(a[c])) return d;
			return null
		},
		Nd = function(a) {
			return function(b) {
				return b.minWidth() <= a
			}
		},
		Pd = function(a, b, c) {
			var d = a && Od(c, b);
			return function(a) {
				return !(d && 250 <= a.height())
			}
		},
		Od = function(a, b) {
			var c;
			try {
				var d = b.document.documentElement.getBoundingClientRect(),
					f = a.getBoundingClientRect();
				c = {
					x: f.left - d.left,
					y: f.top - d.top
				}
			} catch(e) {
				c = null
			}
			return(c ? c.y : 0) < Wc(b).clientHeight - 100
		},
		Qd = function(a, b) {
			var c = Infinity;
			do {
				var d =
					Md(b, a, "height");
				d && (c = Math.min(c, d));
				(d = Md(b, a, "maxHeight")) && (c = Math.min(c, d))
			} while ((b = b.parentElement) && "HTML" != b.tagName);
			return c
		};
	var Rd = function(a) {
			return(a = a.google_ad_modifications) ? a.eids || [] : []
		},
		Sd = function(a) {
			return(a = a.google_ad_modifications) ? a.loeids || [] : []
		},
		Td = function(a, b, c) {
			if(!a) return null;
			for(var d = 0; d < a.length; ++d)
				if((a[d].ad_slot || b) == b && (a[d].ad_tag_origin || c) == c) return a[d];
			return null
		};
	var Ud = function(a) {
			return function(b) {
				for(var c = a.length - 1; 0 <= c; --c)
					if(!a[c](b)) return !1;
				return !0
			}
		},
		Vd = function(a, b, c) {
			for(var d = a.length, f = null, e = 0; e < d; ++e) {
				var g = a[e];
				if(b(g)) {
					if(!c || c(g)) return g;
					null === f && (f = g)
				}
			}
			return f
		};
	var U = function(a, b) {
		this.j = a;
		this.l = b
	};
	var Yd = function(a, b, c, d, f) {
			var e = "auto" == b ? .25 >= a / Math.min(1200, Wc(c).clientWidth) ? 4 : 3 : Ld(b);
			f = f || {};
			f.google_responsive_formats = e;
			f = Xc() && !Od(d, c) && (f.google_safe_for_responsive_override = Kd(d, c));
			var g = (f ? Id : T).slice(0);
			(300 > a || 450 < a ? 0 : L(Rd(c), Bd.C) || L(Rd(c), Bd.B)) && g.unshift(new S(a, Math.floor(.8334 * a + 20), 1));
			var h = 488 > Wc(c).clientWidth,
				h = [Nd(a), Wd(h), Pd(h, c, d), Hd(e)],
				k = [];
			if(f) {
				var l = Qd(c, d);
				k.push(function(a) {
					return a.height() <= l
				})
			}
			c = Vd(g, Ud(h), Ud(k));
			if(!c) throw new P("No slot size for availableWidth=" +
				a);
			return new U(Xd(b, e), c)
		},
		Xd = function(a, b) {
			if("auto" == a) return 1;
			switch(b) {
				case 2:
					return 2;
				case 1:
					return 3;
				case 4:
					return 4;
				case 3:
					return 5;
				case 6:
					return 6;
				case 5:
					return 7;
				case 7:
					return 8
			}
			throw Error("bad mask");
		},
		Wd = function(a) {
			return function(b) {
				return !(320 == b.minWidth() && (a && 50 == b.height() || !a && 100 == b.height()))
			}
		};
	var Zd = {
			Ma: "google_content_recommendation_ui_type",
			fa: "google_content_recommendation_columns_num",
			za: "google_content_recommendation_rows_num"
		},
		V = function(a, b) {
			R.call(this, a, b)
		};
	t(V, R);
	V.prototype.j = function(a) {
		return Math.min(1200, Math.round(a))
	};
	var $d = function(a) {
			var b = 0;
			oc(Zd, function(c) {
				null != a[c] && ++b
			});
			if(0 == b) return !1;
			if(3 == b) return !0;
			throw new P("Tags data-matched-content-ui-type, data-matched-content-columns-num and data-matched-content-rows-num should be set together.");
		},
		ae = function(a) {
			switch(a) {
				case "image_stacked":
					return xa;
				case "image_sidebyside":
					return ya;
				case "image_card_stacked":
					return za;
				case "image_card_sidebyside":
					return Aa;
				default:
					throw new P("Unrecognized layout: " + a);
			}
		};
	var W = function(a, b) {
		R.call(this, a, b)
	};
	t(W, R);
	W.prototype.j = function() {
		return this.minWidth()
	};
	W.prototype.l = function(a) {
		return W.ca.l.call(this, a) + "_0ads_al"
	};
	var be = [new W(728, 15), new W(468, 15), new W(200, 90), new W(180, 90), new W(160, 90), new W(120, 90)],
		ce = function(a) {
			var b = Vd(be, Nd(a));
			if(!b) throw new P("No link unit size for width=" + a + "px");
			return b
		};
	var de = function(a, b) {
			var c = b.google_ad_format;
			if("autorelaxed" == c) return L(Rd(a), Cd.u) ? 6 : L(Rd(a), Dd.u) ? 7 : $d(b) ? 9 : 5;
			if("auto" == c || /^((^|,) *(horizontal|vertical|rectangle) *)+$/.test(c)) return 1;
			if("link" == c) return L(Rd(a), Ed.u) ? 10 : L(Rd(a), Fd.u) ? 4 : 12;
			if("fluid" == c) return 8
		},
		ee = function(a, b, c, d, f) {
			var e = d.google_ad_format;
			switch(a) {
				default:
					case 1:
					return Yd(b, e, f, c, d);
				case 5:
						return new U(9, 1200 <= b ? new V(1200, 600) : 850 <= b ? new V(b, Math.floor(.5 * b)) : 550 <= b ? new V(b, Math.floor(.6 * b)) : 468 <= b ? new V(b, Math.floor(.7 *
						b)) : new V(b, Math.floor(3.44 * b)));
				case 6:
						return b >= oa ? new U(9, new V(b, Math.floor(b * qa))) : new U(9, new V(b, Math.floor(b * ra)));
				case 7:
						return b >= ta ? new U(9, new V(b, Math.floor(b * ua))) : new U(9, new V(b, Math.floor(b * va)));
				case 9:
						f = d.google_content_recommendation_ui_type.split(",");e = ab(d.google_content_recommendation_columns_num.split(","), Number);a = ab(d.google_content_recommendation_rows_num.split(","), Number);a: {
						if(f.length == e.length && e.length == a.length) {
							if(1 == f.length) {
								c = 0;
								break a
							}
							if(2 == f.length) {
								c = b < wa ?
									0 : 1;
								break a
							}
							throw new P("The size of element (" + f.length + ") specified in tag data-matched-content-ui-type, data-matched-content-columns-num, data-matched-content-rows-num is wrong.");
						}
						throw new P("The size of element (" + f.length + "," + e.length + "," + a.length + ") specified in tag data-matched-content-ui-type, data-matched-content-columns-num and data-matched-content-rows-num does not match.");
					}
					f = Na(f[c]);e = e[c];a = a[c];d.google_content_recommendation_ui_type = f;d.google_content_recommendation_columns_num =
					e;d.google_content_recommendation_rows_num = a;d = ae(f);
					if(isNaN(e) || 0 == e) throw new P("Wrong value for data-matched-content-columns-num");
					if(isNaN(a) || 0 == a) throw new P("Wrong value for data-matched-content-rows-num");d = Math.floor((b - 8 * e - 8) / e / d * a + 8 * a + 8);
					if(1500 < b) throw new P("Calculated slot width is too large: " + b);
					if(1500 < d) throw new P("Calculated slot height is too large: " + d);
					return new U(9, new V(b, d));
				case 4:
						return new U(10, ce(b));
				case 10:
						return d = ce(b), new U(10, new W(Math.min(b, 1200), d.height()));
				case 12:
						return d = Qd(f, c), a = ce(b), b = Math.min(b, 1200), a = a.height(), d = Math.max(a, d), new U(10, new W(b, Math.min(d, 15 == a ? 30 : 130)));
				case 8:
						if(250 > b) throw new P("Fluid responsive ads must be at least 250px wide: availableWidth=" + b);b = Math.min(1200, Math.round(b));
					return new U(11, new R(b, 300 >= b ? 150 : 600 >= b ? 250 : 350))
			}
		};
	var fe = {
			'"': '\\"',
			"\\": "\\\\",
			"/": "\\/",
			"\b": "\\b",
			"\f": "\\f",
			"\n": "\\n",
			"\r": "\\r",
			"\t": "\\t",
			"\x0B": "\\u000b"
		},
		ge = /\uffff/.test("\uffff") ? /[\\\"\x00-\x1f\x7f-\uffff]/g : /[\\\"\x00-\x1f\x7f-\xff]/g,
		he = function() {},
		je = function(a, b, c) {
			switch(typeof b) {
				case "string":
					ie(b, c);
					break;
				case "number":
					c.push(isFinite(b) && !isNaN(b) ? String(b) : "null");
					break;
				case "boolean":
					c.push(String(b));
					break;
				case "undefined":
					c.push("null");
					break;
				case "object":
					if(null == b) {
						c.push("null");
						break
					}
					if(b instanceof Array || void 0 !=
						b.length && b.splice) {
						var d = b.length;
						c.push("[");
						for(var f = "", e = 0; e < d; e++) c.push(f), je(a, b[e], c), f = ",";
						c.push("]");
						break
					}
					c.push("{");
					d = "";
					for(f in b) b.hasOwnProperty(f) && (e = b[f], "function" != typeof e && (c.push(d), ie(f, c), c.push(":"), je(a, e, c), d = ","));
					c.push("}");
					break;
				case "function":
					break;
				default:
					throw Error("Unknown type: " + typeof b);
			}
		},
		ie = function(a, b) {
			b.push('"');
			b.push(a.replace(ge, function(a) {
				if(a in fe) return fe[a];
				var b = a.charCodeAt(0),
					c = "\\u";
				16 > b ? c += "000" : 256 > b ? c += "00" : 4096 > b && (c += "0");
				return fe[a] =
					c + b.toString(16)
			}));
			b.push('"')
		};
	var ke = {
			google_ad_modifications: !0,
			google_analytics_domain_name: !0,
			google_analytics_uacct: !0
		},
		le = function(a) {
			a.google_page_url && (a.google_page_url = String(a.google_page_url));
			var b = [];
			oc(a, function(a, d) {
				if(null != a) {
					var c;
					try {
						var e = [];
						je(new he, a, e);
						c = e.join("")
					} catch(g) {}
					c && (c = c.replace(/\//g, "\\$&"), qc(b, d, "=", c, ";"))
				}
			});
			return b.join("")
		};
	var me = null,
		re = function() {
			var a = window,
				b = ne;
			if(void 0 === a.google_dre) {
				var c = "";
				a.postMessage && xc(a) && Xc() && (c = mb(["20050000", "20050001"], Ca)) && (a.google_ad_modifications = a.google_ad_modifications || {}, a.google_ad_modifications.eids = a.google_ad_modifications.eids || [], a.google_ad_modifications.eids.push(c));
				a.google_dre = c;
				"20050001" == a.google_dre && (me = Gc("dr::mh", r(oe, a, b), void 0, r(pe, a)), wb(a.top, "message", me), b = Fc("dr::to", r(qe, a, !0, b), void 0, r(pe, a)), a.setTimeout(b, 2E3), a.google_drc = 0, a.google_q =
					a.google_q || {}, a.google_q.tags = a.google_q.tags || [])
			}
		},
		se = function(a) {
			"20050001" == m.google_dre && (a.params = a.params || {}, a.params.google_delay_requests_delay = 0, a.params.google_delay_requests_count = m.google_drc++, a.$ = fa())
		},
		te = function(a) {
			if("20050001" == m.google_dre) {
				var b = fa() - a.$;
				a.params.google_delay_requests_delay = b
			}
		},
		oe = function(a, b, c) {
			var d;
			if(d = c && "afb" == c.data) c = c.origin, d = !!vc[c] || Ia && wc.test(c);
			d && qe(a, !1, b)
		},
		pe = function(a) {
			a.google_q.tags = void 0
		},
		qe = function(a, b, c) {
			if(a.google_q && a.google_q.tags) {
				var d =
					a.google_q.tags;
				pe(a);
				d.length && (b && Fb(yc, "drt", {
					Ra: d.length,
					duration: 2E3
				}, !0, 1, void 0), c(d))
			}
		};
	var Ac = function(a) {
			try {
				var b = m.google_ad_modifications;
				if(null != b) {
					var c = bb(b.eids, b.loeids);
					null != c && 0 < c.length && (a.eid = c.join(","))
				}
			} catch(d) {}
		},
		zc = function(a) {
			a.shv = Ha()
		};
	M.m = !Ia;
	var X = function(a, b) {
			b && a.push(b)
		},
		Y = function(a, b) {
			for(var c = 0; c < b.length; c++)
				if(C.location && C.location.hash == "#google_plle_" + b[c]) return b[c];
			var d;
			a: {
				try {
					var f = window.top.location.hash;
					if(f) {
						var e = f.match(/\bdeid=([\d,]+)/);
						d = e && e[1] || "";
						break a
					}
				} catch(g) {}
				d = ""
			}
			return(c = (c = d.match(new RegExp("\\b(" + b.join("|") + ")\\b"))) && c[0] || null) ? c : Ad ? null : mb(b, a)
		};
	var ue = function(a) {
			this.j = a;
			a.google_iframe_oncopy || (a.google_iframe_oncopy = {
				handlers: {},
				upd: p(this.o, this)
			});
			this.m = a.google_iframe_oncopy
		},
		ve = function(a, b) {
			var c = new RegExp("\\b" + a + "=(\\d+)"),
				d = c.exec(b);
			d && (b = b.replace(c, a + "=" + (+d[1] + 1 || 1)));
			return b
		};
	ue.prototype.set = function(a, b) {
		this.m.handlers[a] = b;
		this.j.addEventListener && this.j.addEventListener("load", p(this.l, this, a), !1)
	};
	ue.prototype.l = function(a) {
		a = this.j.document.getElementById(a);
		try {
			var b = a.contentWindow.document;
			if(a.onload && b && (!b.body || !b.body.firstChild)) a.onload()
		} catch(c) {}
	};
	ue.prototype.o = function(a, b) {
		var c = ve("rx", a);
		a: {
			if(a && (a = a.match("dt=([^&]+)")) && 2 == a.length) {
				a = a[1];
				break a
			}
			a = ""
		}
		a = (new Date).getTime() - a;
		c = c.replace(/&dtd=(\d+|-?M)/, "&dtd=" + (1E5 <= a ? "M" : 0 <= a ? a : "-M"));
		this.set(b, c);
		return c
	};
	var we = Va("var i=this.id,s=window.google_iframe_oncopy,H=s&&s.handlers,h=H&&H[i],w=this.contentWindow,d;try{d=w.document}catch(e){}if(h&&d&&(!d.body||!d.body.firstChild)){if(h.call){setTimeout(h,0)}else if(h.match){try{h=s.upd(h,i)}catch(e){}w.location.replace(h)}}");
	var xe = function(a) {
		if(!a) return "";
		(a = a.toLowerCase()) && "ca-" != a.substring(0, 3) && (a = "ca-" + a);
		return a
	};
	Qb || Rb || I && Yb(11);
	var ye = function(a, b, c) {
		var d = ["<iframe"],
			f;
		for(f in a) a.hasOwnProperty(f) && qc(d, f + "=" + a[f]);
		d.push('style="left:0;position:absolute;top:0;"');
		d.push("></iframe>");
		a = a.id;
		b = "border:none;height:" + c + "px;margin:0;padding:0;position:relative;visibility:visible;width:" + b + "px;background-color:transparent";
		return ['<ins id="', a + "_expand", '" style="display:inline-table;', b, '"><ins id="', a + "_anchor", '" style="display:block;', b, '">', d.join(" "), "</ins></ins>"].join("")
	};
	var ze = null;
	var Ae = {
		"120x90": !0,
		"160x90": !0,
		"180x90": !0,
		"200x90": !0,
		"468x15": !0,
		"728x15": !0
	};
	var Z = function(a) {
			this.o = [];
			this.l = a || window;
			this.j = 0;
			this.m = null;
			this.F = 0
		},
		Be, Ce = function(a) {
			try {
				return a.sz()
			} catch(b) {
				return !1
			}
		},
		De = function(a) {
			return !!a && ("object" === typeof a || "function" === typeof a) && Ce(a) && pc(a.nq) && pc(a.nqa) && pc(a.al) && pc(a.rl)
		},
		Ee = function() {
			if(Be && Ce(Be)) return Be;
			var a = Uc(),
				b = a.google_jobrunner;
			return De(b) ? Be = b : a.google_jobrunner = Be = new Z(a)
		},
		Fe = function(a, b) {
			Ee().nq(a, b)
		},
		Ge = function(a, b) {
			Ee().nqa(a, b)
		};
	Z.prototype.G = function(a, b) {
		0 != this.j || 0 != this.o.length || b && b != window ? this.v(a, b) : (this.j = 2, this.A(new He(a, window)))
	};
	Z.prototype.v = function(a, b) {
		this.o.push(new He(a, b || this.l));
		Ie(this)
	};
	Z.prototype.R = function(a) {
		this.j = 1;
		if(a) {
			var b = Fc("sjr::timeout", p(this.w, this, !0));
			this.m = this.l.setTimeout(b, a)
		}
	};
	Z.prototype.w = function(a) {
		a && ++this.F;
		1 == this.j && (null != this.m && (this.l.clearTimeout(this.m), this.m = null), this.j = 0);
		Ie(this)
	};
	Z.prototype.T = function() {
		return !(!window || !Array)
	};
	Z.prototype.P = function() {
		return this.F
	};
	var Ie = function(a) {
		var b = Fc("sjr::tryrun", p(a.U, a));
		a.l.setTimeout(b, 0)
	};
	Z.prototype.U = function() {
		if(0 == this.j && this.o.length) {
			var a = this.o.shift();
			this.j = 2;
			var b = Fc("sjr::run", p(this.A, this, a));
			a.j.setTimeout(b, 0);
			Ie(this)
		}
	};
	Z.prototype.A = function(a) {
		this.j = 0;
		a.l()
	};
	Z.prototype.nq = Z.prototype.G;
	Z.prototype.nqa = Z.prototype.v;
	Z.prototype.al = Z.prototype.R;
	Z.prototype.rl = Z.prototype.w;
	Z.prototype.sz = Z.prototype.T;
	Z.prototype.tc = Z.prototype.P;
	var He = function(a, b) {
		this.l = a;
		this.j = b
	};
	var Je = function() {
			var a = Ka ? "https" : "http",
				b = ib("script"),
				c;
			a: {
				if(Ia) try {
					var d = C.google_cafe_host || C.top.google_cafe_host;
					if(d) {
						c = d;
						break a
					}
				} catch(f) {}
				c = ka("", "pagead2.googlesyndication.com")
			}
			return ["<", b, ' src="', Ic(c, ["/pagead/js/", Ha(), "/r20161129/show_ads_impl.js"].join(""), a), '"></', b, ">"].join("")
		},
		Ke = function(a, b, c, d) {
			return function() {
				var f = !1;
				d && Ee().al(3E4);
				try {
					var e = a.document.getElementById(b).contentWindow;
					if(D(e)) {
						var g = a.document.getElementById(b).contentWindow,
							h = g.document;
						h.body && h.body.firstChild || (/Firefox/.test(navigator.userAgent) ? h.open("text/html", "replace") : h.open(), g.google_async_iframe_close = !0, h.write(c))
					} else {
						for(var k = a.document.getElementById(b).contentWindow, e = c, e = String(e), g = ['"'], h = 0; h < e.length; h++) {
							var l = e.charAt(h),
								n = l.charCodeAt(0),
								q = h + 1,
								w;
							if(!(w = Wa[l])) {
								var x;
								if(31 < n && 127 > n) x = l;
								else {
									var y = void 0,
										A = l;
									if(A in
										Xa) x = Xa[A];
									else if(A in Wa) x = Xa[A] = Wa[A];
									else {
										var u = A.charCodeAt(0);
										if(31 < u && 127 > u) y = A;
										else {
											if(256 > u) {
												if(y = "\\x", 16 > u || 256 < u) y += "0"
											} else y = "\\u", 4096 > u && (y += "0");
											y += u.toString(16).toUpperCase()
										}
										x = Xa[A] = y
									}
								}
								w = x
							}
							g[q] = w
						}
						g.push('"');
						k.location.replace("javascript:" + g.join(""))
					}
					f = !0
				} catch(O) {
					k = Uc().google_jobrunner, De(k) && k.rl()
				}
				f && (f = ve("google_async_rrc", c), (new ue(a)).set(b, Ke(a, b, f, !1)))
			}
		},
		Le = function(a) {
			var b = ["<iframe"];
			oc(a, function(a, d) {
				null != a && b.push(" " + d + '="' + Va(a) + '"')
			});
			b.push("></iframe>");
			return b.join("")
		},
		Ne = function(a, b, c) {
			Me(a, b, c, function(a, b, e) {
				a = a.document;
				for(var d = b.id, f = 0; !d || a.getElementById(d);) d = "aswift_" + f++;
				b.id = d;
				b.name = d;
				d = Number(e.google_ad_width);
				f = Number(e.google_ad_height);
				16 == e.google_reactive_ad_format ? (e = a.createElement("div"), a = ye(b, d, f), e.innerHTML = a, c.appendChild(e.firstChild)) : (e = ye(b, d, f), c.innerHTML = e);
				return b.id
			})
		},
		Me = function(a, b, c, d) {
			var f = ib("script"),
				e = {},
				g = b.google_ad_height;
			e.width = '"' + b.google_ad_width + '"';
			e.height = '"' + g + '"';
			e.frameborder = '"0"';
			e.marginwidth = '"0"';
			e.marginheight = '"0"';
			e.vspace = '"0"';
			e.hspace = '"0"';
			e.allowtransparency = '"true"';
			e.scrolling = '"no"';
			e.allowfullscreen = '"true"';
			e.onload = '"' + we + '"';
			d = d(a, e, b);
			g = b.google_ad_output;
			(e = b.google_ad_format) || "html" != g && null != g || (e = b.google_ad_width + "x" + b.google_ad_height);
			g = !b.google_ad_slot || b.google_override_format || !Ae[b.google_ad_width + "x" + b.google_ad_height] && "aa" == b.google_loader_used;
			e && g ? e = e.toLowerCase() : e = "";
			b.google_ad_format = e;
			if(!ba(b.google_reactive_sra_index) || !b.google_ad_unit_key) {
				for(var e = [b.google_ad_slot, b.google_ad_format, b.google_ad_type, b.google_ad_width, b.google_ad_height], g = [], h = 0, k = c; k && 25 > h; k = k.parentNode, ++h) g.push(9 !== k.nodeType && k.id || "");
				(g = g.join()) && e.push(g);
				b.google_ad_unit_key = ob(e.join(":")).toString();
				e = [];
				for(g = 0; c && 25 > g; ++g) {
					h = (h = 9 !== c.nodeType && c.id) ? "/" + h : "";
					a: {
						if(c && c.nodeName && c.parentElement)
							for(var k = c.nodeName.toString().toLowerCase(), l = c.parentElement.childNodes, n = 0, q = 0; q < l.length; ++q) {
								var w = l[q];
								if(w.nodeName && w.nodeName.toString().toLowerCase() === k) {
									if(c ===
										w) {
										k = "." + n;
										break a
									}++n
								}
							}
						k = ""
					}
					e.push((c.nodeName && c.nodeName.toString().toLowerCase()) + h + k);
					c = c.parentElement
				}
				c = e.join() + ":";
				e = a;
				g = [];
				if(e) try {
					for(var x = e.parent, h = 0; x && x !== e && 25 > h; ++h) {
						for(var y = x.frames, k = 0; k < y.length; ++k)
							if(e === y[k]) {
								g.push(k);
								break
							}
						e = x;
						x = e.parent
					}
				} catch(pa) {}
				b.google_ad_dom_fingerprint = ob(c + g.join()).toString()
			}
			var x = le(b),
				A;
			a: {
				try {
					if(window.JSON && window.JSON.stringify && window.encodeURIComponent) {
						var u = window.encodeURIComponent(window.JSON.stringify(b)),
							O;
						try {
							O = ec(u)
						} catch(pa) {
							O =
								"#" + ec(u, !0)
						}
						A = O;
						break a
					}
				} catch(pa) {
					M.j("sblob".toString(), pa, void 0, void 0)
				}
				A = ""
			}
			var E;
			b = b.google_ad_client;
			if(!ze) b: {
				O = [m.top];u = [];
				for(y = 0; c = O[y++];) {
					u.push(c);
					try {
						if(c.frames)
							for(var Q = c.frames.length, e = 0; e < Q && 1024 > O.length; ++e) O.push(c.frames[e])
					} catch(pa) {}
				}
				for(Q = 0; Q < u.length; Q++) try {
					if(E = u[Q].frames.google_esf) {
						ze = E;
						break b
					}
				} catch(pa) {}
				ze = null
			}
			ze ? E = "" : (E = {
				style: "display:none"
			}, /[^a-z0-9-]/.test(b) ? E = "" : (E["data-ad-client"] = xe(b), E.id = "google_esf", E.name = "google_esf", E.src = Ic(ka("",
				"googleads.g.doubleclick.net"), ["/pagead/html/", Ha(), "/r20161129/zrt_lookup.html"].join("")), E = Le(E)));
			Q = ga;
			b = (new Date).getTime();
			u = a.google_unique_id;
			A = ["<!doctype html><html><body>", E, "<", f, ">", x, "google_show_ads_impl=true;google_unique_id=", "number" === typeof u ? u : 0, ';google_async_iframe_id="', d, '";google_start_time=', Q, ";", A ? 'google_pub_vars = "' + A + '";' : "", "google_bpp=", b > Q ? b - Q : 1, ";google_async_rrc=0;google_iframe_start_time=new Date().getTime();</",
				f, ">", Je(), "</body></html>"
			].join("");
			(a.document.getElementById(d) ? Fe : Ge)(Ke(a, d, A, !0))
		},
		Oe = function(a, b) {
			var c = navigator;
			a && b && c && (a = a.document, b = xe(b), /[^a-z0-9-]/.test(b) || ((c = Na("r20160913")) && (c += "/"), c = Ic("pagead2.googlesyndication.com", "/pub-config/" + c + b + ".js"), b = a.createElement("script"), b.src = c, (a = a.getElementsByTagName("script")[0]) && a.parentNode && a.parentNode.insertBefore(b, a)))
		};
	var Pe = !1,
		Qe = 0,
		Re = !1,
		Se = !1,
		Te = function(a) {
			return uc.test(a.className) && "done" != a.getAttribute("data-adsbygoogle-status")
		},
		Ve = function(a, b) {
			var c = window;
			a.setAttribute("data-adsbygoogle-status", "done");
			Ue(a, b, c)
		},
		Ue = function(a, b, c) {
			We(a, b, c);
			if(!Xe(a, b, c)) {
				if(b.google_reactive_ads_config) {
					if(Pe) throw new P("Only one 'enable_page_level_ads' allowed per page.");
					Pe = !0
				} else b.google_ama || rc(c);
				Re || (Re = !0, Oe(c, b.google_ad_client));
				oc(ke, function(a, d) {
					b[d] = b[d] || c[d]
				});
				b.google_loader_used = "aa";
				b.google_reactive_tag_first =
					1 === Qe;
				var d = b.google_ad_output;
				if(d && "html" != d && "js" != d) throw new P("No support for google_ad_output=" + d);
				Dc("aa::load", Ec, function() {
					Ne(c, b, a)
				})
			}
		},
		Xe = function(a, b, c) {
			var d = b.google_reactive_ads_config;
			if(d) var f = d.page_level_pubvars,
				e = (ca(f) ? f : {}).google_tag_origin;
			if(b.google_ama || "js" === b.google_ad_output) return !1;
			var g = b.google_ad_slot,
				f = c.google_ad_modifications;
			!f || Td(f.ad_whitelist, g, e || b.google_tag_origin) ? f = null : (e = f.space_collapsing || "none", f = (g = Td(f.ad_blacklist, g)) ? {
				K: !0,
				N: g.space_collapsing ||
					e
			} : f.remove_ads_by_default ? {
				K: !0,
				N: e
			} : null);
			if(f && f.K && "on" != b.google_adtest) return "slot" == f.N && (null !== rb(a.getAttribute("width")) && a.setAttribute("width", 0), null !== rb(a.getAttribute("height")) && a.setAttribute("height", 0), a.style.width = "0px", a.style.height = "0px"), !0;
			if((f = lb(a, c)) && "none" == f.display && !("on" == b.google_adtest || 0 < b.google_reactive_ad_format || d)) return c.document.createComment && a.appendChild(c.document.createComment("No ad requested because of display:none on the adsbygoogle tag")), !0;
			a = 1 === b.google_reactive_ad_format || 8 === b.google_reactive_ad_format;
			c = null == b.google_pgb_reactive || 3 === b.google_pgb_reactive;
			return Ma && a && c ? (m.console && m.console.warn("Adsbygoogle tag with data-reactive-ad-format=" + b.google_reactive_ad_format + " is deprecated. Check out page-level ads at https://www.google.com/adsense"), !0) : !1
		},
		We = function(a, b, c) {
			for(var d = a.attributes, f = d.length, e = 0; e < f; e++) {
				var g = d[e];
				if(/data-/.test(g.name)) {
					var h = g.name.replace("data-matched-content", "google_content_recommendation").replace("data",
						"google").replace(/-/g, "_");
					if(!b.hasOwnProperty(h)) {
						var k;
						k = h;
						var g = g.value,
							l = {
								google_reactive_ad_format: ha,
								google_allow_expandable_ads: ia
							};
						k = l.hasOwnProperty(k) ? l[k](g, null) : g;
						null === k || (b[h] = k)
					}
				}
			}
			d = N.J;
			if(L(Sd(c), d.VIEWPORT) || L(Sd(c), d.H) && a.style && c.document && c.document.body) {
				f = parseInt(a.style.width, 10);
				b: {
					if(e = xc(c)) {
						e = Wc(e).clientWidth;
						h = c.document.body.currentStyle ? c.document.body.currentStyle.direction : c.getComputedStyle ? c.getComputedStyle(c.document.body).getPropertyValue("direction") : "";
						if("ltr" === h && e) {
							e = Math.min(1200, e - a.getBoundingClientRect().left);
							break b
						}
						if("rtl" === h && e) {
							h = c.document.body.getBoundingClientRect().right - a.getBoundingClientRect().right;
							e = Math.min(1200, e - h - Math.floor((e - c.document.body.clientWidth) / 2));
							break b
						}
					}
					e = -1
				}
				0 <= e && f > e && (L(Sd(c), d.VIEWPORT) ? (b.google_ad_width = e, a.style.width = e + "px") : L(Sd(c), d.H) && (a.style.width = e + "px", b.google_ad_format = "auto"))
			}
			b.google_enable_content_recommendations && 1 == b.google_reactive_ad_format ? (b.google_ad_width = Wc(c).clientWidth,
				b.google_ad_height = 50, a.style.display = "none") : (f = de(c, b)) ? (b.google_auto_format = b.google_ad_format, d = a.offsetWidth, c = ee(f, d, a, b, c), f = c.l, b.google_ad_width = f.j(d), e = b.google_ad_height = f.height(), a.style.height = e + "px", b.google_ad_resizable = !0, b.google_ad_format = f.l(d), b.google_override_format = 1, b.google_responsive_auto_format = c.j, b.google_loader_features_used = 128) : !qb.test(b.google_ad_width) && !pb.test(a.style.width) || !qb.test(b.google_ad_height) && !pb.test(a.style.height) ? (c = lb(a, c), a.style.width = c.width,
				a.style.height = c.height, Jd(c, b), b.google_ad_width || (b.google_ad_width = a.offsetWidth), b.google_ad_height || (b.google_ad_height = a.offsetHeight), b.google_loader_features_used = 256) : (Jd(a.style, b), b.google_ad_output && "html" != b.google_ad_output || 300 != b.google_ad_width || 250 != b.google_ad_height || (c = a.style.width, a.style.width = "100%", d = a.offsetWidth, a.style.width = c, b.google_available_width = d))
		},
		Ye = function(a) {
			for(var b = document.getElementsByTagName("ins"), c = 0, d = b[c]; c < b.length; d = b[++c]) {
				var f = d;
				if(Te(f) && "reserved" !=
					f.getAttribute("data-adsbygoogle-status") && (!a || d.id == a)) return d
			}
			return null
		},
		$e = function(a) {
			if(!Se) {
				Se = !0;
				var b;
				try {
					b = m.localStorage.getItem("google_ama_config")
				} catch(g) {
					b = null
				}
				var c;
				try {
					c = b ? new Qc(fc(b)) : null
				} catch(g) {
					c = null
				}
				if(c)
					if(b = jc(c, Rc, 3), !b || K(b, 1) <= fa()) try {
						m.localStorage.removeItem("google_ama_config")
					} catch(g) {
						td(m, {
							lserr: 1
						})
					} else {
						try {
							var d;
							if(d = !m.google_noqs) a: {
								var f = K(c, 5);
								for(b = 0; b < f.length; b++)
									if(1 == f[b]) {
										d = !0;
										break a
									}
								d = !1
							}
							if(d) {
								var e = new md;
								(new qd(m, new ud(a, c), 100, e)).start();
								e.l.then(r(wd, m), r(xd, m))
							}
						} catch(g) {
							td(m, {
								atf: -1
							})
						}
						c = Ze();
						m.document.documentElement.appendChild(c);
						Ve(c, {
							google_ama: !0,
							google_ad_client: a
						})
					}
			}
		},
		ne = function(a) {
			if(a && a.shift) try {
				for(var b, c = 20; 0 < a.length && (b = a.shift()) && 0 < c;) af(b), --c
			} catch(d) {
				throw window.setTimeout(bf, 0), d;
			}
		},
		Ze = function() {
			var a = document.createElement("ins");
			a.className = "adsbygoogle";
			a.style.display = "none";
			return a
		},
		cf = function(a, b) {
			var c = {};
			oc(Vc, function(b, d) {
				a.hasOwnProperty(d) && (c[d] = a[d])
			});
			ca(a.enable_page_level_ads) && (c.page_level_pubvars =
				a.enable_page_level_ads);
			var d = Ze();
			b ? B.body.appendChild(d) : B.documentElement.appendChild(d);
			Ve(d, {
				google_reactive_ads_config: c,
				google_ad_client: a.google_ad_client
			})
		},
		df = function(a) {
			var b = xc(window);
			if(!b) throw new P("Page-level tag does not work inside iframes.");
			zd(b).wasPlaTagProcessed = !0;
			var b = L(Sd(C), N.I.u),
				c = !b;
			B.body || b ? cf(a, c) : (b = Gc("aa:reactiveTag", function() {
				cf(a, c)
			}), wb(B, "DOMContentLoaded", b))
		},
		ef = function(a, b, c, d) {
			return 0 == b.message.indexOf("TagError") ? (M.o = !0, M.j(a.toString(), b, .1,
				d, "puberror"), !1) : M.j(a.toString(), b, c, d)
		},
		ff = function(a, b, c, d) {
			return 0 == b.message.indexOf("TagError") ? !1 : M.j(a.toString(), b, c, d)
		},
		af = function(a) {
			var b = {};
			Dc("aa::hqe", ef, function() {
				gf(a, b)
			}, function(c) {
				c.client = c.client || b.google_ad_client || a.google_ad_client;
				c.slotname = c.slotname || b.google_ad_slot;
				c.tag_origin = c.tag_origin || b.google_tag_origin
			})
		},
		gf = function(a, b) {
			ga = (new Date).getTime();
			if(m.google_q && m.google_q.tags) se(a), m.google_q.tags.push(a);
			else {
				var c;
				a: {
					if(a.enable_page_level_ads) {
						if("string" ==
							typeof a.google_ad_client) {
							c = !0;
							break a
						}
						throw new P("'google_ad_client' is missing from the tag config.");
					}
					c = !1
				}
				if(c) 0 === Qe && (Qe = 1), $e(a.google_ad_client), df(a);
				else {
					0 === Qe && (Qe = 2);
					m.google_q ? te(a) : (re(), se(a));
					c = a.element;
					(a = a.params) && oc(a, function(a, c) {
						b[c] = a
					});
					if("js" === b.google_ad_output) {
						m.google_ad_request_done_fns = m.google_ad_request_done_fns || [];
						m.google_radlink_request_done_fns = m.google_radlink_request_done_fns || [];
						if(b.google_ad_request_done) {
							if("function" != aa(b.google_ad_request_done)) throw new P("google_ad_request_done parameter must be a function.");
							m.google_ad_request_done_fns.push(b.google_ad_request_done);
							delete b.google_ad_request_done;
							a = m.google_ad_request_done_fns.length - 1;
							b.google_ad_request_done_index = a
						} else throw new P("google_ad_request_done parameter must be specified.");
						if(b.google_radlink_request_done) {
							if("function" != aa(b.google_radlink_request_done)) throw new P("google_radlink_request_done parameter must be a function.");
							m.google_radlink_request_done_fns.push(b.google_radlink_request_done);
							delete b.google_radlink_request_done;
							a =
								m.google_radlink_request_done_fns.length - 1;
							b.google_radlink_request_done_index = a
						}
						a = Ze();
						m.document.documentElement.appendChild(a);
						c = a
					}
					if(c) {
						if(!Te(c) && (c.id ? c = Ye(c.id) : c = null, !c)) throw new P("'element' has already been filled.");
						if(!("innerHTML" in c)) throw new P("'element' is not a good DOM element.");
					} else if(c = Ye(), !c) throw new P("All ins elements in the DOM with class=adsbygoogle already have ads in them.");
					Ve(c, b)
				}
			}
		},
		bf = function() {
			Bc();
			Dc("aa::main", ff, hf)
		},
		hf = function() {
			var a = C.google_ad_modifications =
				C.google_ad_modifications || {};
			if(!a.plle) {
				a.plle = !0;
				var b = a.eids = a.eids || [],
					a = a.loeids = a.loeids || [],
					c, d, f;
				c = Cd;
				X(b, Y(na, [c.s, c.u]));
				c = Dd;
				X(b, Y(sa, [c.s, c.u]));
				c = Ed;
				X(b, Y(la, [c.s, c.u]));
				c = Fd;
				X(b, Y(ma, [c.s, c.u]));
				c = N.V;
				d = Y(Ba, [c.s, c.C, c.B, c.D]);
				X(a, d);
				d == c.s ? f = Bd.s : d == c.C ? f = Bd.C : d == c.B ? f = Bd.B : d == c.D ? f = Bd.D : f = "";
				X(b, f);
				B.body || (c = N.I, X(a, Y(Da, [c.s, c.u])));
				c = N.W;
				d = Y(Ea, [c.s, c.u]);
				X(a, d);
				c = N.X;
				d = Y(Fa, [c.s, c.u]);
				X(a, d);
				Na("") && X(a, "");
				c =
					N.J;
				d = Y(Ga, [c.s, c.VIEWPORT, c.H]);
				X(a, d)
			}
			b = window.adsbygoogle;
			ne(b);
			if(!b || !b.loaded) {
				window.adsbygoogle = {
					push: af,
					loaded: !0
				};
				b && jf(b.onload);
				try {
					Object.defineProperty(window.adsbygoogle, "onload", {
						set: jf
					})
				} catch(e) {}
			}
		},
		jf = function(a) {
			pc(a) && window.setTimeout(a, 0)
		};
	bf();
}).call(this);