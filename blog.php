<?php
/**
 * Fragrance Guide Blog Page
 */
require_once __DIR__ . '/config/config.php';

// Initialize session comments if not set
if (!isset($_SESSION['blog_comments'])) {
    $_SESSION['blog_comments'] = [
        1 => [
            ['name' => 'Sophia Loren', 'date' => 'June 15, 2026', 'comment' => 'This guide helped me understand why my perfume smells different after 2 hours! Top notes fade so fast.'],
            ['name' => 'Jean-Louis', 'date' => 'June 20, 2026', 'comment' => 'Excellent breakdown of the fragrance pyramid. Post more details about silage!']
        ],
        2 => [
            ['name' => 'Amara', 'date' => 'June 22, 2026', 'comment' => 'Oud is indeed liquid gold. Glad you mentioned Cambodian Oud harvesting sustainability.']
        ],
        3 => []
    ];
}

// Handle Comment Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $articleId = (int)$_POST['article_id'];
    $name = sanitize($_POST['name']);
    $comment = sanitize($_POST['comment']);

    if ($articleId >= 1 && $articleId <= 3 && !empty($name) && !empty($comment)) {
        $_SESSION['blog_comments'][$articleId][] = [
            'name' => $name,
            'date' => date('F d, Y'),
            'comment' => $comment
        ];
        setFlashMessage("success", "Comment posted successfully!");
        header("Location: blog.php#article-" . $articleId);
        exit;
    }
}

include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Scent Guides</span>
            <h1 class="luxury-font text-white display-5 mt-2">The AromaLuxe Blog</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Blog Articles List -->
    <div class="container py-5">
        <div class="row g-5 justify-content-center">
            
            <div class="col-lg-9">
                
                <!-- Article 1 -->
                <article class="glass-card p-4 p-md-5 mb-5" id="article-1">
                    <img src="assets/images/oud_perfume.png" alt="Choosing signature scent" class="img-fluid rounded border border-secondary mb-4 img-3d" style="height:350px; width:100%; object-fit:cover;">
                    
                    <div class="d-flex justify-content-between text-secondary small mb-2">
                        <span>June 15, 2026 • By Dr. Elena Rostova</span>
                        <span class="text-warning">FRAGRANCE PYRAMID</span>
                    </div>
                    
                    <h3 class="font-heading text-white mb-3">How to Choose Your Signature Scent</h3>
                    <p class="text-secondary">Your signature scent is more than just a pleasant odor; it is a molecular extension of your character. To find your ideal fragrance, you must first understand the <strong>fragrance pyramid</strong> consisting of Top, Heart, and Base notes.</p>
                    <p class="text-secondary mb-4">Top notes (like Bergamot, Lemon, and Lavender) hit your nose instantly but evaporate within 15 minutes. Heart notes (like Bulgarian Rose, Saffron, and Jasmine) emerge as the top notes fade, defining the perfume's theme for 2-3 hours. Finally, the Base notes (such as Amber, Cedarwood, Musk, and Oud) provide the foundation, lingering on skin for up to 24 hours. When shopping, spray a perfume on your wrist and let it sit for a full afternoon before making a purchase decision.</p>

                    <!-- Comments Accordion Section -->
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <h6 class="text-warning font-heading mb-3">Comments (<?php echo count($_SESSION['blog_comments'][1]); ?>)</h6>
                        
                        <!-- List -->
                        <div class="list-group list-group-flush mb-3">
                            <?php foreach ($_SESSION['blog_comments'][1] as $c): ?>
                                <div class="list-group-item bg-transparent text-light border-0 py-2 px-0">
                                    <div class="small"><strong><?php echo htmlspecialchars($c['name']); ?></strong> <span class="text-secondary text-nowrap ms-2" style="font-size:0.75rem;"><?php echo $c['date']; ?></span></div>
                                    <p class="small text-secondary m-0 mt-1"><?php echo htmlspecialchars($c['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Write comment form -->
                        <form method="POST" action="" class="row g-2 mt-2">
                            <input type="hidden" name="article_id" value="1">
                            <div class="col-sm-4">
                                <input type="text" name="name" class="form-control form-control-sm bg-transparent border-secondary text-white small" placeholder="Your name" required>
                            </div>
                            <div class="col-sm-6">
                                <input type="text" name="comment" class="form-control form-control-sm bg-transparent border-secondary text-white small" placeholder="Write comment..." required>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" name="add_comment" class="btn btn-sm btn-gold w-100 py-1">Post</button>
                            </div>
                        </form>
                    </div>
                </article>

                <!-- Article 2 -->
                <article class="glass-card p-4 p-md-5 mb-5" id="article-2">
                    <img src="assets/images/womens_perfume.png" alt="Liquid gold oud" class="img-fluid rounded border border-secondary mb-4 img-3d" style="height:350px; width:100%; object-fit:cover; filter: grayscale(20%);">
                    
                    <div class="d-flex justify-content-between text-secondary small mb-2">
                        <span>June 20, 2026 • By Amara Al-Jamil</span>
                        <span class="text-warning">ARABIC PERFUMERY</span>
                    </div>
                    
                    <h3 class="font-heading text-white mb-3">Oud: The Liquid Gold of Arabic Perfumery</h3>
                    <p class="text-secondary">Oud (or Agarwood) is one of the most expensive raw materials in the entire cosmetic world. Sourced from the resinous heartwood of Aquilaria trees infected with a specific mold, it is a complex, dark, animalic, and sweet resin that forms the backbone of traditional Arabic perfumes.</p>
                    <p class="text-secondary mb-4">Harvesting true wild Oud requires decades, which is why pure Oud oils fetch thousands of dollars per kilogram. At AromaLuxe, our <strong>Oud Imperial</strong> utilizes sustainably cultivated Cambodian Oudwood combined with saffron and rich amber to offer a modern, wearing-friendly silage that lasts all day.</p>

                    <!-- Comments Section -->
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <h6 class="text-warning font-heading mb-3">Comments (<?php echo count($_SESSION['blog_comments'][2]); ?>)</h6>
                        
                        <div class="list-group list-group-flush mb-3">
                            <?php foreach ($_SESSION['blog_comments'][2] as $c): ?>
                                <div class="list-group-item bg-transparent text-light border-0 py-2 px-0">
                                    <div class="small"><strong><?php echo htmlspecialchars($c['name']); ?></strong> <span class="text-secondary text-nowrap ms-2" style="font-size:0.75rem;"><?php echo $c['date']; ?></span></div>
                                    <p class="small text-secondary m-0 mt-1"><?php echo htmlspecialchars($c['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <form method="POST" action="" class="row g-2 mt-2">
                            <input type="hidden" name="article_id" value="2">
                            <div class="col-sm-4">
                                <input type="text" name="name" class="form-control form-control-sm bg-transparent border-secondary text-white small" placeholder="Your name" required>
                            </div>
                            <div class="col-sm-6">
                                <input type="text" name="comment" class="form-control form-control-sm bg-transparent border-secondary text-white small" placeholder="Write comment..." required>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" name="add_comment" class="btn btn-sm btn-gold w-100 py-1">Post</button>
                            </div>
                        </form>
                    </div>
                </article>

            </div>

        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
