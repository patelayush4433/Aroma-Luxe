/**
 * AromaLuxe Ultra-Premium Frontend Logic v2.0
 */

document.addEventListener('DOMContentLoaded', () => {

    // ═══════════════════════════════════════════════════
    // 1. CURSOR FOLLOWER — Gold dot that tracks mouse
    // ═══════════════════════════════════════════════════
    const cursor = document.querySelector('.cursor-follower');
    if (cursor && window.matchMedia('(pointer: fine)').matches) {
        let mouseX = 0, mouseY = 0;
        let cursorX = 0, cursorY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            if (!cursor.classList.contains('visible')) {
                cursor.classList.add('visible');
            }
        });

        // Smooth follow with lerp
        function animateCursor() {
            cursorX += (mouseX - cursorX) * 0.12;
            cursorY += (mouseY - cursorY) * 0.12;
            cursor.style.left = cursorX - 11 + 'px';
            cursor.style.top = cursorY - 11 + 'px';
            requestAnimationFrame(animateCursor);
        }
        animateCursor();

        // Expand on hoverable elements
        const hoverables = document.querySelectorAll('a, button, .btn, .product-card, .action-btn, .glass-card, .category-card');
        hoverables.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover-active'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover-active'));
        });
    }


    // ═══════════════════════════════════════════════════
    // 2. STICKY NAVBAR with smooth transition
    // ═══════════════════════════════════════════════════
    const navbar = document.querySelector('.navbar-luxury');
    if (navbar) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.scrollY;
            if (currentScroll > 60) {
                navbar.classList.add('sticky-active');
            } else {
                navbar.classList.remove('sticky-active');
            }
            lastScroll = currentScroll;
        }, { passive: true });
    }


    // ═══════════════════════════════════════════════════
    // 3. DARK/LIGHT THEME MANAGER
    // ═══════════════════════════════════════════════════
    const themeToggle = document.getElementById('themeToggleBtn');
    const currentTheme = localStorage.getItem('aromaluxe-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeToggleUI(currentTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            let activeTheme = document.documentElement.getAttribute('data-theme');
            let newTheme = activeTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('aromaluxe-theme', newTheme);
            updateThemeToggleUI(newTheme);
            showToast("Theme Updated", `Switched to ${newTheme} mode.`, "info");
        });
    }

    function updateThemeToggleUI(theme) {
        if (!themeToggle) return;
        if (theme === 'light') {
            themeToggle.innerHTML = '<i class="bi bi-moon-fill"></i>';
        } else {
            themeToggle.innerHTML = '<i class="bi bi-sun-fill"></i>';
        }
    }


    // ═══════════════════════════════════════════════════
    // 4. BACK TO TOP BUTTON
    // ═══════════════════════════════════════════════════
    const b2tBtn = document.getElementById('backToTopBtn');
    if (b2tBtn) {
        window.addEventListener('scroll', () => {
            b2tBtn.style.display = window.scrollY > 400 ? "block" : "none";
        }, { passive: true });
        b2tBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }


    // ═══════════════════════════════════════════════════
    // 5. HERO TEXT SPLIT ANIMATION
    // ═══════════════════════════════════════════════════
    const heroTitle = document.querySelector('.hero-title-animate');
    if (heroTitle) {
        const text = heroTitle.textContent.trim();
        heroTitle.textContent = '';
        heroTitle.style.visibility = 'visible';

        let charIndex = 0;
        // Split by words and preserve spaces
        const words = text.split(' ');
        words.forEach((word, wordIdx) => {
            const wordSpan = document.createElement('span');
            wordSpan.style.display = 'inline-block';
            wordSpan.style.whiteSpace = 'nowrap';

            [...word].forEach((char) => {
                const span = document.createElement('span');
                span.className = 'hero-title-char';
                span.textContent = char;
                span.style.animationDelay = `${charIndex * 0.04 + 0.3}s`;
                wordSpan.appendChild(span);
                charIndex++;
            });

            heroTitle.appendChild(wordSpan);

            // Add space between words (not after last word)
            if (wordIdx < words.length - 1) {
                const space = document.createTextNode('\u00A0');
                heroTitle.appendChild(space);
                charIndex++;
            }
        });
    }


    // ═══════════════════════════════════════════════════
    // 6. LIVE SEARCH HANDLER
    // ═══════════════════════════════════════════════════
    const searchInput = document.getElementById('navSearchInput');
    const searchDropdown = document.getElementById('navSearchDropdown');

    if (searchInput && searchDropdown) {
        let searchDebounce;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            const query = searchInput.value.trim();
            if (query.length < 2) {
                searchDropdown.style.display = 'none';
                return;
            }

            searchDebounce = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchDropdown.innerHTML = '';
                        if (data.length === 0) {
                            searchDropdown.innerHTML = '<div class="p-3 text-center text-muted">No fragrances matched.</div>';
                        } else {
                            data.forEach(item => {
                                searchDropdown.innerHTML += `
                                    <a href="product.php?id=${item.id}" class="search-item">
                                        <img src="${item.image}" alt="${item.name}">
                                        <div>
                                            <div class="fw-bold">${item.name}</div>
                                            <div class="text-muted small">${item.brand} • ${item.price}</div>
                                        </div>
                                    </a>
                                `;
                            });
                        }
                        searchDropdown.style.display = 'block';
                    })
                    .catch(err => console.error("Search API Error:", err));
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    }


    // ═══════════════════════════════════════════════════
    // 7. PRODUCT IMAGE ZOOM-ON-HOVER
    // ═══════════════════════════════════════════════════
    const zoomContainer = document.querySelector('.zoom-container');
    if (zoomContainer) {
        const zoomImg = zoomContainer.querySelector('img');
        zoomContainer.addEventListener('mousemove', (e) => {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            zoomImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            zoomImg.style.transform = "scale(1.8)";
        });
        zoomContainer.addEventListener('mouseleave', () => {
            zoomImg.style.transform = "scale(1)";
            zoomImg.style.transformOrigin = "center center";
        });
    }


    // ═══════════════════════════════════════════════════
    // 8. STAR REVIEW SELECTION
    // ═══════════════════════════════════════════════════
    const ratingStars = document.querySelectorAll('.rating-star-select i');
    const ratingInput = document.getElementById('reviewRatingInput');
    if (ratingStars && ratingInput) {
        ratingStars.forEach((star, idx) => {
            star.addEventListener('click', () => {
                ratingInput.value = idx + 1;
                ratingStars.forEach((s, i) => {
                    s.className = i <= idx
                        ? "bi bi-star-fill text-warning"
                        : "bi bi-star text-muted";
                });
            });
        });
    }


    // ═══════════════════════════════════════════════════
    // 9. SCROLL-TRIGGERED REVEAL with stagger
    // ═══════════════════════════════════════════════════
    const scrollElements = document.querySelectorAll('.animate-on-scroll');
    if (scrollElements.length > 0) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Stagger children if they exist
                    const children = entry.target.querySelectorAll('[class*="stagger-"]');
                    if (children.length > 0) {
                        children.forEach(child => child.classList.add('visible'));
                    }
                    entry.target.classList.add('visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { root: null, rootMargin: '0px', threshold: 0.08 });

        scrollElements.forEach(el => observer.observe(el));
    }


    // ═══════════════════════════════════════════════════
    // 10. PAGE FADE-IN
    // ═══════════════════════════════════════════════════
    requestAnimationFrame(() => {
        document.body.classList.add('loaded');
    });


    // ═══════════════════════════════════════════════════
    // 11. MAGNETIC BUTTON EFFECT
    // ═══════════════════════════════════════════════════
    const magneticBtns = document.querySelectorAll('.btn-gold, .btn-outline-gold');
    if (window.matchMedia('(pointer: fine)').matches) {
        magneticBtns.forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px) scale(1.02)`;
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = '';
            });
        });
    }


    // ═══════════════════════════════════════════════════
    // 12. SMOOTH PARALLAX SCROLL for hero
    // ═══════════════════════════════════════════════════
    const heroSection = document.querySelector('.hero-section');
    const heroContent = document.querySelector('.hero-content');
    const heroImageContainer = document.querySelector('.hero-image-container');

    if (heroSection && heroContent) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            const heroHeight = heroSection.offsetHeight;

            if (scrolled < heroHeight) {
                const factor = scrolled / heroHeight;
                if (heroContent) {
                    heroContent.style.transform = `translateY(${scrolled * 0.25}px)`;
                    heroContent.style.opacity = 1 - factor * 0.8;
                }
                if (heroImageContainer) {
                    heroImageContainer.style.transform = `translateY(${scrolled * 0.12}px)`;
                }
            }
        }, { passive: true });
    }

});


// ═══════════════════════════════════════════════════════
// GLOBAL FUNCTIONS (Outside DOMContentLoaded)
// ═══════════════════════════════════════════════════════

// 13. LUXURY TOAST NOTIFICATION SYSTEM
function showToast(title, message, type = "success") {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }

    const toastId = 'toast-' + Date.now();
    const iconMap = {
        success: 'bi-check-circle-fill text-success',
        danger: 'bi-x-circle-fill text-danger',
        warning: 'bi-exclamation-triangle-fill text-warning',
        info: 'bi-info-circle-fill text-info'
    };

    const toastHTML = `
        <div id="${toastId}" class="toast toast-luxury fade hide" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
            <div class="toast-header bg-dark text-white border-bottom border-secondary">
                <i class="bi ${iconMap[type]} me-2"></i>
                <strong class="me-auto font-heading" style="letter-spacing: 1px;">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-dark text-light small">
                ${message}
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHTML);
    const toastEl = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastEl);
    bsToast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}


