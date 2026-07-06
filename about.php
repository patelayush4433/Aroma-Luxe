<?php
/**
 * About Us Page
 */
require_once __DIR__ . '/config/config.php';
include_once __DIR__ . '/includes/header.php';
?>

    <!-- Page Title Header -->
    <div class="py-5 bg-black text-center animate-on-scroll" style="padding-top: 5rem !important; padding-bottom: 5rem !important;">
        <div class="container">
            <span class="section-label">Heritage &amp; Craft</span>
            <h1 class="font-display text-white display-4 mt-3 text-luxury-glow">About AromaLuxe</h1>
            <div class="section-heading-line mx-auto mt-3"></div>
            <p class="text-secondary fs-5 mt-4 mx-auto" style="max-width: 680px; font-family: var(--font-body);">
                Where ancient botanical alchemy meets modern luxury — crafting fragrances that become part of your identity.
            </p>
        </div>
    </div>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Company Story Section -->
    <div class="container py-5">
        <div class="row align-items-center g-5 py-4 animate-on-scroll">
            <div class="col-lg-6">
                <span class="section-label">Our Origin Story</span>
                <h2 class="font-display text-white mt-3 mb-4" style="font-size: 2.2rem;">Born from a passion for botanical alchemy.</h2>
                <p class="text-secondary mb-3" style="line-height: 1.9;">Founded in 2018, AromaLuxe was created to reconnect modern scent selection with the raw beauty of natural botanicals. We reject mass-production shortcuts, working directly with local flower gardens in Grasse and sustainable oud plantations in Southeast Asia.</p>
                <p class="text-secondary mb-4" style="line-height: 1.9;">Every bottle represents months of careful filtration, aging, and blend adjustments, ensuring that the final accord is a masterpiece that develops uniquely on your skin.</p>
                
                <div class="row g-4 mt-2">
                    <div class="col-6 col-sm-3">
                        <div style="border-left: 2px solid var(--gold); padding-left: 16px;">
                            <h3 class="font-display m-0" style="background: linear-gradient(135deg, var(--gold-light), var(--gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 2rem;">15+</h3>
                            <span class="small text-secondary text-uppercase" style="letter-spacing: 1.5px; font-size: 0.7rem;">Private Blends</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="border-left: 2px solid var(--rose-gold); padding-left: 16px;">
                            <h3 class="font-display m-0" style="background: linear-gradient(135deg, var(--rose-gold), var(--gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 2rem;">98%</h3>
                            <span class="small text-secondary text-uppercase" style="letter-spacing: 1.5px; font-size: 0.7rem;">Botanical Oils</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="border-left: 2px solid var(--amethyst); padding-left: 16px;">
                            <h3 class="font-display m-0" style="background: linear-gradient(135deg, var(--amethyst), var(--gold-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 2rem;">6+</h3>
                            <span class="small text-secondary text-uppercase" style="letter-spacing: 1.5px; font-size: 0.7rem;">Years of Craft</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div style="border-left: 2px solid var(--champagne); padding-left: 16px;">
                            <h3 class="font-display m-0" style="background: linear-gradient(135deg, var(--champagne), var(--gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 2rem;">50K+</h3>
                            <span class="small text-secondary text-uppercase" style="letter-spacing: 1.5px; font-size: 0.7rem;">Happy Clients</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 text-center">
                <img src="assets/images/womens_perfume.png" alt="Botanical extraction" class="img-fluid rounded border border-secondary shadow img-3d" style="max-height: 420px; object-fit: cover;">
            </div>
        </div>
    </div>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Statement Tagline Banner -->
    <section class="py-5 text-center animate-on-scroll" style="background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-tertiary) 50%, var(--bg-primary) 100%);">
        <div class="container py-4">
            <i class="bi bi-gem fs-1" style="background: linear-gradient(135deg, var(--gold-light), var(--rose-gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
            <h2 class="font-display display-5 fw-bold mt-3 mb-3" style="background: linear-gradient(135deg, var(--gold-light), var(--gold), var(--rose-gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; max-width: 800px; margin: 0 auto;">
                "We Don't Sell Perfumes. We Bottle Memories, Emotions &amp; Identity."
            </h2>
            <p class="text-secondary mx-auto mt-3" style="max-width: 600px; font-size: 1.05rem; line-height: 1.8;">
                Every fragrance we craft is a journey — from the sun-drenched fields of Grasse to the ancient oud forests of Cambodia. We believe scent is the most powerful form of self-expression.
            </p>
            <div class="section-heading-line mx-auto mt-4"></div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Vision, Mission and Awards Blocks -->
    <section class="py-5 animate-on-scroll" style="background-color: var(--bg-secondary);">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="section-label">Our Philosophy</span>
                <h2 class="text-white font-display mt-2">Vision, Mission &amp; Achievement</h2>
                <div class="section-heading-line mx-auto mt-3"></div>
            </div>

            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(212, 168, 83, 0.08); border: 1px solid rgba(212, 168, 83, 0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="bi bi-eye-fill fs-4" style="color: var(--gold);"></i>
                        </div>
                        <h4 class="font-heading text-white mt-3 mb-2">Our Vision</h4>
                        <p class="small text-secondary">To become the world's premiere artisan perfume house, championing raw natural botanicals over synthesized shortcuts.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(139, 108, 193, 0.08); border: 1px solid rgba(139, 108, 193, 0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="bi bi-shield-fill-check fs-4" style="color: var(--amethyst);"></i>
                        </div>
                        <h4 class="font-heading text-white mt-3 mb-2">Our Mission</h4>
                        <p class="small text-secondary">To curate and bottle high-potency perfumes that provide distinct sensory identities, using ethical and sustainable sourcing.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4 h-100">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(201, 139, 110, 0.08); border: 1px solid rgba(201, 139, 110, 0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <i class="bi bi-trophy-fill fs-4" style="color: var(--rose-gold);"></i>
                        </div>
                        <h4 class="font-heading text-white mt-3 mb-2">Awards Won</h4>
                        <p class="small text-secondary">Winner of the International Niche Fragrance of the Year (2024) for our signature 'Oud Imperial' blend.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Perfumery Team Section -->
    <div class="container py-5 animate-on-scroll">
        <div class="text-center mb-5">
            <span class="section-label">Master Artisans</span>
            <h2 class="text-white font-display mt-2">Meet Our Perfumers</h2>
            <div class="section-heading-line mx-auto mt-3"></div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <img src="assets/images/mens_perfume.png" alt="Dr. Elena Rostova" class="rounded-circle mb-3 border img-3d-circle" style="width: 130px; height: 130px; object-fit: cover; border-color: var(--gold) !important;">
                    <h5 class="text-white font-heading mb-1">Dr. Elena Rostova</h5>
                    <span class="small text-uppercase" style="letter-spacing: 2px; background: linear-gradient(135deg, var(--gold), var(--rose-gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Chief Scent Architect</span>
                    <p class="small text-secondary mt-3">With 15 years in Grasse development hubs, Elena leads our organic notes harvesting and distillation processes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <img src="assets/images/womens_perfume.png" alt="Jean-Louis Martin" class="rounded-circle mb-3 border img-3d-circle" style="width: 130px; height: 130px; object-fit: cover; border-color: var(--amethyst) !important;">
                    <h5 class="text-white font-heading mb-1">Jean-Louis Martin</h5>
                    <span class="small text-uppercase" style="letter-spacing: 2px; background: linear-gradient(135deg, var(--amethyst), var(--gold-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Accord Formulator</span>
                    <p class="small text-secondary mt-3">An expert in woody base notes, Jean-Louis designs the longevity and silage ratios of our designer collections.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <img src="assets/images/unisex_perfume.png" alt="Amara Al-Jamil" class="rounded-circle mb-3 border img-3d-circle" style="width: 130px; height: 130px; object-fit: cover; border-color: var(--rose-gold) !important;">
                    <h5 class="text-white font-heading mb-1">Amara Al-Jamil</h5>
                    <span class="small text-uppercase" style="letter-spacing: 2px; background: linear-gradient(135deg, var(--rose-gold), var(--gold)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Oud Specialist</span>
                    <p class="small text-secondary mt-3">Hailing from Dubai, Amara blends traditional Arabian spices, amber oils, and premium agarwood accords.</p>
                </div>
            </div>
        </div>
    </div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
