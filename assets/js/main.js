/**
 * AromaLuxe Site-Wide Frontend Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Header
    const navbar = document.querySelector('.navbar-luxury');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('sticky-active');
            } else {
                navbar.classList.remove('sticky-active');
            }
        });
    }

    // 2. Dark/Light Theme Manager
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

    // 3. Back to Top Button
    const b2tBtn = document.getElementById('backToTopBtn');
    if (b2tBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                b2tBtn.style.display = "block";
            } else {
                b2tBtn.style.display = "none";
            }
        });
        b2tBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // 4. Live Search Handler
    const searchInput = document.getElementById('navSearchInput');
    const searchDropdown = document.getElementById('navSearchDropdown');
    
    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                searchDropdown.style.display = 'none';
                return;
            }

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
        });

        // Close search list if user clicks away
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    }

    // 5. Product Image Zoom-on-Hover
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

    // 6. Interactive Star Review Form Selection
    const ratingStars = document.querySelectorAll('.rating-star-select i');
    const ratingInput = document.getElementById('reviewRatingInput');
    if (ratingStars && ratingInput) {
        ratingStars.forEach((star, idx) => {
            star.addEventListener('click', () => {
                ratingInput.value = idx + 1;
                ratingStars.forEach((s, i) => {
                    if (i <= idx) {
                        s.className = "bi bi-star-fill text-warning";
                    } else {
                        s.className = "bi bi-star text-muted";
                    }
                });
            });
        });
    }

    // 6b. Scroll-triggered entrance observer
    const scrollElements = document.querySelectorAll('.animate-on-scroll');
    if (scrollElements.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        scrollElements.forEach(el => observer.observe(el));
    }

    // 6c. Page Fade-In Entrance
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 50);
});

// 7. Site-Wide Luxury Toast Notifications Generator
function showToast(title, message, type = "success") {
    // Create container if missing
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
                <strong class="me-auto font-heading">${title}</strong>
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

    // Cleanup toast element from DOM after hide
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// 8. AJAX Shopping Cart Operations
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
            // Update cart badges
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

// 9. AJAX Wishlist Operations
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
            
            // Update specific button icons
            const wishlistBtns = document.querySelectorAll(`[data-wishlist-id="${productId}"]`);
            wishlistBtns.forEach(btn => {
                if (data.status === 'added') {
                    btn.innerHTML = '<i class="bi bi-heart-fill text-danger"></i>';
                } else {
                    btn.innerHTML = '<i class="bi bi-heart"></i>';
                }
            });
            
            // Update wishlist badge in navbar
            const wishlistBadges = document.querySelectorAll('.wishlist-badge');
            wishlistBadges.forEach(badge => {
                badge.innerText = data.total_items;
                badge.style.display = data.total_items > 0 ? 'inline-block' : 'none';
            });

            // If we are on profile.php, reload after a brief delay to reflect changes inside the list
            if (window.location.pathname.includes('profile.php')) {
                setTimeout(() => location.reload(), 800);
            }
        } else if (data.status === 'not_logged_in') {
            showToast("Wishlist Action", data.message, "warning");
            setTimeout(() => {
                window.location.href = 'auth/login.php';
            }, 1000);
        } else {
            showToast("Wishlist Action", data.message, "warning");
        }
    })
    .catch(err => showToast("Network Error", "Could not toggle wishlist.", "danger"));
}

// 10. Cart Quantity Update (used in cart.php)
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

// 11. Cart Item Remove (used in cart.php)
function removeItemFromCart(productId, size) {
    // Create a luxury-styled Bootstrap modal for confirmation instead of browser confirm()
    let modal = document.getElementById('removeItemModal');
    if (!modal) {
        const modalHTML = `
        <div class="modal fade" id="removeItemModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content bg-dark text-white border border-warning" style="border-radius: 12px;">
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

    // Remove any previous click handlers
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

// 12. Floating AI Assistant Chat Widget Toggle and Communication
document.addEventListener('DOMContentLoaded', () => {
    const aiBubble = document.getElementById('aiChatBubble');
    const aiWidget = document.getElementById('aiChatWidget');
    const closeAiBtn = document.getElementById('closeAiChatBtn');
    const aiForm = document.getElementById('aiChatForm');
    const aiInput = document.getElementById('aiChatInput');
    const aiMessages = document.getElementById('aiChatMessages');

    if (aiBubble && aiWidget) {
        aiBubble.addEventListener('click', () => {
            aiWidget.classList.toggle('active');
        });
    }

    if (closeAiBtn && aiWidget) {
        closeAiBtn.addEventListener('click', () => {
            aiWidget.classList.remove('active');
        });
    }

    // Append message element to chat window
    function appendMessage(text, sender = 'bot', products = []) {
        if (!aiMessages) return;
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-message ${sender}`;
        msgDiv.innerHTML = text;

        // If there are products returned, render them beautifully
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

    // Global helper for chips click
    window.sendQuickPrompt = function(promptText) {
        if (!aiInput) return;
        aiInput.value = promptText;
        if (aiForm) {
            aiForm.dispatchEvent(new Event('submit'));
        }
    };

    if (aiForm) {
        aiForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const rawMsg = aiInput.value.trim();
            if (!rawMsg) return;

            // Clear input
            aiInput.value = '';

            // Render user message
            appendMessage(rawMsg, 'user');

            // Render typing indicator
            const typingDiv = document.createElement('div');
            typingDiv.className = 'ai-message bot typing-indicator-msg';
            typingDiv.innerHTML = '<em>Thinking...</em>';
            aiMessages.appendChild(typingDiv);
            aiMessages.scrollTop = aiMessages.scrollHeight;

            // Fetch bot response
            fetch('api/ai-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: rawMsg })
            })
            .then(res => res.json())
            .then(data => {
                // Remove typing indicator
                const indicators = document.querySelectorAll('.typing-indicator-msg');
                indicators.forEach(ind => ind.remove());

                if (data.status === 'success') {
                    appendMessage(data.response, 'bot', data.products || []);
                } else {
                    appendMessage("Apologies, I encountered a communication problem. Please rephrase your query.", "bot");
                }
            })
            .catch(() => {
                const indicators = document.querySelectorAll('.typing-indicator-msg');
                indicators.forEach(ind => ind.remove());
                appendMessage("A network issue prevented me from connecting to the perfume house. Please try again.", "bot");
            });
        });
    }
});
