<?php
/**
 * Global Footer layout file
 */
?>
<?php if (!isset($no_visible_footer) || !$no_visible_footer): ?>
    <!-- Brand Partners Showcase -->
    <section class="py-5 border-top border-bottom border-secondary partners-section-luxury text-center">
        <div class="container">
            <h6 class="text-uppercase font-heading small mb-4" style="letter-spacing: 3px;">Our Fragrance Partners</h6>
            <div class="row align-items-center justify-content-center g-4">
                <div class="col-6 col-sm-4 col-md-2 luxury-font fs-5">CHANEL</div>
                <div class="col-6 col-sm-4 col-md-2 luxury-font fs-5">DIOR</div>
                <div class="col-6 col-sm-4 col-md-2 luxury-font fs-5">TOM FORD</div>
                <div class="col-6 col-sm-4 col-md-2 luxury-font fs-5">CREED</div>
                <div class="col-6 col-sm-4 col-md-2 luxury-font fs-5">GUCCI</div>
            </div>
        </div>
    </section>

    <!-- Main Footer -->
    <footer class="pt-5 pb-3 border-top border-secondary footer-luxury">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Col 1: About -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-warning font-heading mb-4">AROMALUXE</h5>
                    <p class="small text-muted mb-4">
                        AromaLuxe is a curated boutique for luxury, niche, and designer fragrances. We craft and select masterworks that define identity, emotion, and memory.
                    </p>
                    <div class="d-flex gap-3 fs-5">
                        <a href="#" class="text-secondary-hover text-white"><i class="bi bi-facebook"></i></a>
                        <a href="https://instagram.com" target="_blank" class="text-secondary-hover text-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-secondary-hover text-white"><i class="bi bi-pinterest"></i></a>
                        <a href="#" class="text-secondary-hover text-white"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>

                <!-- Col 2: Customer Care Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white font-heading text-uppercase mb-4 small" style="letter-spacing: 1.5px;">Boutique</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="shop.php" class="text-muted text-white-hover">All Fragrances</a></li>
                        <li class="mb-2"><a href="shop.php?category=mens-perfume" class="text-muted text-white-hover">Men's Perfume</a></li>
                        <li class="mb-2"><a href="shop.php?category=womens-perfume" class="text-muted text-white-hover">Women's Perfume</a></li>
                        <li class="mb-2"><a href="shop.php?category=unisex" class="text-muted text-white-hover">Unisex Blends</a></li>
                        <li class="mb-2"><a href="shop.php?category=gift-sets" class="text-muted text-white-hover">Gift Sets</a></li>
                    </ul>
                </div>

                <!-- Col 3: Policy Page Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white font-heading text-uppercase mb-4 small" style="letter-spacing: 1.5px;">Policies & FAQs</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="about.php" class="text-muted text-white-hover">Our Story</a></li>
                        <li class="mb-2"><a href="#" onclick="alert('Privacy Policy: All customer credentials, card details and referral inputs are stored securely under salted password hashing models.'); return false;" class="text-muted text-white-hover">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" onclick="alert('Terms of Service: Deliveries will be scheduled within the dates and times chosen at the checkout portal.'); return false;" class="text-muted text-white-hover">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="#" onclick="alert('Refund Policy: We offer a 30-day return policy for unopened items.'); return false;" class="text-muted text-white-hover">Refund Policy</a></li>
                        <li class="mb-2"><a href="#" onclick="alert('Shipping Policy: Free standard shipping on orders over ₹8,350. Deliveries contain secure bubble packaging.'); return false;" class="text-muted text-white-hover">Shipping Info</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white font-heading text-uppercase mb-4 small" style="letter-spacing: 1.5px;">Newsletter Sign-up</h6>
                    <p class="small text-muted mb-3">Subscribe to receive early releases, private sales invitations, and fragrance tips.</p>
                    
                    <form id="footerNewsletterForm" class="input-group">
                        <input type="email" id="newsletterEmailInput" class="form-control bg-transparent border-secondary text-white small" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-gold py-1">Subscribe</button>
                    </form>
                    <div id="newsletterStatusMessage" class="small mt-2" style="display:none;"></div>
                </div>
            </div>

            <hr class="bg-secondary mb-4">

            <!-- Footer copyright and business hours info -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start small text-muted">
                    &copy; <?php echo date('Y'); ?> AromaLuxe Inc. All Rights Reserved. Designed for elegance.
                </div>
                <div class="col-md-6 text-center text-md-end small text-muted">
                    Business Hours: Mon - Sat: 9:00 AM - 9:00 PM EST | Support: (800) 799-2766
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

    <!-- Back to Top Button -->
    <button id="backToTopBtn" title="Go to top"><i class="bi bi-arrow-up"></i></button>

    <!-- Floating WhatsApp Bubble -->
    <a href="https://wa.me/917284077032?text=Hello%20AromaLuxe,%20I'm%20inquiring%20about%20your%20luxury%20perfumes." target="_blank" class="whatsapp-chat-bubble" title="Chat on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Floating AI Assistant Bubble -->
    <div id="aiChatBubble" class="ai-chat-bubble" title="Ask AI Assistant">
        <i class="bi bi-robot"></i>
    </div>

    <!-- AI Chat Window Widget -->
    <div id="aiChatWidget" class="ai-chat-widget">
        <div class="ai-chat-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="ai-avatar">
                    <i class="bi bi-robot text-warning"></i>
                </div>
                <div class="text-start">
                    <h6 class="font-heading m-0 text-white" style="font-size: 0.85rem; letter-spacing: 1px;">AromaLuxe AI Guide</h6>
                    <span class="small text-success" style="font-size: 0.65rem;">● Online & ready</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white text-white-50" id="closeAiChatBtn" aria-label="Close" style="font-size: 0.75rem;"></button>
        </div>
        <div class="ai-chat-body" id="aiChatMessages">
            <div class="ai-message bot">
                Hello! I am your AromaLuxe Scent Guide. Ask me for recommendations (e.g., <em>"Suggest an Oud fragrance"</em>) or store policies.
            </div>
        </div>
        <div class="ai-chat-chips d-flex gap-2 overflow-x-auto py-2 px-3 border-top border-secondary" id="aiChatChips">
            <span class="badge bg-secondary-subtle text-secondary-hover cursor-pointer py-1 px-2 text-warning border border-warning" onclick="sendQuickPrompt('best perfume')">Best Perfume</span>
            <span class="badge bg-secondary-subtle text-secondary-hover cursor-pointer py-1 px-2 text-warning border border-warning" onclick="sendQuickPrompt('high demand perfume')">High Demand</span>
            <span class="badge bg-secondary-subtle text-secondary-hover cursor-pointer py-1 px-2 text-warning border border-warning" onclick="sendQuickPrompt('regular perfume')">Regular Perfumes</span>
            <span class="badge bg-secondary-subtle text-secondary-hover cursor-pointer py-1 px-2 text-warning border border-warning" onclick="sendQuickPrompt('woody fragrance')">Woody Scents</span>
            <span class="badge bg-secondary-subtle text-secondary-hover cursor-pointer py-1 px-2 text-warning border border-warning" onclick="sendQuickPrompt('coupon code')">Coupons</span>
        </div>
        <form class="ai-chat-input-area d-flex border-top border-secondary" id="aiChatForm">
            <input type="text" id="aiChatInput" class="form-control bg-transparent border-0 text-white small" placeholder="Type a message..." required autocomplete="off">
            <button type="submit" class="btn btn-gold py-1 px-3 border-0 rounded-0"><i class="bi bi-send-fill text-dark"></i></button>
        </form>
    </div>

    <!-- Simulated Notifications Drawer (Logs Debugger) -->
    <?php if (isset($_SESSION['notifications_log']) && count($_SESSION['notifications_log']) > 0): ?>
        <div class="notification-bubble" data-bs-toggle="modal" data-bs-target="#notificationsLogModal">
            <i class="bi bi-bell-fill text-warning animated-bell"></i>
            <span class="small font-heading d-none d-sm-inline">Simulated Inbox (<?php echo count($_SESSION['notifications_log']); ?>)</span>
        </div>

        <!-- Notification Center Modal -->
        <div class="modal fade" id="notificationsLogModal" tabindex="-1" aria-labelledby="notificationsLogModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-dark text-white border border-warning">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title font-heading text-warning" id="notificationsLogModalLabel">
                            <i class="bi bi-envelope-paper-fill me-2"></i>Simulated Notifications Center
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">
                        <div class="alert alert-secondary m-3 p-2 small border-0 text-dark">
                            <strong>Note:</strong> Since actual SMTP/SMS servers require external APIs, this panel displays all system notifications (OTP tokens, order confirmations, referral bonuses) in real-time.
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_reverse($_SESSION['notifications_log']) as $log): ?>
                                <div class="list-group-item bg-transparent text-white border-secondary p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-outline-gold text-warning border border-warning"><?php echo $log['type']; ?></span>
                                        <span class="small text-muted"><?php echo $log['time']; ?></span>
                                    </div>
                                    <div class="small fw-bold">To: <span class="text-secondary"><?php echo $log['recipient']; ?></span></div>
                                    <div class="small fw-bold">Subject: <span class="text-white-50"><?php echo $log['subject']; ?></span></div>
                                    <div class="mt-2 p-2 bg-black rounded text-warning small font-monospace" style="font-size: 0.8rem; border-left: 3px solid var(--gold);">
                                        <?php echo nl2br(htmlspecialchars($log['body'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <form method="POST" action="">
                            <input type="hidden" name="clear_simulated_logs" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Clear Logs</button>
                        </form>
                        <button type="button" class="btn btn-sm btn-gold" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        // Clear logic for logs
        if (isset($_POST['clear_simulated_logs'])) {
            unset($_SESSION['notifications_log']);
            echo "<script>window.location.reload();</script>";
        }
        ?>
    <?php endif; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Premium Script JS -->
    <script src="assets/js/main.js"></script>

    <!-- AJAX Newsletter Submission script -->
    <script>
        const newsForm = document.getElementById('footerNewsletterForm');
        if (newsForm) {
            newsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const emailInput = document.getElementById('newsletterEmailInput').value.trim();
                const statusDiv = document.getElementById('newsletterStatusMessage');

                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=newsletter_subscribe&email=${encodeURIComponent(emailInput)}`
                })
                .then(res => res.json())
                .then(data => {
                    statusDiv.style.display = 'block';
                    if (data.status === 'success') {
                        statusDiv.className = "small mt-2 text-success";
                        statusDiv.innerText = data.message;
                        newsForm.reset();
                    } else {
                        statusDiv.className = "small mt-2 text-danger";
                        statusDiv.innerText = data.message;
                    }
                })
                .catch(err => {
                    statusDiv.style.display = 'block';
                    statusDiv.className = "small mt-2 text-danger";
                    statusDiv.innerText = "Network error. Please try again.";
                });
            });
        }
    </script>

    <!-- 3D Tilt Effect for Product Cards -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const rotateX = ((y - centerY) / centerY) * -6;
                    const rotateY = ((x - centerX) / centerX) * 6;
                    card.style.transform = `translateY(-8px) perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                });
            });
        });
    </script>
</body>
</html>
