(function () {
	'use strict';

	var mqReduce = window.matchMedia('(prefers-reduced-motion: reduce)');
	var mqHover = window.matchMedia('(hover: hover) and (pointer: fine)');
	var reduced = mqReduce.matches;
	var canHover = mqHover.matches;
	var docEl = document.documentElement;

	/* ------------------------------------------------------------------ *
	 * 1. Mobile drawer (base behaviour)
	 * ------------------------------------------------------------------ */
	var drawer = document.getElementById('cdc-drawer');
	var burger = document.getElementById('cdc-burger');
	var close = document.getElementById('cdc-drawer-close');
	var backdrop = document.getElementById('cdc-drawer-backdrop');

	if (drawer && burger) {
		function openDrawer() {
			drawer.classList.add('open');
			drawer.setAttribute('aria-hidden', 'false');
			burger.setAttribute('aria-expanded', 'true');
			document.body.style.overflow = 'hidden';
			if (close) {
				close.focus();
			}
		}

		function closeDrawer() {
			drawer.classList.remove('open');
			drawer.setAttribute('aria-hidden', 'true');
			burger.setAttribute('aria-expanded', 'false');
			document.body.style.overflow = '';
			burger.focus();
		}

		burger.addEventListener('click', openDrawer);

		if (close) {
			close.addEventListener('click', closeDrawer);
		}

		if (backdrop) {
			backdrop.addEventListener('click', closeDrawer);
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && drawer.classList.contains('open')) {
				closeDrawer();
			}
		});

		window.addEventListener('resize', function () {
			if (window.innerWidth >= 1024 && drawer.classList.contains('open')) {
				closeDrawer();
			}
		});

		var links = drawer.querySelectorAll('a');
		Array.prototype.forEach.call(links, function (link) {
			link.addEventListener('click', closeDrawer);
		});
	}

	/* ------------------------------------------------------------------ *
	 * 2. Motion bootstrap — reveals/parallax/tilt are gated per-feature,
	 *    while layout-critical behaviour (stack, counters) always runs.
	 * ------------------------------------------------------------------ */
	if (!reduced) {
		docEl.classList.add('cdc-anim');
	}

	var progress = document.getElementById('cdc-progress');
	var stack = document.querySelector('.cdc-showcase');
	var stackCards = [];
	var stackCount = 0;

	if (stack) {
		Array.prototype.forEach.call(document.querySelectorAll('.cdc-stack-card'), function (el, i) {
			stackCards.push({ el: el, index: i });
			el.style.zIndex = i + 1;
		});
		stackCount = stackCards.length;
	}

	/* ------------------------------------------------------------------ *
	 * 3. Master rAF loop (scroll-linked)
	 * ------------------------------------------------------------------ */
	var ticking = false;

	function requestTick() {
		if (!ticking) {
			ticking = true;
			requestAnimationFrame(run);
		}
	}

	function run() {
		ticking = false;
		var y = window.pageYOffset || document.documentElement.scrollTop;

		var max = docEl.scrollHeight - window.innerHeight;
		if (progress && !reduced) {
			progress.style.transform = 'scaleX(' + (max > 0 ? Math.min(y / max, 1) : 0) + ')';
		}

		if (y > 10) {
			document.body.classList.add('cdc-scrolled');
		} else {
			document.body.classList.remove('cdc-scrolled');
		}

		updateParallax();
		updateStack();
	}

	window.addEventListener('scroll', requestTick, { passive: true });
	window.addEventListener('resize', requestTick, { passive: true });

	/* ------------------------------------------------------------------ *
	 * 4. Scroll reveals (IntersectionObserver + stagger)
	 * ------------------------------------------------------------------ */
	function collectReveals() {
		var selectors = [
			'[data-cdc-reveal]',
			'.cdc-grid > .cdc-card',
			'.cdc-cat-grid > .cdc-cat-tile',
			'ul.products > li.product',
			'.cdc-feature',
			'.cdc-section-head',
			'.cdc-shop-head',
			'.cdc-shop-toolbar',
			'.cdc-banner',
			'.cdc-hero-copy > *',
			'.cdc-stats-item'
		];
		var set = new Set();

		selectors.forEach(function (sel) {
			Array.prototype.forEach.call(document.querySelectorAll(sel), function (el) {
				set.add(el);
			});
		});

		set.forEach(function (el) {
			el.classList.add('cdc-reveal-item');
			var siblings = Array.prototype.filter.call(el.parentNode.children, function (c) {
				return c.classList.contains('cdc-reveal-item');
			});
			var idx = siblings.indexOf(el);
			var delay = Math.min(idx * 90, 360);
			if (el.hasAttribute('data-cdc-delay')) {
				delay = parseInt(el.getAttribute('data-cdc-delay'), 10) || 0;
			}
			el.style.setProperty('--cdc-r-delay', delay + 'ms');
		});
		return set;
	}

	var revealTargets = [];

	if (!reduced) {
		revealTargets = collectReveals();

		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							var el = entry.target;
							el.classList.add('is-in');
							io.unobserve(el);
						}
					});
				},
				{ threshold: 0.15, rootMargin: '0px 0px -8% 0px' }
			);
			revealTargets.forEach(function (el) {
				io.observe(el);
			});
		} else {
			revealTargets.forEach(function (el) {
				el.classList.add('is-in');
			});
		}
	}

	/* ------------------------------------------------------------------ *
	 * 5. Parallax layers
	 * ------------------------------------------------------------------ */
	var parallaxEls = [];
	Array.prototype.forEach.call(document.querySelectorAll('[data-cdc-parallax]'), function (el) {
		parallaxEls.push({
			el: el,
			speed: parseFloat(el.getAttribute('data-cdc-speed')) || 0.1
		});
	});

	function updateParallax() {
		if (reduced || !parallaxEls.length) {
			return;
		}
		var vh = window.innerHeight;
		for (var i = 0; i < parallaxEls.length; i++) {
			var p = parallaxEls[i];
			var rect = p.el.getBoundingClientRect();
			if (rect.bottom < -250 || rect.top > vh + 250) {
				continue;
			}
			var center = rect.top + rect.height / 2 - vh / 2;
			p.el.style.transform = 'translate3d(0,' + (center * p.speed).toFixed(1) + 'px,0)';
		}
	}

	/* ------------------------------------------------------------------ *
	 * 6. 3D tilt + glare
	 * ------------------------------------------------------------------ */
	if (canHover && !reduced) {
		Array.prototype.forEach.call(
			document.querySelectorAll('[data-cdc-tilt], .cdc-card, .cdc-cat-tile, .cdc-hero-card'),
			function (el) {
				var max = parseFloat(el.getAttribute('data-cdc-tilt-max')) || 8;

				var glare = document.createElement('span');
				glare.className = 'cdc-tilt-glare';
				glare.setAttribute('aria-hidden', 'true');
				el.appendChild(glare);

				el.addEventListener('pointerenter', function () {
					el.classList.add('is-tilting');
				});

				el.addEventListener('pointermove', function (e) {
					var r = el.getBoundingClientRect();
					var px = (e.clientX - r.left) / r.width;
					var py = (e.clientY - r.top) / r.height;
					var rx = (0.5 - py) * max;
					var ry = (px - 0.5) * max;
					el.style.transform =
						'translateY(var(--cdc-reveal-y, 0px)) perspective(900px) rotateX(' + rx.toFixed(2) + 'deg) rotateY(' + ry.toFixed(2) + 'deg) translateZ(0)';
					el.style.setProperty('--cdc-glare-x', (px * 100).toFixed(1) + '%');
					el.style.setProperty('--cdc-glare-y', ((1 - py) * 100).toFixed(1) + '%');
				});

				el.addEventListener('pointerleave', function () {
					el.classList.remove('is-tilting');
					el.style.transform = '';
					el.style.setProperty('--cdc-glare-x', '50%');
					el.style.setProperty('--cdc-glare-y', '50%');
				});
			}
		);

		/* Magnetic buttons */
		Array.prototype.forEach.call(
			document.querySelectorAll('[data-cdc-magnet], .cdc-btn, .cdc-link-arrow'),
			function (el) {
				var strength = parseFloat(el.getAttribute('data-cdc-magnet')) || 0.25;

				el.addEventListener('pointermove', function (e) {
					var r = el.getBoundingClientRect();
					var dx = (e.clientX - (r.left + r.width / 2)) * strength;
					var dy = (e.clientY - (r.top + r.height / 2)) * strength;
					var d = Math.sqrt(dx * dx + dy * dy);
					var cap = 10;
					if (d > cap) {
						dx *= cap / d;
						dy *= cap / d;
					}
					el.style.transform = 'translate3d(' + dx.toFixed(1) + 'px,' + dy.toFixed(1) + 'px,0)';
				});

				el.addEventListener('pointerleave', function () {
					el.style.transform = '';
				});
			}
		);
	}

	/* ------------------------------------------------------------------ *
	 * 7. Animated counters
	 * ------------------------------------------------------------------ */
	function animateCount(el) {
		var target = parseFloat(el.getAttribute('data-cdc-count'));
		if (isNaN(target)) {
			return;
		}
		var decimals = parseInt(el.getAttribute('data-cdc-decimals'), 10) || 0;
		var suffix = el.getAttribute('data-cdc-suffix') || '';
		var dur = 1500;
		var start = null;

		function format(v) {
			var locale = docEl.lang || 'en-IN';
			return (
				v.toLocaleString(locale, {
					minimumFractionDigits: decimals,
					maximumFractionDigits: decimals
				}) + suffix
			);
		}

		if (reduced) {
			el.textContent = format(target);
			return;
		}

		function step(ts) {
			if (!start) {
				start = ts;
			}
			var p = Math.min((ts - start) / dur, 1);
			var eased = 1 - Math.pow(1 - p, 4);
			el.textContent = format(target * eased);
			if (p < 1) {
				requestAnimationFrame(step);
			} else {
				el.textContent = format(target);
			}
		}

		requestAnimationFrame(step);
	}

	if ('IntersectionObserver' in window) {
		var cio = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						animateCount(entry.target);
						cio.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.6 }
		);
		Array.prototype.forEach.call(document.querySelectorAll('[data-cdc-count]'), function (el) {
			cio.observe(el);
		});
	}

	/* ------------------------------------------------------------------ *
	 * 8. Sticky stacked-card showcase
	 * ------------------------------------------------------------------ */
	function updateStack() {
		if (!stackCount) {
			return;
		}
		if (reduced) {
			for (var s = 0; s < stackCount; s++) {
				var d = stackCount - 1 - s;
				var sy = -70 - d * 18;
				var ss = 0.88 - d * 0.04;
				stackCards[s].el.style.transform =
					'translateY(' + sy.toFixed(1) + 'px) scale(' + ss.toFixed(3) + ') rotate(' + ((1 - ss) * 18).toFixed(1) + 'deg)';
				stackCards[s].el.style.opacity = '1';
			}
			return;
		}
		var rect = stack.getBoundingClientRect();
		var vh = window.innerHeight;
		var total = stack.offsetHeight - vh;
		var p = total > 0 ? Math.max(0, Math.min(1, -rect.top / total)) : 0;
		var pn = p * stackCount;

		for (var i = 0; i < stackCount; i++) {
			var t = pn - i;
			var depth = stackCount - 1 - i;
			var y, s, o, r;
			if (t < -1) {
				y = 110;
				s = 0.82;
				o = 0;
				r = 0;
			} else if (t <= 0) {
				var k = t + 1;
				y = 110 - 110 * k;
				s = 0.82 + 0.18 * k;
				o = k;
				r = (1 - s) * 18;
			} else if (t <= 1) {
				y = -70 * t;
				s = 1 - 0.12 * t;
				o = 1;
				r = (1 - s) * 18;
			} else {
				y = -70 - depth * 18;
				s = 0.88 - depth * 0.04;
				o = 1;
				r = (1 - s) * 18;
			}
			var c = stackCards[i];
			c.el.style.transform =
				'translateY(' + y.toFixed(1) + 'px) scale(' + s.toFixed(3) + ') rotate(' + r.toFixed(1) + 'deg)';
			c.el.style.opacity = o.toFixed(2);
		}
	}

	requestTick();

	/* ------------------------------------------------------------------ *
	 * 9. Wishlist (localStorage-backed hearts + header panel)
	 * ------------------------------------------------------------------ */
	function wlSanitize(list) {
		var seen = {};
		var out = [];
		for (var i = 0; i < list.length; i++) {
			var item = list[i];
			if (item && item.id != null && (typeof item.id === 'number' || typeof item.id === 'string') && !seen[String(item.id)]) {
				seen[String(item.id)] = 1;
				out.push(item);
			}
		}
		return out;
	}

	var WL_KEY = 'cdc_wishlist';
	var wlItems = [];
	try {
		wlItems = JSON.parse(localStorage.getItem(WL_KEY)) || [];
	} catch (e) {
		wlItems = [];
	}
	if (!Array.isArray(wlItems)) {
		wlItems = [];
	}
	wlItems = wlSanitize(wlItems);

	var wlToggle = document.getElementById('cdc-wishlist-toggle');
	var wlPanel = document.getElementById('cdc-wishlist-panel');
	var wlPanelList = document.getElementById('cdc-wishlist-panel-list');
	var wlCount = document.getElementById('cdc-wishlist-count');

	function wlEsc(str) {
		return String(str == null ? '' : str).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function wlFind(id) {
		for (var i = 0; i < wlItems.length; i++) {
			if (wlItems[i].id === id) {
				return i;
			}
		}
		return -1;
	}

	function wlPersist() {
		try {
			localStorage.setItem(WL_KEY, JSON.stringify(wlItems));
		} catch (e) {}
	}

	var i18n = window.cdcThemeI18n || {};
	function wlI18n(key, fallback) {
		return i18n[key] || fallback;
	}

	function wlFmt(fmt, name) {
		return fmt.indexOf('%s') > -1 ? fmt.replace('%s', name) : fmt;
	}

	function wlLabel(btn, on) {
		var name = btn.getAttribute('data-name');
		if (btn.classList.contains('cdc-wishlist-btn--single')) {
			return on ? wlI18n('removeSaved', 'Remove from wishlist') : wlI18n('saveWishlist', 'Save to wishlist');
		}
		return on
			? wlFmt(wlI18n('removeWishlist', 'Remove %s from wishlist'), name)
			: wlFmt(wlI18n('addWishlist', 'Add %s to wishlist'), name);
	}

	function wlSyncHearts() {
		var btns = document.querySelectorAll('.cdc-wishlist-btn');
		for (var i = 0; i < btns.length; i++) {
			var id = parseInt(btns[i].getAttribute('data-product-id'), 10);
			var on = wlFind(id) > -1;
			btns[i].classList.toggle('is-in', on);
			btns[i].setAttribute('aria-pressed', on ? 'true' : 'false');
			btns[i].setAttribute('aria-label', wlLabel(btns[i], on));
		}
		var n = wlItems.length;
		if (wlCount) {
			wlCount.textContent = n;
			wlCount.hidden = n === 0;
		}
	}

	function wlRenderPanel() {
		if (!wlPanelList) {
			return;
		}
		if (!wlItems.length) {
			var shopUrl = wlPanel ? wlPanel.getAttribute('data-shop-url') : '';
			wlPanelList.innerHTML =
				'<p class="cdc-wishlist-empty">' + wlEsc(wlI18n('emptyTitle', 'Your wishlist is empty.')) +
				'<br><span>' + wlEsc(wlI18n('emptySub', 'Save your favourite displays and revisit them anytime.')) + '</span></p>' +
				'<a class="cdc-btn cdc-btn-primary" href="' + wlEsc(shopUrl || '/shop/') + '">' + wlEsc(wlI18n('browse', 'Browse the collection')) + '</a>';
			return;
		}
		var html = '';
		for (var i = 0; i < wlItems.length; i++) {
			var it = wlItems[i];
			var thumb = it.img
				? '<img src="' + wlEsc(it.img) + '" alt="" loading="lazy" width="56" height="56">'
				: '<span class="cdc-wishlist-thumb-ph" aria-hidden="true"></span>';
			html +=
				'<div class="cdc-wishlist-item">' +
				'<a class="cdc-wishlist-item-link" href="' + wlEsc(it.url || '#') + '">' + thumb + '</a>' +
				'<div class="cdc-wishlist-item-body">' +
				'<a class="cdc-wishlist-item-name" href="' + wlEsc(it.url || '#') + '">' + wlEsc(it.name) + '</a>' +
				(it.price ? '<span class="cdc-wishlist-item-price">' + wlEsc(it.price) + '</span>' : '') +
				'</div>' +
				'<button type="button" class="cdc-wishlist-item-remove" data-id="' + wlEsc(it.id) + '" aria-label="' + wlEsc(wlI18n('removeSaved', 'Remove from wishlist')) + '">&times;</button>' +
				'</div>';
		}
		wlPanelList.innerHTML = html;
	}

	function wlAnnounce(text) {
		var live = document.getElementById('cdc-wl-live');
		if (!live) {
			return;
		}
		live.textContent = '';
		live.textContent = text;
	}

	function wlClosePanel() {
		if (wlPanel) {
			wlPanel.classList.remove('open');
		}
		if (wlToggle) {
			wlToggle.setAttribute('aria-expanded', 'false');
			wlToggle.focus();
		}
	}

	function wlOpenPanel() {
		if (!wlPanel) {
			return;
		}
		wlLastFocus = document.activeElement;
		wlPanel.classList.add('open');
		if (wlToggle) {
			wlToggle.setAttribute('aria-expanded', 'true');
		}
		var panelClose = document.getElementById('cdc-wishlist-panel-close');
		if (panelClose) {
			panelClose.focus();
		}
	}

	function wlTrapFocus(e) {
		if (!wlPanel || !wlPanel.classList.contains('open')) {
			return;
		}
		if (e.key !== 'Tab') {
			return;
		}
		var focusables = wlPanel.querySelectorAll('button, a[href], [tabindex]:not([tabindex="-1"])');
		if (!focusables.length) {
			return;
		}
		var first = focusables[0];
		var last = focusables[focusables.length - 1];
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	function wlToggleItem(id, btn) {
		var idx = wlFind(id);
		if (idx > -1) {
			wlItems.splice(idx, 1);
		} else if (btn) {
			wlItems.push({
				id: id,
				name: btn.getAttribute('data-name'),
				img: btn.getAttribute('data-img'),
				url: btn.getAttribute('data-url'),
				price: btn.getAttribute('data-price')
			});
		} else {
			return;
		}
		wlPersist();
		wlSyncHearts();
		wlRenderPanel();
		wlAnnounce(idx > -1 ? wlI18n('removed', 'Removed from wishlist') : wlI18n('added', 'Added to wishlist'));
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.cdc-wishlist-btn') : null;
		if (btn) {
			e.preventDefault();
			e.stopPropagation();
			var id = parseInt(btn.getAttribute('data-product-id'), 10);
			wlToggleItem(id, btn);
			btn.classList.remove('pop');
			void btn.offsetWidth;
			btn.classList.add('pop');
			return;
		}

		var rm = e.target.closest ? e.target.closest('.cdc-wishlist-item-remove') : null;
		if (rm) {
			var rid = parseInt(rm.getAttribute('data-id'), 10);
			wlToggleItem(rid, null);
			return;
		}

		if (wlPanel && wlPanel.classList.contains('open')) {
			if (wlPanel.contains(e.target) || (wlToggle && wlToggle.contains(e.target))) {
				return;
			}
			wlClosePanel();
		}
	});

	var wlLastFocus = null;

	if (wlToggle && wlPanel) {
		wlToggle.addEventListener('click', function (e) {
			e.preventDefault();
			if (wlPanel.classList.contains('open')) {
				wlClosePanel();
			} else {
				wlOpenPanel();
			}
		});

		var wlPanelClose = document.getElementById('cdc-wishlist-panel-close');
		if (wlPanelClose) {
			wlPanelClose.addEventListener('click', wlClosePanel);
		}

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && wlPanel.classList.contains('open')) {
				wlClosePanel();
			}
			wlTrapFocus(e);
		});
	}

	window.addEventListener('storage', function (e) {
		if (e.key === WL_KEY) {
			try {
				wlItems = JSON.parse(e.newValue) || [];
			} catch (err) {
				wlItems = [];
			}
			if (!Array.isArray(wlItems)) {
				wlItems = [];
			}
			wlItems = wlSanitize(wlItems);
			wlSyncHearts();
			wlRenderPanel();
		}
	});

	wlSyncHearts();
	wlRenderPanel();
})();
