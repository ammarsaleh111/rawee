<?php
// ======================= LANGUAGE LOGIC (MUST BE AT THE TOP) =======================
// 1. Determine the language from the URL (?lang=ar), default to 'en'
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';

// 2. Construct the JSON file name (place your en.json and ar.json in a folder named 'translations')
$lang_file = "translations/{$lang}.json";

// 3. Load the language file's content
if (file_exists($lang_file)) {
    $text = json_decode(file_get_contents($lang_file), true);
} else {
    // Create empty text array if file is missing to prevent errors
    $text = []; 
    // You could also add an error message here:
    // die("Language file not found: " . $lang_file);
}

// 4. Set the page title for the header. The '??' operator provides a fallback value.
$pageTitle = $text['pageTitle'] ?? 'About Us - RAWEE Smart Farming';
// ========================================================================================
?>
<!DOCTYPE html>
<!-- The lang and dir attributes are set dynamically based on the chosen language -->
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/style.css" />

    <!-- This stylesheet is loaded ONLY for Arabic to fix right-to-left layout issues -->
    <?php if ($lang == 'ar'): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <!-- This font is loaded ONLY for Arabic for better readability -->
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  </head>
  <body>
    <?php
      // The header is included here. It will use the $lang and $pageTitle variables we defined above.
      include 'includes/header.php';
    ?>

    <main class="about-page-main">
      <!-- Hero Section -->
      <section class="about-hero-v3">
        <div class="hero-overlay-v3"></div>
        <div class="hero-content-v3">
          <h1><?php echo $text['heroTitle'] ?? 'Fusing Technology with Tradition'; ?></h1>
          <p><?php echo $text['heroSubtitle'] ?? 'At Rawee Team, we are passionate innovators...'; ?></p>
        </div>
      </section>

      <!-- Our Vision Section -->
      <section class="vision-section-v3">
        <div class="container">
          <h2 class="section-title-v3"><?php echo $text['visionTitle'] ?? 'Our Vision'; ?></h2>
          <p class="vision-statement-v3"><?php echo $text['visionStatement'] ?? '"We envision a world..."'; ?></p>
        </div>
      </section>

      <!-- What We Do Section -->
      <section class="what-we-do-section-v3">
        <div class="container">
          <h2 class="section-title-v3"><?php echo $text['whatWeDoTitle'] ?? 'What We Do'; ?></h2>
          <div class="service-grid-v3">
            <div class="service-card-v3">
              <div class="service-icon-v3"><i class="fas fa-robot"></i></div>
              <h4><?php echo $text['service1Title'] ?? 'Smart Irrigation Automation'; ?></h4>
              <p><?php echo $text['service1Desc'] ?? 'We build systems...'; ?></p>
            </div>
            <div class="service-card-v3">
              <div class="service-icon-v3"><i class="fas fa-chart-bar"></i></div>
              <h4><?php echo $text['service2Title'] ?? 'Monitoring & AI Analytics'; ?></h4>
              <p><?php echo $text['service2Desc'] ?? 'Our dashboards provide...'; ?></p>
            </div>
            <div class="service-card-v3">
              <div class="service-icon-v3"><i class="fas fa-leaf"></i></div>
              <h4><?php echo $text['service3Title'] ?? 'Resource Optimization'; ?></h4>
              <p><?php echo $text['service3Desc'] ?? 'Save water, reduce waste...'; ?></p>
            </div>
            <div class="service-card-v3">
              <div class="service-icon-v3"><i class="fas fa-drafting-compass"></i></div>
              <h4><?php echo $text['service4Title'] ?? 'Custom-Tailored Solutions'; ?></h4>
              <p><?php echo $text['service4Desc'] ?? 'We adapt to your farm’s...'; ?></p>
            </div>
          </div>
        </div>
      </section>

      <!-- Our Process Section -->
      <section class="our-process-section-v3">
        <div class="container">
          <h2 class="section-title-v3"><?php echo $text['processTitle'] ?? 'Our Process'; ?></h2>
          <p class="section-subtitle-v3"><?php echo $text['processSubtitle'] ?? 'We follow a tried-and-true process...'; ?></p>
          <div class="process-steps-v3">
            <div class="process-step-v3">
              <div class="step-number-v3">1</div>
              <div class="step-content-v3">
                <h5><?php echo $text['step1Title'] ?? 'Discovery'; ?></h5>
                <p><?php echo $text['step1Desc'] ?? 'We talk with you...'; ?></p>
              </div>
            </div>
            <div class="process-step-v3">
              <div class="step-number-v3">2</div>
              <div class="step-content-v3">
                <h5><?php echo $text['step2Title'] ?? 'Design & Prototyping'; ?></h5>
                <p><?php echo $text['step2Desc'] ?? 'We sketch and build...'; ?></p>
              </div>
            </div>
            <div class="process-step-v3">
              <div class="step-number-v3">3</div>
              <div class="step-content-v3">
                <h5><?php echo $text['step3Title'] ?? 'Implementation & Testing'; ?></h5>
                <p><?php echo $text['step3Desc'] ?? 'We install sensors...'; ?></p>
              </div>
            </div>
            <div class="process-step-v3">
              <div class="step-number-v3">4</div>
              <div class="step-content-v3">
                <h5><?php echo $text['step4Title'] ?? 'Support & Iteration'; ?></h5>
                <p><?php echo $text['step4Desc'] ?? 'Once deployed, we monitor...'; ?></p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Why Choose Us Section -->
      <section class="why-choose-us-section-v3">
        <div class="container">
          <div class="why-us-grid-v3">
            <div class="why-us-content-v3">
              <h2 class="section-title-v3" style="text-align: left"><?php echo $text['whyChooseUsTitle'] ?? 'Why Choose RAWEE?'; ?></h2>
              <div class="benefit-item-v3">
                <div class="benefit-icon-v3"><i class="fas fa-map-marked-alt"></i></div>
                <div class="benefit-text-v3">
                  <h5><?php echo $text['benefit1Title'] ?? 'Local Understanding'; ?></h5>
                  <p><?php echo $text['benefit1Desc'] ?? 'We know your land...'; ?></p>
                </div>
              </div>
              <div class="benefit-item-v3">
                <div class="benefit-icon-v3"><i class="fas fa-brain"></i></div>
                <div class="benefit-text-v3">
                  <h5><?php echo $text['benefit2Title'] ?? 'Real Data, Real Decisions'; ?></h5>
                  <p><?php echo $text['benefit2Desc'] ?? 'Our systems adapt...'; ?></p>
                </div>
              </div>
              <div class="benefit-item-v3">
                <div class="benefit-icon-v3"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="benefit-text-v3">
                  <h5><?php echo $text['benefit3Title'] ?? 'Savings & Sustainability'; ?></h5>
                  <p><?php echo $text['benefit3Desc'] ?? 'Achieve higher yields...'; ?></p>
                </div>
              </div>
              <div class="benefit-item-v3">
                <div class="benefit-icon-v3"><i class="fas fa-users-cog"></i></div>
                <div class="benefit-text-v3">
                  <h5><?php echo $text['benefit4Title'] ?? 'Full-Cycle Support'; ?></h5>
                  <p><?php echo $text['benefit4Desc'] ?? 'We are with you...'; ?></p>
                </div>
              </div>
            </div>
            <div class="why-us-image-v3">
              <img src="https://images.unsplash.com/photo-1492496913980-501348b61469?q=80&w=1887&auto=format&fit=crop" alt="Hands holding healthy soil with a young plant" />
            </div>
          </div>
        </div>
      </section>
      
      <!-- Our Team Section -->
      <section class="team-section">
        <div class="container">
          <h2 class="section-title-v3" style="text-align: left; margin-bottom: 50px; margin-top: 0"><?php echo $text['teamTitle'] ?? 'Meet Our Team'; ?></h2>
          <div class="team-grid">
            <div class="team-card">
              <div class="team-card-image"><img src="https://images.unsplash.com/photo-1557862921-37829c790f19?q=80&w=2071&auto=format&fit=crop" alt="Team Member 1" /></div>
              <div class="team-card-content">
                <h5><?php echo $text['teamMember1Name'] ?? 'Alex Johnson'; ?></h5>
                <p><?php echo $text['teamMember1Role'] ?? 'Co-Founder & CEO'; ?></p>
              </div>
            </div>
            <div class="team-card">
              <div class="team-card-image"><img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=1888&auto=format&fit=crop" alt="Team Member 2" /></div>
              <div class="team-card-content">
                <h5><?php echo $text['teamMember2Name'] ?? 'Maria Garcia'; ?></h5>
                <p><?php echo $text['teamMember2Role'] ?? 'Head of Agronomy'; ?></p>
              </div>
            </div>
            <div class="team-card">
              <div class="team-card-image"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=1887&auto=format&fit=crop" alt="Team Member 3" /></div>
              <div class="team-card-content">
                <h5><?php echo $text['teamMember3Name'] ?? 'Ben Carter'; ?></h5>
                <p><?php echo $text['teamMember3Role'] ?? 'Lead IoT Engineer'; ?></p>
              </div>
            </div>
            <div class="team-card">
              <div class="team-card-image"><img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=2070&auto=format&fit=crop" alt="Team Member 4" /></div>
              <div class="team-card-content">
                <h5><?php echo $text['teamMember4Name'] ?? 'Chloe Wang'; ?></h5>
                <p><?php echo $text['teamMember4Role'] ?? 'Product Manager'; ?></p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Your Login/Sign-up Modal would also need to be updated with dynamic text -->
    <!-- I have left it out for brevity, but the same principle applies -->

    <script src="js/edits.js"></script>
  </body>
</html>
