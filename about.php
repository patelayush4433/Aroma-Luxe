<?php
/**
 * About Us Page
 */
require_once __DIR__ . '/config/config.php';
include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black border-bottom border-secondary text-center">
        <div class="container">
            <span class="text-warning text-uppercase small tracking-widest">Heritage</span>
            <h1 class="luxury-font text-white display-5 mt-2">About AromaLuxe</h1>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>
    </div>

    <!-- Company Story Section -->
    <div class="container py-5">
        <div class="row align-items-center g-5 py-4">
            <div class="col-lg-6">
                <span class="text-warning font-heading text-uppercase small tracking-wide">Our Origin Story</span>
                <h2 class="font-heading text-white mt-2 mb-4">Born from a passion for botanical alchemy.</h2>
                <p class="text-secondary mb-3">Founded in 2018, AromaLuxe was created to reconnect modern scent selection with the raw beauty of natural botanicals. We reject mass-production shortcuts, working directly with local flower gardens in Grasse and sustainable oud plantations in Southeast Asia.</p>
                <p class="text-secondary mb-4">Every bottle represents months of careful filtration, aging, and blend adjustments, ensuring that the final accord is a masterpiece that develops uniquely on your skin.</p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="border-start border-warning ps-3">
                            <h3 class="text-white font-heading m-0">15+</h3>
                            <span class="small text-secondary text-uppercase">Private Blends</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-warning ps-3">
                            <h3 class="text-white font-heading m-0">98%</h3>
                            <span class="small text-secondary text-uppercase">Botanical Oils</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 text-center">
                <img src="assets/images/womens_perfume.png" alt="Botanical extraction" class="img-fluid rounded border border-secondary shadow img-3d" style="max-height: 400px; object-fit: cover;">
            </div>
        </div>
    </div>

    <!-- Vision, Mission and Awards Blocks -->
    <section class="py-5" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
        <div class="container py-4">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <i class="bi bi-eye-fill fs-2 text-warning"></i>
                        <h4 class="font-heading text-white mt-3 mb-2">Our Vision</h4>
                        <p class="small text-secondary">To become the world's premiere artisan perfume house, championing raw natural botanicals over synthesized shortcuts.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <i class="bi bi-shield-fill-check fs-2 text-warning"></i>
                        <h4 class="font-heading text-white mt-3 mb-2">Our Mission</h4>
                        <p class="small text-secondary">To curate and bottle high-potency perfumes that provide distinct sensory identities, using ethical and sustainable sourcing.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <i class="bi bi-trophy-fill fs-2 text-warning"></i>
                        <h4 class="font-heading text-white mt-3 mb-2">Awards Won</h4>
                        <p class="small text-secondary">Winner of the International Niche Fragrance of the Year (2024) for our signature 'Oud Imperial' blend.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfumery Team Section -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-warning text-uppercase small tracking-widest">Master Artisans</span>
            <h2 class="text-white font-heading mt-2">Meet Our Perfumers</h2>
            <div class="mx-auto bg-warning mt-3" style="width: 50px; height: 1.5px;"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <img src="assets/images/mens_perfume.png" alt="Master Perfumer" class="rounded-circle mb-3 border border-secondary img-3d-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="text-white font-heading mb-1">Dr. Elena Rostova</h5>
                    <span class="text-warning small text-uppercase tracking-wider">Chief Scent Architect</span>
                    <p class="small text-secondary mt-3">With 15 years in Grasse development hubs, Elena leads our organic notes harvesting and distillation processes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <img src="assets/images/womens_perfume.png" alt="Master Perfumer" class="rounded-circle mb-3 border border-secondary img-3d-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="text-white font-heading mb-1">Jean-Louis Martin</h5>
                    <span class="text-warning small text-uppercase tracking-wider">Accord Formulator</span>
                    <p class="small text-secondary mt-3">An expert in woody base notes, Jean-Louis designs the longevity and silage ratios of our designer collections.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <img src="assets/images/unisex_perfume.png" alt="Master Perfumer" class="rounded-circle mb-3 border border-secondary img-3d-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="text-white font-heading mb-1">Amara Al-Jamil</h5>
                    <span class="text-warning small text-uppercase tracking-wider">Oud Specialist</span>
                    <p class="small text-secondary mt-3">Hailing from Dubai, Amara blends traditional Arabian spices, amber oils, and premium agarwood accords.</p>
                </div>
            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
