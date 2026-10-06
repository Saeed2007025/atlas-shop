/**
 * Atlas Shop Checkout JavaScript
 * Dynamic Province & City Dropdowns + Province-based Shipping Methods Filtering
 */

document.addEventListener('DOMContentLoaded', function () {

    const provinceSelect = document.querySelector('#province-select');
    const citySelect = document.querySelector('#city-select');

    if (provinceSelect && citySelect) {
        function initCities(citiesData) {
            let pOptions = '<option value="">انتخاب استان...</option>';
            citiesData.forEach(item => {
                pOptions += `<option value="${item.province}">${item.province}</option>`;
            });
            provinceSelect.innerHTML = pOptions;

            const defaultProvince = provinceSelect.getAttribute('data-selected') || 'قم';
            provinceSelect.value = defaultProvince;
            updateCities(defaultProvince);
            filterShippingMethods(defaultProvince);

            provinceSelect.addEventListener('change', function () {
                const selProvince = this.value;
                updateCities(selProvince);
                filterShippingMethods(selProvince);
            });

            function updateCities(selectedProvince) {
                const found = citiesData.find(item => item.province === selectedProvince);
                if (found && found.cities) {
                    let cOptions = '<option value="">انتخاب شهر...</option>';
                    found.cities.forEach(city => {
                        cOptions += `<option value="${city}">${city}</option>`;
                    });
                    citySelect.innerHTML = cOptions;
                    const defaultCity = citySelect.getAttribute('data-selected') || 'قم';
                    citySelect.value = defaultCity;
                } else {
                    citySelect.innerHTML = '<option value="">ابتدا استان را انتخاب کنید</option>';
                }
            }
        }

        if (window.iranCitiesData) {
            initCities(window.iranCitiesData);
        } else {
            fetch('data/iran-cities.json')
                .then(res => res.json())
                .then(citiesData => initCities(citiesData))
                .catch(err => console.log('Cities load error', err));
        }
    }

    // Filter Shipping Methods Based on Selected Province
    function filterShippingMethods(provinceName) {
        const shippingCards = document.querySelectorAll('.shipping-option-card');
        let firstVisibleRadio = null;
        let isCurrentCheckedVisible = false;

        shippingCards.forEach(card => {
            const allowed = card.getAttribute('data-allowed-provinces') || 'all';
            const radio = card.querySelector('input[type="radio"]');

            if (allowed === 'all' || allowed === provinceName || allowed.includes(provinceName)) {
                card.style.display = 'flex';
                if (!firstVisibleRadio) firstVisibleRadio = radio;
                if (radio.checked) isCurrentCheckedVisible = true;
            } else {
                card.style.display = 'none';
                radio.checked = false;
                card.classList.remove('selected');
            }
        });

        // If the previously checked radio was hidden, auto-check the first visible one
        if (!isCurrentCheckedVisible && firstVisibleRadio) {
            firstVisibleRadio.checked = true;
            firstVisibleRadio.closest('.shipping-option-card').classList.add('selected');
        }

        updateShippingTotals();
    }

    // Shipping Method Selector Change Event
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    shippingRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.shipping-option-card').forEach(c => c.classList.remove('selected'));
            if (this.checked) {
                this.closest('.shipping-option-card').classList.add('selected');
            }
            updateShippingTotals();
        });
    });

    // Apply Coupon Code
    const applyCouponBtn = document.querySelector('#apply-coupon-btn');
    const couponInput = document.querySelector('#coupon-code-input');
    const couponMessage = document.querySelector('#coupon-message');

    if (applyCouponBtn && couponInput) {
        applyCouponBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const code = couponInput.value.trim();
            if (!code) return;

            const formData = new FormData();
            formData.append('code', code);

            fetch('api/coupon.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    couponMessage.innerHTML = `<span style="color:#84CC16; font-weight:bold;">${data.message}</span>`;
                    location.reload();
                } else {
                    couponMessage.innerHTML = `<span style="color:#EF4444; font-weight:bold;">${data.message}</span>`;
                }
            });
        });
    }

    function updateShippingTotals() {
        const selectedRadio = document.querySelector('input[name="shipping_method"]:checked');
        if (!selectedRadio) return;

        const card = selectedRadio.closest('.shipping-option-card');
        const cost = parseFloat(card.getAttribute('data-cost') || 0);
        const freeThreshold = parseFloat(card.getAttribute('data-free-threshold') || 0);
        const subtotal = parseFloat(document.querySelector('#checkout-subtotal-val')?.value || 0);

        let finalCost = cost;
        if (freeThreshold > 0 && subtotal >= freeThreshold) {
            finalCost = 0;
        }

        const shippingEl = document.querySelector('#checkout-shipping-cost-display');
        const totalEl = document.querySelector('#checkout-payable-total-display');
        const discountVal = parseFloat(document.querySelector('#checkout-discount-val')?.value || 0);

        if (shippingEl) {
            shippingEl.textContent = finalCost === 0 ? 'رایگان' : formatPriceJS(finalCost);
        }

        const payable = Math.max(0, subtotal - discountVal + finalCost);
        if (totalEl) {
            totalEl.textContent = formatPriceJS(payable);
        }
    }

    function formatPriceJS(num) {
        const en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        const fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        let formatted = new Intl.NumberFormat('en-US').format(num);
        for (let i = 0; i < 10; i++) {
            formatted = formatted.replaceAll(en[i], fa[i]);
        }
        return formatted + ' تومان';
    }

});
