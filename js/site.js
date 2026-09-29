/* Landing Page - raw JS, no libraries */
(function () {
    'use strict';

    var CFG = window.LP || {};
    var CURRENCY = CFG.currency || '৳';
    var SELECTED_FROM_CARD = parseInt(CFG.selectedProduct, 10) || 0;

    /* ----------------------------------------------------------- helpers */
    /* Mirrors the price() PHP helper: thousands separators, and the decimals are
       dropped when the amount is a whole number (1450 -> "৳1,450"). A real
       fractional part is kept rather than rounded away. */
    function money(value) {
        var n = Number(value) || 0;
        var text = Number.isInteger(n)
            ? String(n)
            : n.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
        return CURRENCY + text.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function scrollToCheckout() {
        var target = document.getElementById('checkout');
        if (!target) return;
        var top = target.getBoundingClientRect().top + window.pageYOffset - 16;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    function flashCard(id) {
        var card = document.querySelector('[data-card="' + id + '"]');
        if (!card) return;
        card.classList.add('product-card--active');
        window.setTimeout(function () { card.classList.remove('product-card--active'); }, 1600);
    }

    /* --------------------------------------------------- testimonial slider */
    function initSlider(root) {
        var track = root.querySelector('[data-slider-track]');
        var prev = root.querySelector('[data-slider-prev]');
        var next = root.querySelector('[data-slider-next]');
        var dotsBox = root.querySelector('[data-slider-dots]');
        if (!track) return;

        var slides = Array.prototype.slice.call(track.children);
        if (slides.length === 0) return;

        var index = 0;
        var perView = 3;
        var dots = [];
        var maxIndex = 0;

        function computePerView() {
            var w = window.innerWidth;
            if (w <= 720) return 1;
            if (w <= 1024) return 2;
            return 3;
        }

        function gap() {
            if (slides.length < 2) return 0;
            return parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0') || 0;
        }

        function buildDots() {
            if (!dotsBox) return;
            dotsBox.innerHTML = '';
            dots = [];
            for (var i = 0; i <= maxIndex; i++) {
                (function (i) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                    b.addEventListener('click', function () { go(i); });
                    dotsBox.appendChild(b);
                    dots.push(b);
                })(i);
            }
        }

        function measure() {
            perView = computePerView();
            maxIndex = Math.max(0, slides.length - perView);
            if (index > maxIndex) index = maxIndex;
            buildDots();
            update();
        }

        function update() {
            var shift = index * (100 / perView);
            track.style.transform = 'translateX(calc(-' + shift + '% - ' + (index * gap()) + 'px))';

            if (prev) prev.hidden = maxIndex === 0;
            if (next) next.hidden = maxIndex === 0;

            for (var i = 0; i < dots.length; i++) {
                dots[i].classList.toggle('is-active', i === index);
            }
        }

        function go(i) {
            index = Math.min(Math.max(i, 0), maxIndex);
            update();
        }

        if (prev) prev.addEventListener('click', function () { go(index - 1); });
        if (next) next.addEventListener('click', function () { go(index + 1); });

        window.addEventListener('resize', measure);
        measure();

        /* swipe + drag */
        var startX = 0, delta = 0, dragging = false;

        function down(x) { dragging = true; startX = x; delta = 0; track.classList.add('is-dragging'); }
        function move(x, ev) {
            if (!dragging) return;
            delta = x - startX;
            if (ev && ev.cancelable) ev.preventDefault();
        }
        function up() {
            if (!dragging) return;
            dragging = false;
            track.classList.remove('is-dragging');
            if (Math.abs(delta) > 45) go(index + (delta < 0 ? 1 : -1));
            delta = 0;
            update();
        }

        track.addEventListener('mousedown', function (e) { down(e.clientX); });
        window.addEventListener('mousemove', function (e) { move(e.clientX, e); });
        window.addEventListener('mouseup', up);
        track.addEventListener('touchstart', function (e) { down(e.touches[0].clientX); }, { passive: true });
        track.addEventListener('touchmove', function (e) { move(e.touches[0].clientX, e); }, { passive: false });
        track.addEventListener('touchend', up);
    }

    /* ------------------------------------------------ animated stat counters */
    function initCounter(el) {
        var to = parseFloat(el.dataset.toValue);
        if (isNaN(to)) return;

        var from = parseFloat(el.dataset.fromValue);
        if (isNaN(from)) from = 0;
        var duration = parseInt(el.dataset.duration, 10);
        if (isNaN(duration) || duration < 0) duration = 2000;
        var delimiter = el.dataset.delimiter || '';

        function format(n) {
            var rounded = Math.round(n);
            var out = String(rounded);
            if (delimiter) {
                out = out.replace(/\B(?=(\d{3})+(?!\d))/g, delimiter);
            }
            return out;
        }

        if (duration === 0) {
            el.textContent = format(to);
            return;
        }

        var start = null;

        function step(now) {
            if (start === null) start = now;
            var progress = Math.min((now - start) / duration, 1);
            /* easeOutExpo, so it races to the target then settles */
            var eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            el.textContent = format(from + (to - from) * eased);
            if (progress < 1) window.requestAnimationFrame(step);
        }

        function play() {
            el.textContent = format(from);
            window.requestAnimationFrame(step);
        }

        if (!('IntersectionObserver' in window)) {
            play();
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                observer.disconnect();
                play();
            });
        }, { threshold: 0.35 });

        observer.observe(el);
    }

    document.querySelectorAll('[data-slider]').forEach(initSlider);
    document.querySelectorAll('.elementor-counter-number[data-to-value]').forEach(initCounter);

    /* ------------------------------------------- live BD phone verification */
    /* Mirrors CheckoutController::normaliseBdPhone() — keep the two in sync. */
    function normaliseBdPhone(raw) {
        var s = String(raw == null ? '' : raw).replace(/[\s\-().]/g, '');

        if (/^(?:\+?00880|\+?880)/.test(s)) {
            s = s.replace(/^(?:\+?00880|\+?880)/, '');
            if (s !== '' && s.charAt(0) !== '0') s = '0' + s;
        }

        return /^01[3-9]\d{8}$/.test(s) ? s : null;
    }

    function initPhoneCheck(form) {
        var input = form.querySelector('[data-phone-input]');
        if (!input) return;

        var ok = form.querySelector('[data-phone-ok]');
        var hint = document.getElementById('phone-hint');
        var touched = false;

        function paint() {
            var raw = input.value.trim();

            if (raw === '') {
                if (ok) ok.hidden = true;
                if (hint) hint.classList.remove('is-bad');
                input.classList.remove('is-valid', 'is-invalid');
                return;
            }

            var valid = normaliseBdPhone(raw) !== null;
            input.classList.toggle('is-valid', valid);
            input.classList.toggle('is-invalid', !valid);
            if (ok) ok.hidden = !valid;
            if (hint) hint.classList.toggle('is-bad', !valid);
        }

        input.addEventListener('input', paint);
        input.addEventListener('blur', function () { touched = true; paint(); });
        paint();

        /* Client-side is a convenience only: the controller re-validates and
           normalises the same way, so a bypassed check still cannot store junk. */
        form.addEventListener('submit', function (e) {
            var raw = input.value.trim();
            if (raw === '' || normaliseBdPhone(raw) !== null) return;

            e.preventDefault();
            touched = true;
            paint();
            input.focus();
        });
    }

    /* ---------------------------------------------------- order form logic */
    var form = document.querySelector('[data-checkout-form]');
    if (!form) return;

    initPhoneCheck(form);

    var rows = Array.prototype.slice.call(form.querySelectorAll('[data-product-row]'));
    var list = form.querySelector('[data-summary-list]');
    var emptyNote = form.querySelector('[data-summary-empty]');
    var elSubtotal = form.querySelector('[data-summary-subtotal]');
    var elDelivery = form.querySelector('[data-summary-delivery]');
    var elTotal = form.querySelector('[data-summary-total]');
    var submitTotal = form.querySelector('[data-submit-total]');
    var submitBtn = form.querySelector('[data-submit]');
    var zoneRadios = Array.prototype.slice.call(form.querySelectorAll('[data-zone-radio]'));
    var zoneCards = Array.prototype.slice.call(form.querySelectorAll('[data-zone-card]'));

    function selectedZone() {
        for (var i = 0; i < zoneRadios.length; i++) {
            if (zoneRadios[i].checked) {
                var card = zoneRadios[i].closest('[data-zone-card]');
                return {
                    charge: card ? parseFloat(card.dataset.charge || '0') : 0,
                    name: card ? card.dataset.zoneName : ''
                };
            }
        }
        return { charge: 0, name: '' };
    }

    function qtyOf(row) {
        var input = row.querySelector('[data-row-qty]');
        var v = parseInt(input ? input.value : '1', 10);
        if (isNaN(v) || v < 1) v = 1;
        if (v > 99) v = 99;
        return v;
    }

    function setQty(row, value) {
        var input = row.querySelector('[data-row-qty]');
        if (input) input.value = value;
    }

    function isChecked(row) {
        var box = row.querySelector('[data-row-checkbox]');
        return !!(box && box.checked);
    }

    function refresh() {
        var picked = [];
        var subtotal = 0;

        rows.forEach(function (row) {
            var on = isChecked(row);
            row.classList.toggle('product-card--active', on);
            row.style.opacity = on ? '1' : '.62';

            var qty = qtyOf(row);
            var price = parseFloat(row.dataset.price || '0');
            var line = price * qty;

            var lineEl = row.querySelector('[data-row-line]');
            if (lineEl) lineEl.textContent = money(line);

            if (on) {
                subtotal += line;
                picked.push({
                    name: (row.querySelector('[data-row-name]') || {}).textContent || 'Product',
                    image: (row.querySelector('.picker-row__thumb') || {}).getAttribute
                        ? row.querySelector('.picker-row__thumb').getAttribute('src') : '',
                    price: price,
                    qty: qty,
                    line: line
                });
            }
        });

        var zone = selectedZone();
        var delivery = rows.length === 0 ? 0 : zone.charge;
        var total = subtotal + delivery;

        /* rebuild the order review table body */
        if (list) {
            list.innerHTML = '';

            if (picked.length === 0) {
                var blank = document.createElement('tr');
                blank.className = 'review__row review__row--empty';
                var blankCell = document.createElement('td');
                blankCell.setAttribute('colspan', '2');
                blankCell.innerHTML =
                    '<p class="summary__name">কোনো পণ্য নির্বাচন করা হয়নি</p>' +
                    '<span class="summary__meta">উপরে কোনো পণ্যে টিক দিন অথবা &ldquo;এখনই অর্ডার করুন&rdquo; বাটনে চাপুন।</span>';
                blank.appendChild(blankCell);
                list.appendChild(blank);
            } else {
                picked.forEach(function (p) {
                    var tr = document.createElement('tr');
                    tr.className = 'review__row';

                    var nameCell = document.createElement('td');
                    nameCell.className = 'review__col-product';

                    var wrap = document.createElement('div');
                    wrap.className = 'review__product';

                    var thumb = document.createElement('img');
                    thumb.className = 'review__thumb';
                    thumb.alt = '';
                    thumb.width = 48;
                    thumb.height = 48;
                    thumb.loading = 'lazy';
                    thumb.src = p.image || '';
                    wrap.appendChild(thumb);

                    var text = document.createElement('div');
                    text.className = 'review__text';

                    var name = document.createElement('span');
                    name.className = 'summary__name';
                    name.textContent = p.name;
                    text.appendChild(name);

                    var meta = document.createElement('span');
                    meta.className = 'summary__meta';
                    meta.textContent = money(p.price) + ' × ' + p.qty;
                    text.appendChild(meta);

                    wrap.appendChild(text);
                    nameCell.appendChild(wrap);
                    tr.appendChild(nameCell);

                    var totalCell = document.createElement('td');
                    totalCell.className = 'review__col-total';
                    totalCell.textContent = money(p.line);
                    tr.appendChild(totalCell);

                    list.appendChild(tr);
                });
            }
        }

        if (elSubtotal) elSubtotal.textContent = money(subtotal);
        if (elDelivery) elDelivery.textContent = delivery > 0 ? money(delivery) : (rows.length ? 'Free' : money(0));
        if (elTotal) elTotal.textContent = money(total);
        if (submitTotal) submitTotal.textContent = money(total);

        var nothingPicked = picked.length === 0 || rows.length === 0;
        if (submitBtn) submitBtn.disabled = nothingPicked;
    }

    /* row interactions */
    rows.forEach(function (row) {
        var box = row.querySelector('[data-row-checkbox]');
        var qty = row.querySelector('[data-row-qty]');

        if (box) {
            box.addEventListener('change', function () {
                if (box.checked && qty) {
                    var v = parseInt(qty.value, 10);
                    if (isNaN(v) || v < 1) qty.value = 1;
                }
                refresh();
                if (box.checked) {
                    trackAddToCart(row, qtyOf(row));
                }
            });
        }

        if (qty) {
            qty.addEventListener('change', function () {
                var v = parseInt(qty.value, 10);
                if (isNaN(v) || v < 1) v = 1;
                if (v > 99) v = 99;
                qty.value = v;
                refresh();
            });
            qty.addEventListener('input', refresh);
        }

        /* -/+ steppers. preventDefault matters: these buttons live inside the row's
           <label>, so without it the browser would also fire the label's default
           action and toggle the product checkbox on every tap. */
        Array.prototype.slice.call(row.querySelectorAll('[data-qty-up], [data-qty-down]'))
            .forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!qty) return;

                    var step = btn.hasAttribute('data-qty-up') ? 1 : -1;
                    var v = parseInt(qty.value, 10);
                    if (isNaN(v)) v = 1;
                    v += step;
                    if (v < 1) v = 1;
                    if (v > 99) v = 99;
                    qty.value = v;
                    refresh();
                });
            });
    });

    zoneRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            zoneCards.forEach(function (c) {
                var r = c.querySelector('[data-zone-radio]');
                c.style.opacity = (r && r.checked) ? '1' : '.75';
            });
            refresh();
        });
    });

    /* "Order Now" buttons on the product cards */
    document.querySelectorAll('[data-order-now]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.dataset.orderNow;
            var row = form.querySelector('[data-product-row="' + id + '"]');

            if (row) {
                var box = row.querySelector('[data-row-checkbox]');
                var wasChecked = box && box.checked;
                if (box && !box.checked) {
                    box.checked = true;
                }
                setQty(row, 1);
                refresh();
                flashCard(id);

                /* Programmatic .checked does not fire a change event, so AddToCart
                   is fired here rather than relying on the checkbox listener. */
                if (!wasChecked) {
                    trackAddToCart(row, 1);
                }
            }

            scrollToCheckout();

            var nameField = form.querySelector('#customer_name');
            if (nameField && !nameField.value) {
                window.setTimeout(function () { nameField.focus({ preventScroll: true }); }, 500);
            }
        });
    });

    /* ------------------------------------------------------- pixel tracking */
    function track(event, data) {
        if (typeof window.LPTrack === 'function') window.LPTrack(event, data || {});
    }

    function productData(id, price, qty) {
        return {
            value: Number(price) * Number(qty),
            currency: CURRENCY === '$' ? 'USD' : 'BDT',
            content_type: 'product',
            content_ids: [id],
            contents: [{ id: id, quantity: Number(qty), item_price: Number(price) }],
            num_items: Number(qty)
        };
    }

    function trackAddToCart(row, qty) {
        if (!row) return;
        track('AddToCart', productData(
            'product_' + row.dataset.productRow,
            row.dataset.price,
            qty
        ));
    }

    /* ViewContent: once per card, when it first scrolls into view. */
    if (typeof window.LPTrack === 'function') {
        var seen = document.querySelectorAll('[data-content-id]');

        if (seen.length && 'IntersectionObserver' in window) {
            var viewObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    viewObserver.unobserve(entry.target);
                    track('ViewContent', productData(
                        entry.target.dataset.contentId,
                        entry.target.dataset.contentPrice,
                        1
                    ));
                });
            }, { threshold: 0.5 });

            seen.forEach(function (el) { viewObserver.observe(el); });
        }

        /* InitiateCheckout: once, when the checkout form first becomes visible. */
        var checkout = document.getElementById('checkout');
        if (checkout && 'IntersectionObserver' in window) {
            var checkoutFired = false;
            var checkoutObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || checkoutFired) return;
                    checkoutFired = true;
                    checkoutObserver.disconnect();
                    track('InitiateCheckout', { currency: CURRENCY === '$' ? 'USD' : 'BDT' });
                });
            }, { threshold: 0.15 });

            checkoutObserver.observe(checkout);
        }
    }

    /* ?product=ID deep link support */
    if (SELECTED_FROM_CARD) {
        var deep = form.querySelector('[data-product-row="' + SELECTED_FROM_CARD + '"]');
        if (deep) {
            var deepBox = deep.querySelector('[data-row-checkbox]');
            if (deepBox) deepBox.checked = true;
            setQty(deep, 1);
        }
    }

    /* initial paint */
    zoneCards.forEach(function (c) {
        var r = c.querySelector('[data-zone-radio]');
        c.style.opacity = (r && r.checked) ? '1' : '.75';
    });
    refresh();
})();
