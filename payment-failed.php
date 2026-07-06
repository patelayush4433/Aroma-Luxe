<?php
/**
 * Payment Failed Landing Page
 */
require_once __DIR__ . '/config/config.php';
include_once __DIR__ . '/includes/header.php';
?>

    <div class="container py-5 text-center my-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                
                <div class="glass-card p-5 border border-danger shadow-lg">
                    <!-- Failure icon -->
                    <div class="mb-4">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center border border-danger bg-black text-danger" style="width: 80px; height: 80px; font-size: 2.5rem; box-shadow: 0 0 20px rgba(220,53,69,0.3);">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>

                    <span class="text-danger font-heading text-uppercase small tracking-widest">Transaction Declined</span>
                    <h2 class="font-heading text-white mt-2 mb-3">Payment Processing Failed</h2>
                    
                    <p class="text-secondary small mb-4">We were unable to authorize your payment transaction. Please verify card credentials, check account balances, or switch gateway methods.</p>

                    <div class="alert alert-secondary bg-transparent text-white-50 border-secondary small text-start p-3 mb-4">
                        <strong>Reason codes:</strong> Gateway simulated failure. If you scanned QR code, ensure timer did not elapse. Cards require 3-digit CVV formats.
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="checkout.php" class="btn btn-gold flex-fill"><i class="bi bi-arrow-repeat me-2"></i>Retry Checkout</a>
                        <a href="cart.php" class="btn btn-outline-secondary border-secondary text-white flex-fill">Modify Cart Bag</a>
                    </div>

                    <div class="mt-4 pt-3 border-top border-secondary">
                        <a href="index.php" class="small text-muted text-white-hover">Return to Landing Page</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