// 14. AJAX CART OPERATIONS
function addToCart(productId, size, quantity = 1) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&size=${size}&quantity=${quantity}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToast("Added to Cart", data.message, "success");
            const badges = document.querySelectorAll('.cart-badge');
            badges.forEach(badge => {
                badge.innerText = data.total_items;
                badge.style.display = data.total_items > 0 ? 'inline-block' : 'none';
            });
        } else {
            showToast("Cart Error", data.message, "danger");
        }
    })
    .catch(err => showToast("Network Error", "Could not add item to cart.", "danger"));
}


// 15. AJAX WISHLIST OPERATIONS
function toggleWishlist(productId) {
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'added' || data.status === 'removed') {
            showToast("Wishlist Updated", data.message, data.status === 'added' ? "success" : "info");

            const wishlistBtns = document.querySelectorAll(`[data-wishlist-id="${productId}"]`);
            wishlistBtns.forEach(btn => {
                btn.innerHTML = data.status === 'added'
                    ? '<i class="bi bi-heart-fill text-danger"></i>'
                    : '<i class="bi bi-heart"></i>';
            });

            const wishlistBadges = document.querySelectorAll('.wishlist-badge');
            wishlistBadges.forEach(badge => {
                badge.innerText = data.total_items;
                badge.style.display = data.total_items > 0 ? 'inline-block' : 'none';
            });

            if (window.location.pathname.includes('profile.php')) {
                setTimeout(() => location.reload(), 800);
            }
        } else if (data.status === 'not_logged_in') {
            showToast("Wishlist Action", data.message, "warning");
            setTimeout(() => { window.location.href = 'auth/login.php'; }, 1000);
        } else {
            showToast("Wishlist Action", data.message, "warning");
        }
    })
    .catch(err => showToast("Network Error", "Could not toggle wishlist.", "danger"));
}


