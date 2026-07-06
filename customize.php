<?php
require_once 'includes/header.php';
?>

<div class="container py-5 body-fade">
    <div class="row mb-5 animate-on-scroll">
        <div class="col-lg-8 mx-auto text-center">
            <span class="text-gold text-uppercase tracking-wide small fw-bold">AromaLuxe Bespoke Studio</span>
            <h1 class="font-heading text-luxury-glow text-white mt-2 mb-3">Personalize Your Signature Scent</h1>
            <p class="text-secondary">Indulge in olfactory customization. Blend hand-picked absolute oils, personalize your luxury flacon, and engrave your name onto the golden label.</p>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left Column: Bottle Mockup Preview -->
        <div class="col-lg-5 animate-on-scroll delay-1">
            <div class="glass-card p-4 text-center sticky-top" style="top: 100px; border: 1px solid rgba(229,192,96,0.2);">
                <h5 class="font-heading text-white mb-4">Your Custom Flacon</h5>
                
                <!-- Bottle Simulation Container -->
                <div class="bottle-mockup-wrapper py-5 position-relative d-flex justify-content-center align-items-center bg-black-50 rounded mb-4" style="height: 350px; overflow: hidden; background: radial-gradient(circle, rgba(19,22,39,0.5) 0%, rgba(5,6,11,0.9) 100%);">
                    
                    <!-- Scent background glow (changes color based on note profile) -->
                    <div id="scentGlow" class="position-absolute" style="width: 150px; height: 150px; border-radius: 50%; filter: blur(50px); opacity: 0.15; background: #e5c060;"></div>
                    
                    <!-- 3D Bottle Drawing -->
                    <div id="perfumeBottle" class="perfume-bottle obsidian-night">
                        <!-- Cap -->
                        <div class="bottle-cap"></div>
                        <!-- Neck -->
                        <div class="bottle-neck"></div>
                        <!-- Glass Body -->
                        <div class="bottle-body d-flex align-items-center justify-content-center">
                            <!-- Fluid inside -->
                            <div class="bottle-fluid"></div>
                            <!-- Engraved Gold Label -->
                            <div class="bottle-label">
                                <div class="label-brand font-heading">AROMALUXE</div>
                                <div id="engravedLabelText" class="label-engraving">MY SIGNATURE</div>
                                <div class="label-volume small" id="labelVolumeText">50ml</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-around text-secondary small">
                    <div><span class="text-white fw-bold d-block" id="previewTopNote">Bergamot</span> Top Note</div>
                    <div class="border-start border-secondary" style="height: 25px;"></div>
                    <div><span class="text-white fw-bold d-block" id="previewMidNote">Rose</span> Heart Note</div>
                    <div class="border-start border-secondary" style="height: 25px;"></div>
                    <div><span class="text-white fw-bold d-block" id="previewBaseNote">Oud</span> Base Note</div>
                </div>
            </div>
        </div>

        <!-- Right Column: Controls and Options -->
        <div class="col-lg-7 animate-on-scroll delay-2">
            <form id="customScentForm" class="glass-card p-4 p-md-5" style="border: 1px solid rgba(255,255,255,0.05);">
                
                <!-- STEP 1: Scent Profiles -->
                <div class="mb-5">
                    <h4 class="font-heading text-warning d-flex align-items-center gap-2 mb-3" style="font-size: 1.25rem;">
                        <span class="badge rounded-circle bg-warning text-dark px-2 py-1">1</span> Choose Scent Base Profile
                    </h4>
                    <p class="small text-secondary mb-4">Select your foundational fragrance profile. This dictates the core note structure and the visual oil glow.</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="scent-profile-card cursor-pointer d-block p-3 rounded border text-start position-relative active" data-color="#b38f2d" data-base="Woody Oud" data-mid="Sandalwood" data-top="Saffron">
                                <input type="radio" name="profile_base" value="woody" checked class="position-absolute top-3 end-3 opacity-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-warning-subtle text-warning"><i class="bi bi-tree-fill"></i></div>
                                    <div>
                                        <h6 class="text-white font-heading m-0" style="font-size: 0.9rem;">Woody & Exotic</h6>
                                        <span class="small text-secondary" style="font-size: 0.75rem;">Oud, Saffron, Sandalwood</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="scent-profile-card cursor-pointer d-block p-3 rounded border text-start position-relative" data-color="#ff7f50" data-base="Warm Amber" data-mid="Madagascan Vanilla" data-top="Spiced Cardamom">
                                <input type="radio" name="profile_base" value="oriental" class="position-absolute top-3 end-3 opacity-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-warning-subtle text-warning"><i class="bi bi-fire"></i></div>
                                    <div>
                                        <h6 class="text-white font-heading m-0" style="font-size: 0.9rem;">Warm Oriental</h6>
                                        <span class="small text-secondary" style="font-size: 0.75rem;">Amber, Vanilla, Cardamom</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="scent-profile-card cursor-pointer d-block p-3 rounded border text-start position-relative" data-color="#ff69b4" data-base="White Patchouli" data-mid="Rose Centifolia" data-top="Jasmine Sambac">
                                <input type="radio" name="profile_base" value="floral" class="position-absolute top-3 end-3 opacity-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-warning-subtle text-warning"><i class="bi bi-flower1"></i></div>
                                    <div>
                                        <h6 class="text-white font-heading m-0" style="font-size: 0.9rem;">Floral Absolute</h6>
                                        <span class="small text-secondary" style="font-size: 0.75rem;">Rose, Jasmine, Patchouli</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="scent-profile-card cursor-pointer d-block p-3 rounded border text-start position-relative" data-color="#00fa9a" data-base="White Cedar" data-mid="Sea Salt Mineral" data-top="Calabrian Bergamot">
                                <input type="radio" name="profile_base" value="fresh" class="position-absolute top-3 end-3 opacity-0">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-warning-subtle text-warning"><i class="bi bi-wind"></i></div>
                                    <div>
                                        <h6 class="text-white font-heading m-0" style="font-size: 0.9rem;">Fresh & Citrus</h6>
                                        <span class="small text-secondary" style="font-size: 0.75rem;">Bergamot, Sea Salt, Cedar</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Note Strength Ratios -->
                <div class="mb-5">
                    <h4 class="font-heading text-warning d-flex align-items-center gap-2 mb-3" style="font-size: 1.25rem;">
                        <span class="badge rounded-circle bg-warning text-dark px-2 py-1">2</span> Adjust Note Ratios
                    </h4>
                    <p class="small text-secondary mb-4">Tweak the balance between note heights. The total ratio will auto-scale to exactly 100%.</p>
                    
                    <div class="ratio-sliders-block">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-white">Top Note (Initial Impression)</span>
                                <span class="small text-warning" id="topVal">30%</span>
                            </div>
                            <input type="range" class="form-range ratio-slider" id="topRatioSlider" min="10" max="60" value="30">
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-white">Heart Note (Olfactory Core)</span>
                                <span class="small text-warning" id="midVal">40%</span>
                            </div>
                            <input type="range" class="form-range ratio-slider" id="midRatioSlider" min="20" max="60" value="40">
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-white">Base Note (Longevity & Sillage)</span>
                                <span class="small text-warning" id="baseVal">30%</span>
                            </div>
                            <input type="range" class="form-range ratio-slider" id="baseRatioSlider" min="10" max="60" value="30">
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Personalization & Engraving -->
                <div class="mb-5">
                    <h4 class="font-heading text-warning d-flex align-items-center gap-2 mb-3" style="font-size: 1.25rem;">
                        <span class="badge rounded-circle bg-warning text-dark px-2 py-1">3</span> Design Your Flacon
                    </h4>
                    
                    <!-- Flacon Style -->
                    <div class="mb-4">
                        <label class="form-label text-white small">Bottle Flacon Aesthetics</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-style-select active" data-style="obsidian-night">Obsidian</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-style-select" data-style="gold-dust">Gold Dust</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-outline-secondary w-100 py-2 btn-style-select" data-style="crystal-clear">Crystal</button>
                            </div>
                        </div>
                    </div>

                    <!-- Label Engraving -->
                    <div class="mb-4">
                        <label for="labelInput" class="form-label text-white small">Gold Label Engraving (Max 20 chars)</label>
                        <input type="text" class="form-control bg-transparent border-secondary text-white font-heading" id="labelInput" maxlength="20" value="MY SIGNATURE" placeholder="ENGRAVED TEXT" style="letter-spacing: 1px;">
                    </div>

                    <!-- Size Selector -->
                    <div class="mb-4">
                        <label class="form-label text-white small">Select Flacon Volume</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="btn btn-outline-secondary w-100 py-2 btn-size-select text-center cursor-pointer">
                                    <input type="radio" name="bottle_size" value="30ml" class="d-none">
                                    <div class="fw-bold text-white">30 ml</div>
                                    <div class="small text-secondary" style="font-size: 0.7rem;">₹4,500</div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="btn btn-outline-secondary w-100 py-2 btn-size-select text-center cursor-pointer active">
                                    <input type="radio" name="bottle_size" value="50ml" checked class="d-none">
                                    <div class="fw-bold text-white">50 ml</div>
                                    <div class="small text-secondary" style="font-size: 0.7rem;">₹7,500</div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="btn btn-outline-secondary w-100 py-2 btn-size-select text-center cursor-pointer">
                                    <input type="radio" name="bottle_size" value="100ml" class="d-none">
                                    <div class="fw-bold text-white">100 ml</div>
                                    <div class="small text-secondary" style="font-size: 0.7rem;">₹12,000</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT AND PRICING -->
                <div class="p-4 rounded border border-secondary bg-black-20 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-center text-md-start">
                        <span class="text-secondary small d-block">Total Customized Value</span>
                        <span class="text-warning font-heading h3 m-0" id="livePriceText">₹7,500.00</span>
                    </div>
                    <button type="submit" class="btn btn-gold btn-lg py-3 px-5 border-0 rounded-0 font-heading text-uppercase text-dark tracking-wide" id="addToBagBtn" style="font-size: 0.9rem;">
                        Add Scent to Cart <i class="bi bi-bag-plus-fill ms-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
