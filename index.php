<?php
// ======================= LANGUAGE LOGIC (MUST BE AT THE TOP) =======================
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];

// The page title uses the specific 'index_pageTitle' key.
$pageTitle = $text['index_pageTitle'] ?? 'RAWEE - Smart Farming Solutions';
// ========================================================================================
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link
      rel="stylesheet"
      href="../bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.css"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
      crossorigin="anonymous"
    />

    <link rel="stylesheet" href="css/style.css" />
    <?php if ($lang == 'ar' ): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <?php 
      // The $text array is now available for the header to use for navigation text
      include 'includes/header.php'; 
    ?>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
      <div class="container">
        <h1 class="animate-on-scroll"><?php echo $text['index_heroTitle'] ?? 'The Future of Agriculture is Here'; ?></h1>
        <p class="animate-on-scroll">
          <?php echo $text['index_heroSubtitle'] ?? 'RAWEE delivers intelligent IoT solutions to enhance crop yield, optimize resource usage, and drive sustainable farming forward.'; ?>
        </p>
        <a href="product.php?lang=<?php echo $lang; ?>" class="cta-button animate-on-scroll">
          <?php echo $text['index_heroButton'] ?? 'Explore Our Products'; ?>
        </a>
      </div>
    </section>

    <!-- About Us -->
    <section class="home-section-v4 about-short-v4" id="about-short">
      <div class="container">
        <div class="about-short-grid-v4">
          <div class="about-short-image-v4">
            <div class="image-wrapper-v4">
              <img
                src="images/annie-spratt-QckxruozjRg-unsplash.jpg"
                alt="The Rawee team collaborating"
              />
            </div>
          </div>
          <div class="about-short-content-v4">
            <span class="section-subheading-v4"><?php echo $text['index_aboutSubheading'] ?? 'Who We Are'; ?></span>
            <h2 class="section-title-v4">
              <?php echo $text['index_aboutTitle'] ?? 'Merging Innovation with Agriculture'; ?>
            </h2>
            <p>
              <?php echo $text['index_aboutParagraph'] ?? 'At Rawee Team, we are passionate innovators...'; ?>
            </p>
            <a href="about.php?lang=<?php echo $lang; ?>" class="btn-link-v4">
              <span><?php echo $text['index_aboutLink'] ?? 'Learn More About Us'; ?></span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Technology  -->
    <section class="home-section-v4 tech-showcase-v4" id="technology">
      <div class="container">
        <div class="tech-showcase-grid-v4">
          <div class="tech-showcase-content-v4">
            <span class="section-subheading-v4"><?php echo $text['index_techSubheading'] ?? 'Our Technology'; ?></span>
            <h2 class="section-title-v4"><?php echo $text['index_techTitle'] ?? 'Your Farm, in Your Hand'; ?></h2>
            <p>
              <?php echo $text['index_techParagraph'] ?? 'Our intuitive mobile app is the command center...'; ?>
            </p>
            <ul class="tech-features-list-v4">
              <li>
                <i class="fas fa-chart-line"></i> <?php echo $text['index_techFeature1'] ?? 'Real-time data dashboards'; ?>
              </li>
              <li><i class="fas fa-bell"></i> <?php echo $text['index_techFeature2'] ?? 'Instant custom alerts'; ?></li>
              <li><i class="fas fa-sliders-h"></i> <?php echo $text['index_techFeature3'] ?? 'Remote system control'; ?></li>
            </ul>
          </div>
          <div class="tech-showcase-image-v4">
            <img
              class="phone-mockup"
              src="images/business-data-monitoring-with-mobile-app-vector-48632371.jpg"
              alt="RAWEE mobile app on a smartphone"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Our Solutions  -->
    <section class="home-section-v4 solutions-v4" id="solutions">
      <div class="container text-center">
        <span class="section-subheading-v4"><?php echo $text['index_solutionsSubheading'] ?? 'Our Solutions'; ?></span>
        <h2 class="section-title-v4"><?php echo $text['index_solutionsTitle'] ?? 'Intelligent Systems for Every Need'; ?></h2>
        <div class="solutions-grid-v4">
          <div class="solution-card-v4">
            <div class="solution-card-front">
              <div class="solution-icon-v4"><i class="fas fa-robot"></i></div>
              <h5><?php echo $text['index_solution1Title'] ?? 'Smart Irrigation Automation'; ?></h5>
            </div>
            <div class="solution-card-back">
              <p>
                <?php echo $text['index_solution1Desc'] ?? 'Automated watering based on real-time soil...'; ?>
              </p>
            </div>
          </div>
          <div class="solution-card-v4">
            <div class="solution-card-front">
              <div class="solution-icon-v4">
                <i class="fas fa-chart-bar"></i>
              </div>
              <h5><?php echo $text['index_solution2Title'] ?? 'Monitoring & AI Analytics'; ?></h5>
            </div>
            <div class="solution-card-back">
              <p>
                <?php echo $text['index_solution2Desc'] ?? 'Gain deep insights into soil health...'; ?>
              </p>
            </div>
          </div>
          <div class="solution-card-v4">
            <div class="solution-card-front">
              <div class="solution-icon-v4"><i class="fas fa-leaf"></i></div>
              <h5><?php echo $text['index_solution3Title'] ?? 'Resource Optimization'; ?></h5>
            </div>
            <div class="solution-card-back">
              <p>
                <?php echo $text['index_solution3Desc'] ?? 'Save water, reduce waste, and cut costs...'; ?>
              </p>
            </div>
          </div>
          <div class="solution-card-v4">
            <div class="solution-card-front">
              <div class="solution-icon-v4">
                <i class="fas fa-drafting-compass"></i>
              </div>
              <h5><?php echo $text['index_solution4Title'] ?? 'Custom-Tailored Solutions'; ?></h5>
            </div>
            <div class="solution-card-back">
              <p>
                <?php echo $text['index_solution4Desc'] ?? 'We adapt to your farm’s unique needs...'; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!--  Why Choose Us  -->
    <section class="home-section-v4 why-us-v4" id="why-us">
      <div class="container">
        <div class="why-us-grid-v4">
          <div class="why-us-content-v4">
            <span class="section-subheading-v4"><?php echo $text['index_whyUsSubheading'] ?? 'Our Promise'; ?></span>
            <h2 class="section-title-v4"><?php echo $text['index_whyUsTitle'] ?? 'Why Partner with RAWEE?'; ?></h2>
            <div class="accordion-v4">
              <div class="accordion-item-v4">
                <div class="accordion-header-v4">
                  <div class="accordion-icon-v4">
                    <i class="fas fa-map-marked-alt"></i>
                  </div>
                  <h5><?php echo $text['index_whyUsAccordion1Title'] ?? 'Local Understanding'; ?></h5>
                  <div class="accordion-toggle-v4">
                    <i class="fas fa-chevron-down"></i>
                  </div>
                </div>
                <div class="accordion-content-v4">
                  <p>
                    <?php echo $text['index_whyUsAccordion1Desc'] ?? 'We know your land, climate, and environment...'; ?>
                  </p>
                </div>
              </div>
              <div class="accordion-item-v4 active">
                <div class="accordion-header-v4">
                  <div class="accordion-icon-v4">
                    <i class="fas fa-brain"></i>
                  </div>
                  <h5><?php echo $text['index_whyUsAccordion2Title'] ?? 'Real Data, Real Decisions'; ?></h5>
                  <div class="accordion-toggle-v4">
                    <i class="fas fa-chevron-down"></i>
                  </div>
                </div>
                <div class="accordion-content-v4">
                  <p>
                    <?php echo $text['index_whyUsAccordion2Desc'] ?? 'Our systems adapt in real-time...'; ?>
                  </p>
                </div>
              </div>
              <div class="accordion-item-v4">
                <div class="accordion-header-v4">
                  <div class="accordion-icon-v4">
                    <i class="fas fa-users-cog"></i>
                  </div>
                  <h5><?php echo $text['index_whyUsAccordion3Title'] ?? 'Full-Cycle Support'; ?></h5>
                  <div class="accordion-toggle-v4">
                    <i class="fas fa-chevron-down"></i>
                  </div>
                </div>
                <div class="accordion-content-v4">
                  <p>
                    <?php echo $text['index_whyUsAccordion3Desc'] ?? 'We are with you every step of the way...'; ?>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="why-us-image-v4">
            <img
              src="https://images.unsplash.com/photo-1492496913980-501348b61469?q=80&w=1887&auto=format&fit=crop"
              alt="Hands holding healthy soil with a young plant"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact" class="content-section">
      <div class="container animate-on-scroll">
        <h2><?php echo $text['index_contactTitle'] ?? "Start Your Farm's Transformation"; ?></h2>
        <p>
          <?php echo $text['index_contactSubtitle'] ?? 'Ready to see how RAWEE can work for you?...'; ?>
        </p>
        <form class="contact-form">
          <input type="text" name="name" placeholder="<?php echo $text['index_contactNamePlaceholder'] ?? 'Your Name'; ?>" required />
          <input type="email" name="email" placeholder="<?php echo $text['index_contactEmailPlaceholder'] ?? 'Your Email'; ?>" required />
          <textarea
            name="message"
            placeholder="<?php echo $text['index_contactMessagePlaceholder'] ?? 'Tell us about your farm and your goals...'; ?>"
            rows="5"
            required
          ></textarea>
          <button type="submit" class="cta-button">
            <?php echo $text['index_contactButton'] ?? 'Request a Consultation'; ?>
          </button>
        </form>
      </div>
    </section>

    <?php include 'includes/footer.php'; ?>

  </body>
</html>