// 16. CART QUANTITY UPDATE
function updateItemQty(productId, size, newQty) {
    if (newQty < 1) {
        removeItemFromCart(productId, size);
        return;
    }
    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${productId}&size=${size}&quantity=${newQty}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            showToast("Cart Error", data.message, "danger");
        }
    })
    .catch(() => showToast("Network Error", "Could not update cart.", "danger"));
}


// 17. CART ITEM REMOVE with luxury modal
function removeItemFromCart(productId, size) {
    let modal = document.getElementById('removeItemModal');
    if (!modal) {
        const modalHTML = `
        <div class="modal fade" id="removeItemModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content bg-dark text-white border border-warning" style="border-radius: 16px;">
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-trash3 text-danger" style="font-size: 2.5rem;"></i>
                        <h6 class="font-heading text-white mt-3 mb-2">Remove Item</h6>
                        <p class="small text-secondary mb-4">Are you sure you want to remove this item from your shopping bag?</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-sm btn-danger px-4" id="confirmRemoveBtn">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modal = document.getElementById('removeItemModal');
    }

    const bsModal = new bootstrap.Modal(modal);
    const confirmBtn = document.getElementById('confirmRemoveBtn');
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

    newConfirmBtn.addEventListener('click', function() {
        bsModal.hide();
        fetch('api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=remove&product_id=${productId}&size=${size}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showToast("Cart Updated", "Item removed from cart.", "info");
                setTimeout(() => location.reload(), 800);
            } else {
                showToast("Cart Error", data.message || "Could not remove item.", "danger");
            }
        })
        .catch(() => showToast("Network Error", "Could not remove item. Please try again.", "danger"));
    });

    bsModal.show();
}


// ═══════════════════════════════════════════════════════
// 18. AI ASSISTANT CHAT
// ═══════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const aiBubble = document.getElementById('aiChatBubble');
    const aiWidget = document.getElementById('aiChatWidget');
    const closeAiBtn = document.getElementById('closeAiChatBtn');
    const aiForm = document.getElementById('aiChatForm');
    const aiInput = document.getElementById('aiChatInput');
    const aiMessages = document.getElementById('aiChatMessages');

    if (aiBubble && aiWidget) {
        aiBubble.addEventListener('click', () => aiWidget.classList.toggle('active'));
    }
    if (closeAiBtn && aiWidget) {
        closeAiBtn.addEventListener('click', () => aiWidget.classList.remove('active'));
    }

    function appendMessage(text, sender = 'bot', products = []) {
        if (!aiMessages) return;
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-message ${sender}`;
        msgDiv.innerHTML = text;

        if (products.length > 0) {
            const prodContainer = document.createElement('div');
            prodContainer.className = 'row g-2 mt-2 border-top border-secondary pt-2';
            products.forEach(p => {
                prodContainer.innerHTML += `
                    <div class="col-12 text-start">
                        <a href="product.php?id=${p.id}" class="d-flex align-items-center bg-black rounded p-2 border border-secondary text-decoration-none">
                            <img src="${p.image}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: contain; background: #000;" class="rounded me-2">
                            <div class="overflow-hidden">
                                <div class="text-white small fw-bold text-truncate" style="font-size:0.75rem;">${p.name}</div>
                                <div class="text-warning small" style="font-size:0.7rem;">${p.brand} • ${p.price}</div>
                            </div>
                        </a>
                    </div>
                `;
            });
            msgDiv.appendChild(prodContainer);
        }

        aiMessages.appendChild(msgDiv);
        aiMessages.scrollTop = aiMessages.scrollHeight;
    }

    window.sendQuickPrompt = function(promptText) {
        if (!aiInput) return;
        aiInput.value = promptText;
        if (aiForm) aiForm.dispatchEvent(new Event('submit'));
    };

    if (aiForm) {
        aiForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const rawMsg = aiInput.value.trim();
            if (!rawMsg) return;
            aiInput.value = '';
            appendMessage(rawMsg, 'user');

            const typingDiv = document.createElement('div');
            typingDiv.className = 'ai-message bot typing-indicator-msg';
            typingDiv.innerHTML = '<em>Thinking...</em>';
            aiMessages.appendChild(typingDiv);
            aiMessages.scrollTop = aiMessages.scrollHeight;

            fetch('api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: rawMsg })
            })
            .then(res => res.json())
            .then(data => {
                document.querySelectorAll('.typing-indicator-msg').forEach(ind => ind.remove());
                if (data.status === 'success') {
                    appendMessage(data.response, 'bot', data.products || []);
                } else {
                    appendMessage("Apologies, I encountered a communication problem. Please rephrase your query.", "bot");
                }
            })
            .catch(() => {
                document.querySelectorAll('.typing-indicator-msg').forEach(ind => ind.remove());
                appendMessage("A network issue prevented me from connecting. Please try again.", "bot");
            });
        });
    }
});