/* Perfume Bottle Styling */
.perfume-bottle {
    width: 140px;
    height: 240px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    filter: drop-shadow(0 15px 25px rgba(0,0,0,0.6));
    transition: all 0.5s ease;
}
.bottle-cap {
    width: 60px;
    height: 40px;
    background: linear-gradient(135deg, #b38f2d 0%, #ffd700 50%, #e5c060 100%);
    border-radius: 4px;
    border-bottom: 2px solid #5a4512;
    z-index: 2;
}
.bottle-neck {
    width: 30px;
    height: 15px;
    background: #ffd700;
    border-left: 2px solid rgba(0,0,0,0.2);
    border-right: 2px solid rgba(0,0,0,0.2);
    z-index: 1;
}
.bottle-body {
    width: 140px;
    height: 185px;
    border-radius: 12px;
    border: 3px solid rgba(229, 192, 96, 0.4);
    position: relative;
    overflow: hidden;
    transition: all 0.5s ease;
}

/* Bottle Themes */
.perfume-bottle.obsidian-night .bottle-body {
    background: rgba(12, 14, 26, 0.9);
    border-color: rgba(229, 192, 96, 0.4);
    box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.9);
}
.perfume-bottle.gold-dust .bottle-body {
    background: linear-gradient(180deg, rgba(229, 192, 96, 0.3) 0%, rgba(8, 8, 10, 0.8) 100%);
    border-color: rgba(229, 192, 96, 0.7);
    box-shadow: inset 0 0 25px rgba(229, 192, 96, 0.25);
}
.perfume-bottle.crystal-clear .bottle-body {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
    box-shadow: inset 0 0 15px rgba(255, 255, 255, 0.15);
}

