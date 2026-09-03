/**
 * BongoStore BD - Core JavaScript
 * Handles cart interactions, gallery thumbnail switching, quantity adjustments,
 * dynamic delivery fee recalculations, and Bangladeshi mobile number validation.
 */

document.addEventListener('DOMContentLoaded', () => {
    initGallery();
    initQuantitySteppers();
    initDeliveryAreaSwitchers();
    initMobileNav();
    initCountdownTimer();
});

/**
 * Toast Notification System
 */
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgColor = type === 'success' ? '#047857' : (type === 'error' ? '#ef4444' : '#1e293b');
    const icon = type === 'success' ? '✓' : '!';

    toast.style.cssText = `
        background: ${bgColor};
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transform: translateY(12px);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: auto;
    `;

    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);

    // Animation in
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    // Auto remove after 3s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(12px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Quick Add to Cart via AJAX with UI fallback
 */
function addToCart(productId, quantity = 1, buttonElement = null) {
    if (buttonElement) {
        buttonElement.disabled = true;
        buttonElement.dataset.originalHtml = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> যোগ হচ্ছে...';
    }

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/cart.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update cart badges in header
            const cartCounters = document.querySelectorAll('.cart-counter');
            cartCounters.forEach(el => {
                el.textContent = data.cart_count;
                el.style.display = data.cart_count > 0 ? 'flex' : 'none';
            });

            showToast('পণ্যটি সফলভাবে শপিং কার্টে যুক্ত হয়েছে!', 'success');
        } else {
            showToast(data.message || 'কার্টে যোগ করতে সমস্যা হয়েছে', 'error');
        }
    })
    .catch(() => {
        // Fallback: regular form submit
        window.location.href = `/cart.php?action=add&id=${productId}&qty=${quantity}`;
    })
    .finally(() => {
        if (buttonElement && buttonElement.dataset.originalHtml) {
            buttonElement.innerHTML = buttonElement.dataset.originalHtml;
            buttonElement.disabled = false;
        }
    });
}

/**
 * Product Details Gallery Thumbnail Switcher
 */
function initGallery() {
    const mainImg = document.getElementById('pdp-main-image');
    const thumbnails = document.querySelectorAll('.thumbnail-btn');

    if (!mainImg || thumbnails.length === 0) return;

    thumbnails.forEach(btn => {
        btn.addEventListener('click', function () {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const targetSrc = this.getAttribute('data-full-img');
            if (targetSrc) {
                mainImg.src = targetSrc;
            }
        });
    });
}

/**
 * Quantity increment/decrement steppers
 */
function initQuantitySteppers() {
    document.querySelectorAll('.qty-stepper').forEach(stepper => {
        const minusBtn = stepper.querySelector('.qty-minus');
        const plusBtn = stepper.querySelector('.qty-plus');
        const input = stepper.querySelector('.qty-input');

        if (!input) return;

        if (minusBtn) {
            minusBtn.addEventListener('click', (e) => {
                e.preventDefault();
                let val = parseInt(input.value) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', (e) => {
                e.preventDefault();
                let val = parseInt(input.value) || 1;
                const max = parseInt(input.getAttribute('max')) || 99;
                if (val < max) {
                    input.value = val + 1;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    });
}

/**
 * Dynamic Delivery Fee & Total recalculation on Cart / Checkout
 */
function initDeliveryAreaSwitchers() {
    const deliveryRadios = document.querySelectorAll('input[name="delivery_area"]');
    if (deliveryRadios.length === 0) return;

    const deliveryDisplay = document.getElementById('display-delivery-charge');
    const grandTotalDisplay = document.getElementById('display-grand-total');
    const subtotalInput = document.getElementById('cart-subtotal-val');
    const discountInput = document.getElementById('cart-discount-val');

    function updateSummary() {
        let selectedArea = 'Inside Dhaka';
        deliveryRadios.forEach(radio => {
            if (radio.checked) {
                selectedArea = radio.value;
                const card = radio.closest('.radio-card');
                if (card) {
                    document.querySelectorAll('.radio-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                }
            }
        });

        const deliveryCharge = (selectedArea === 'Inside Dhaka') ? 70 : 130;
        const subtotal = subtotalInput ? parseFloat(subtotalInput.value) || 0 : 0;
        const discount = discountInput ? parseFloat(discountInput.value) || 0 : 0;
        const grandTotal = Math.max(0, subtotal + deliveryCharge - discount);

        if (deliveryDisplay) {
            deliveryDisplay.textContent = '৳' + deliveryCharge.toLocaleString('en-US');
        }
        if (grandTotalDisplay) {
            grandTotalDisplay.textContent = '৳' + grandTotal.toLocaleString('en-US');
        }
    }

    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });

    updateSummary();
}

/**
 * Mobile Navigation Drawer Toggle
 */
function initMobileNav() {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const navBar = document.getElementById('mobile-drawer');
    const backdrop = document.getElementById('drawer-backdrop');

    if (!toggleBtn || !navBar) return;

    function openDrawer() {
        navBar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        navBar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', openDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);

    const closeBtn = document.getElementById('drawer-close-btn');
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
}

/**
 * Flash Deal Countdown Timer (Simulated remaining offer time)
 */
function initCountdownTimer() {
    const hoursEl = document.getElementById('cd-hours');
    const minsEl = document.getElementById('cd-mins');
    const secsEl = document.getElementById('cd-secs');

    if (!hoursEl || !minsEl || !secsEl) return;

    let totalSeconds = (14 * 3600) + (35 * 60) + 40; // 14 hours 35 mins remaining

    setInterval(() => {
        if (totalSeconds <= 0) {
            totalSeconds = 24 * 3600;
        }
        totalSeconds--;

        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;

        hoursEl.textContent = String(h).padStart(2, '0');
        minsEl.textContent = String(m).padStart(2, '0');
        secsEl.textContent = String(s).padStart(2, '0');
    }, 1000);
}

/**
 * Bangladeshi 11-digit Mobile Number Validation
 * Format: 013, 014, 015, 016, 017, 018, 019 + 8 digits
 */
function validateBdPhone(phone) {
    const cleaned = phone.replace(/[^0-9]/g, '');
    const regex = /^(01[3-9]\d{8})$/;
    return regex.test(cleaned);
}
