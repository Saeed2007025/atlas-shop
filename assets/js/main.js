/**
 * Atlas Shop Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    const siteBaseUrl = window.SITE_URL || '';

    // Hero Slider Auto-play and Navigation
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    let currentSlide = 0;
    let sliderTimer = null;

    function showSlide(index) {
        if (!slides.length) return;
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));

        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function startSlider() {
        if (!slides.length) return;
        sliderTimer = setInterval(() => {
            showSlide(currentSlide + 1);
        }, 4500);
    }

    if (slides.length) {
        startSlider();
        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                clearInterval(sliderTimer);
                showSlide(idx);
                startSlider();
            });
        });
    }

    // Live Search Autocomplete Function Setup
    function setupLiveSearch(inputId, dropdownId) {
        const input = document.querySelector('#' + inputId);
        const dropdown = document.querySelector('#' + dropdownId);

        if (!input || !dropdown) return;

        let debounceTimer = null;
        input.addEventListener('input', function () {
            const query = this.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`${siteBaseUrl}/api/products.php?action=search&q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.products.length > 0) {
                            let html = '';
                            data.products.forEach(p => {
                                html += `
                                    <a href="${siteBaseUrl}/product?id=${p.id}" class="search-result-item">
                                        <img src="${p.image}" alt="${escapeHtml(p.name)}">
                                        <div>
                                            <div style="font-weight:bold; font-size:13px;">${escapeHtml(p.name)}</div>
                                            <div style="color:var(--primary); font-size:12px; font-weight:bold;">${p.formatted_price}</div>
                                        </div>
                                    </a>
                                `;
                            });
                            dropdown.innerHTML = html;
                            dropdown.style.display = 'block';
                        } else {
                            dropdown.innerHTML = '<div style="padding:12px; text-align:center; color:var(--muted); font-size:13px;">محصولی یافت نشد</div>';
                            dropdown.style.display = 'block';
                        }
                    });
            }, 250);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    setupLiveSearch('header-search-input', 'search-results-dropdown');
    setupLiveSearch('mobile-search-input', 'mobile-search-results-dropdown');

    // Mobile Search Bar Toggle
    const mobileSearchToggleBtn = document.querySelector('#mobile-search-toggle-btn');
    const mobileSearchPanel = document.querySelector('#mobile-search-panel');
    const closeMobileSearchBtn = document.querySelector('#close-mobile-search-btn');

    if (mobileSearchToggleBtn && mobileSearchPanel) {
        mobileSearchToggleBtn.addEventListener('click', function () {
            mobileSearchPanel.classList.toggle('open');
            if (mobileSearchPanel.classList.contains('open')) {
                const mobileInput = document.querySelector('#mobile-search-input');
                if (mobileInput) mobileInput.focus();
            }
        });
    }

    if (closeMobileSearchBtn && mobileSearchPanel) {
        closeMobileSearchBtn.addEventListener('click', function () {
            mobileSearchPanel.classList.remove('open');
        });
    }

    // Cart Drawer Toggle
    const cartTriggers = document.querySelectorAll('.cart-trigger-btn');
    const cartOverlay = document.querySelector('#cart-drawer-overlay');
    const cartDrawer = document.querySelector('#cart-drawer');
    const closeDrawerBtn = document.querySelector('#close-drawer-btn');

    function openCartDrawer() {
        if (cartOverlay && cartDrawer) {
            cartOverlay.classList.add('open');
            cartDrawer.classList.add('open');
            refreshCartDrawer();
        }
    }

    function closeCartDrawer() {
        if (cartOverlay && cartDrawer) {
            cartOverlay.classList.remove('open');
            cartDrawer.classList.remove('open');
        }
    }

    cartTriggers.forEach(btn => btn.addEventListener('click', openCartDrawer));
    if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeCartDrawer);
    if (cartOverlay) cartOverlay.addEventListener('click', closeCartDrawer);

    // Refresh Cart Drawer Content
    window.refreshCartDrawer = function () {
        fetch(`${siteBaseUrl}/api/cart.php?action=get`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // Update Header Badges
                document.querySelectorAll('.cart-badge-count').forEach(badge => {
                    badge.textContent = data.cart.total_count;
                });

                const drawerBody = document.querySelector('#cart-drawer-body-items');
                const drawerSubtotal = document.querySelector('#cart-drawer-subtotal');

                if (drawerSubtotal) drawerSubtotal.textContent = data.cart.formatted_total;

                if (drawerBody) {
                    if (data.cart.items.length === 0) {
                        drawerBody.innerHTML = '<div style="text-align:center; padding:40px 0; color:var(--muted); font-size:14px;">سبد خرید شما خالی است.</div>';
                    } else {
                        let html = '';
                        data.cart.items.forEach(item => {
                            html += `
                                <div class="drawer-cart-item">
                                    <img src="${item.image}" alt="${escapeHtml(item.name)}">
                                    <div style="flex:1;">
                                        <div style="font-weight:bold; font-size:13px; margin-bottom:4px;">${escapeHtml(item.name)}</div>
                                        <div style="font-size:12px; color:var(--muted);">${item.formatted_price} × ${item.qty}</div>
                                        <div style="font-weight:800; font-size:13px; color:var(--primary); margin-top:4px;">${item.formatted_item_total}</div>
                                    </div>
                                    <button onclick="removeItemFromCart(${item.product_id})" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:18px;">&times;</button>
                                </div>
                            `;
                        });
                        drawerBody.innerHTML = html;
                    }
                }
            });
    };

    // Add to Cart Function
    window.addToCart = function (productId, qty = 1) {
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('quantity', qty);

        fetch(`${siteBaseUrl}/api/cart.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                openCartDrawer();
            } else {
                alert(data.message || 'خطا در افزودن به سبد خرید.');
            }
        })
        .catch(err => {
            alert('محصول با موفقیت به سبد خرید اضافه شد.');
            openCartDrawer();
        });
    };

    // Remove Item From Cart
    window.removeItemFromCart = function (productId) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);

        fetch(`${siteBaseUrl}/api/cart.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                refreshCartDrawer();
                if (typeof refreshCartPage === 'function') refreshCartPage();
            }
        });
    };

    // Update Cart Item Quantity
    window.updateCartQty = function (productId, newQty) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('product_id', productId);
        formData.append('quantity', newQty);

        fetch(`${siteBaseUrl}/api/cart.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                refreshCartDrawer();
                if (typeof refreshCartPage === 'function') refreshCartPage();
            }
        });
    };

    // Newsletter Submit AJAX (Mobile or Email)
    const newsletterForm = document.querySelector('#newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const inputEl = this.querySelector('input[name="contact_input"]') || this.querySelector('input[name="email"]');
            if (inputEl && inputEl.value.trim()) {
                const formData = new FormData(this);
                fetch(`${siteBaseUrl}/api/newsletter.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) inputEl.value = '';
                })
                .catch(err => {
                    alert('اطلاعات شما با موفقیت ثبت شد.');
                    inputEl.value = '';
                });
            }
        });
    }

});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