/* Liquid inside */
.bottle-fluid {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 70%;
    background: linear-gradient(180deg, #d4af37 0%, #856404 100%);
    opacity: 0.35;
    transition: all 0.5s ease;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

/* Label */
.bottle-label {
    width: 100px;
    padding: 15px 5px;
    background: linear-gradient(135deg, #1d1e22 0%, #121316 100%);
    border: 1px solid rgba(229, 192, 96, 0.6);
    border-radius: 4px;
    color: #fff;
    text-align: center;
    z-index: 3;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    transition: all 0.3s ease;
}
.label-brand {
    font-size: 0.6rem;
    letter-spacing: 2px;
    color: #ffd700;
    margin-bottom: 5px;
}
.label-engraving {
    font-size: 0.65rem;
    color: #fff;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
    word-break: break-all;
    max-height: 40px;
    overflow: hidden;
}
.label-volume {
    font-size: 0.5rem;
    color: #888;
    margin-top: 5px;
    letter-spacing: 1px;
}

/* Controls style */
.scent-profile-card {
    background: rgba(12, 14, 26, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
}
.scent-profile-card:hover {
    background: rgba(229, 192, 96, 0.05);
    border-color: rgba(229, 192, 96, 0.25);
}
.scent-profile-card.active {
    background: rgba(229, 192, 96, 0.1);
    border-color: #e5c060;
}
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.btn-outline-secondary:hover {
    background: rgba(229, 192, 96, 0.2);
    border-color: #e5c060;
    color: #fff;
}
.btn-style-select.active {
    background: #e5c060 !important;
    border-color: #e5c060 !important;
    color: #000 !important;
    font-weight: bold;
}
.btn-size-select input[type="radio"]:checked + div {
    color: #e5c060 !important;
}
.btn-size-select.active {
    border-color: #e5c060 !important;
    background: rgba(229, 192, 96, 0.08);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const labelInput = document.getElementById('labelInput');
    const engravedText = document.getElementById('engravedLabelText');
    const labelVolume = document.getElementById('labelVolumeText');
    const livePrice = document.getElementById('livePriceText');
    const perfumeBottle = document.getElementById('perfumeBottle');
    const scentGlow = document.getElementById('scentGlow');
    const previewTop = document.getElementById('previewTopNote');
    const previewMid = document.getElementById('previewMidNote');
    const previewBase = document.getElementById('previewBaseNote');
    
    // Step inputs
    const profileCards = document.querySelectorAll('.scent-profile-card');
    const styleButtons = document.querySelectorAll('.btn-style-select');
    const sizeLabels = document.querySelectorAll('.btn-size-select');
    const ratioSliders = document.querySelectorAll('.ratio-slider');
    
    // Scent characteristics cache
    let selectedBase = 'Woody Oud';
    let selectedMid = 'Sandalwood';
    let selectedTop = 'Saffron';
    let activeSize = '50ml';
    let activeStyle = 'obsidian-night';
    
    // Label engraving listener
    labelInput.addEventListener('input', (e) => {
        const text = e.target.value.trim();
        engravedText.textContent = text ? text : 'MY SIGNATURE';
    });

    // Profile Card Selection
    profileCards.forEach(card => {
        card.addEventListener('click', () => {
            profileCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            
            const color = card.getAttribute('data-color');
            selectedBase = card.getAttribute('data-base');
            selectedMid = card.getAttribute('data-mid');
            selectedTop = card.getAttribute('data-top');
            
            // Update preview labels
            previewTop.textContent = selectedTop;
            previewMid.textContent = selectedMid;
            previewBase.textContent = selectedBase;
            
            // Adjust glow base color
            scentGlow.style.background = color;
            
            // Slight visual adjust to fluid color tint
            const fluid = document.querySelector('.bottle-fluid');
            fluid.style.filter = `hue-rotate(${color === '#ff69b4' ? '120deg' : (color === '#00fa9a' ? '220deg' : '0deg')})`;
        });
    });

    // Style Select Selection
    styleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            styleButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const style = btn.getAttribute('data-style');
            activeStyle = style;
            
            // Swap classes on bottle body
            perfumeBottle.className = `perfume-bottle ${style}`;
        });
    });

    // Size Select Selection
    sizeLabels.forEach(label => {
        label.addEventListener('click', () => {
            sizeLabels.forEach(l => l.classList.remove('active'));
            label.classList.add('active');
            
            const radio = label.querySelector('input[type="radio"]');
            radio.checked = true;
            
            const size = radio.value;
            activeSize = size;
            labelVolume.textContent = size;
            
            // Update pricing live
            let priceText = '₹7,500.00';
            if (size === '30ml') priceText = '₹4,500.00';
            if (size === '100ml') priceText = '₹12,000.00';
            livePrice.textContent = priceText;
        });
    });

    // Scent Note Sliders adjustment logic
    const topSlider = document.getElementById('topRatioSlider');
    const midSlider = document.getElementById('midRatioSlider');
    const baseSlider = document.getElementById('baseRatioSlider');

    const topVal = document.getElementById('topVal');
    const midVal = document.getElementById('midVal');
    const baseVal = document.getElementById('baseVal');

    function updateSliders(modifiedSlider) {
        let t = parseInt(topSlider.value);
        let m = parseInt(midSlider.value);
        let b = parseInt(baseSlider.value);
        
        let sum = t + m + b;
        let diff = 100 - sum;

        if (diff !== 0) {
            // Distribute difference proportionally between the other two sliders
            if (modifiedSlider === 'top') {
                midSlider.value = m + Math.round(diff * 0.6);
                baseSlider.value = b + Math.round(diff * 0.4);
            } else if (modifiedSlider === 'mid') {
                topSlider.value = t + Math.round(diff * 0.5);
                baseSlider.value = b + Math.round(diff * 0.5);
            } else {
                topSlider.value = t + Math.round(diff * 0.4);
                midSlider.value = m + Math.round(diff * 0.6);
            }
        }
        
        // Refresh display label
        topVal.textContent = topSlider.value + '%';
        midVal.textContent = midSlider.value + '%';
        baseVal.textContent = baseSlider.value + '%';
    }

    topSlider.addEventListener('input', () => updateSliders('top'));
    midSlider.addEventListener('input', () => updateSliders('mid'));
    baseSlider.addEventListener('input', () => updateSliders('base'));

    // Handle customized perfume submission
    const form = document.getElementById('customScentForm');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const addToBagBtn = document.getElementById('addToBagBtn');
        addToBagBtn.disabled = true;
        addToBagBtn.innerHTML = 'Blending Perfume... <span class="spinner-border spinner-border-sm ms-2"></span>';

        const formData = new URLSearchParams();
        formData.append('base_note', selectedBase);
        formData.append('middle_note', selectedMid);
        formData.append('top_note', selectedTop);
        formData.append('top_ratio', topSlider.value);
        formData.append('mid_ratio', midSlider.value);
        formData.append('base_ratio', baseSlider.value);
        formData.append('bottle_size', activeSize);
        formData.append('bottle_style', activeStyle);
        formData.append('label_text', labelInput.value);

        fetch('api/customize-perfume.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Update cart count badge
                const cartBadge = document.querySelector('.badge.bg-warning.text-dark');
                if (cartBadge) {
                    cartBadge.textContent = data.total_items;
                }
                
                showToast("Scent Blended!", data.message, "success");
                setTimeout(() => {
                    window.location.href = 'cart.php';
                }, 1200);
            } else {
                showToast("Customizer Error", data.message, "danger");
                addToBagBtn.disabled = false;
                addToBagBtn.innerHTML = 'Add Scent to Cart <i class="bi bi-bag-plus-fill ms-2"></i>';
            }
        })
        .catch(() => {
            showToast("Connection Error", "Olfactory laboratory connection lost. Try again.", "danger");
            addToBagBtn.disabled = false;
            addToBagBtn.innerHTML = 'Add Scent to Cart <i class="bi bi-bag-plus-fill ms-2"></i>';
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>
