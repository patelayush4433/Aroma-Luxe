<?php
/**
 * Contact Us Page
 */
require_once __DIR__ . '/config/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all contact fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO `contact` (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            
            // simulated confirmation log
            sendSimulatedNotification(
                'Email',
                'patelayush4433@gmail.com',
                'New Support Ticket Raised - ' . htmlspecialchars($subject),
                "Sender: $name <$email>\n\nMessage:\n$message"
            );

            $success = "Your message has been sent to our support desk. We will respond within 24 hours.";
        } catch (PDOException $e) {
            $error = "Failed to submit. " . $e->getMessage();
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Connect</span>
            <h1 class="luxury-font text-white display-5 mt-2">Contact Us</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Main contact details and forms -->
    <div class="container py-5">
        <div class="row g-5">
            
            <!-- Left: Info panel details -->
            <div class="col-lg-5">
                <div class="glass-card p-4 p-md-5 h-100">
                    <h4 class="font-heading text-white mb-4">Our Boutique</h4>
                    
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="bi bi-geo-alt-fill text-warning fs-4"></i>
                        <div>
                            <h6 class="text-white font-heading mb-1">Address</h6>
                            <p class="small text-secondary m-0">Ground Floor, Block-A, Shop No. 13, Orbit Mall, Kudasan Road, Kudasan, Gandhinagar, Gujarat 382421</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="bi bi-telephone-fill text-warning fs-4"></i>
                        <div>
                            <h6 class="text-white font-heading mb-1">Phone</h6>
                            <p class="small text-secondary m-0">+91 7284077032</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="bi bi-envelope-fill text-warning fs-4"></i>
                        <div>
                            <h6 class="text-white font-heading mb-1">Email</h6>
                            <p class="small text-secondary m-0">patelayush4433@gmail.com</p>
                        </div>
                    </div> 

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <i class="bi bi-clock-fill text-warning fs-4"></i>
                        <div>
                            <h6 class="text-white font-heading mb-1">Boutique Hours</h6>
                            <p class="small text-secondary m-0">Monday - Saturday: 9:00 AM - 9:00 PM EST<br>Sunday: Closed</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-whatsapp text-warning fs-4"></i>
                        <div>
                            <h6 class="text-white font-heading mb-1">WhatsApp Chat</h6>
                            <a href="https://wa.me/917284077032" target="_blank" class="small text-warning text-decoration-underline">Direct Support Link</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Submission Form -->
            <div class="col-lg-7">
                <div class="glass-card p-4 p-md-5">
                    <h4 class="font-heading text-white mb-4">Send a Message</h4>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success bg-success-subtle border-0 text-success small py-2 mb-4"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger bg-danger-subtle border-0 text-danger small py-2 mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Your Name *</label>
                                <input type="text" name="name" class="form-control bg-transparent border-secondary text-white" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-white-50 small">Your Email *</label>
                                <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50 small">Subject *</label>
                                <input type="text" name="subject" class="form-control bg-transparent border-secondary text-white" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-white-50 small">Message *</label>
                                <textarea name="message" rows="5" class="form-control bg-transparent border-secondary text-white" placeholder="Describe your question, fragrance feedback or wholesale inquiries..." required></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-gold px-4 py-2 mt-2">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Google Map Iframe Integration -->
        <div class="row mt-5 pt-3">
            <div class="col-12">
                <div class="rounded border border-secondary overflow-hidden shadow-lg" style="height: 350px;">
                    <iframe src="https://maps.google.com/maps?q=23.1851127,72.6308695+(AROMALUXE%20-%20PERFUME%20STORE)&t=&z=15&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>

    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
