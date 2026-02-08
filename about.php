<?php
session_start();
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
$emailInitial = $user ? strtoupper(substr($user['email'] ?? 'U', 0, 1)) : '';
$dashboardUrl = 'user_dashboard.php';
if ($role === 'admin') {
    $dashboardUrl = 'admin_dashboard.php';
} elseif ($role === 'collector') {
    $dashboardUrl = 'collector_dashboard.php';
}
$profileUrl = 'profile.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us | MediEco</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <!-- Main CSS -->
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="theme.css">
  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Inter Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    .back-home{
      position:fixed;
      top:88px;
      left:24px;
      z-index:60;
      display:flex;
      align-items:center;
      gap:8px;
      font-weight:700;
      color:#64748b;
      text-decoration:none;
      transition:color 0.2s ease;
    }
    .back-home:hover{color:#0f172a;}
    .back-home i{transition:transform 0.2s ease;}
    .back-home:hover i{transform:translateX(-4px);}

    .about-hero{
      padding:120px 10% 80px;
      background:
        radial-gradient(circle at 15% 20%, rgba(26,163,155,0.15), transparent 45%),
        radial-gradient(circle at 85% 10%, rgba(30,109,224,0.18), transparent 45%),
        linear-gradient(180deg, #f7fbff, #eef5ff);
    }
    .about-hero-grid{
      display:grid;
      grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
      gap:40px;
      align-items:center;
    }
    .about-kicker{
      display:inline-flex;
      align-items:center;
      gap:8px;
      font-weight:700;
      color:#1e6de0;
      background:#e8f1ff;
      padding:6px 12px;
      border-radius:999px;
      font-size:13px;
      margin-bottom:16px;
    }
    .about-title{
      font-size:48px;
      line-height:1.1;
      margin:0 0 14px 0;
      color:#0f172a;
    }
    .about-lead{
      font-size:18px;
      color:#475569;
      line-height:1.7;
      max-width:560px;
    }
    .hero-card{
      background:#ffffff;
      border:1px solid #e2e8f0;
      border-radius:18px;
      padding:24px;
      box-shadow:0 16px 30px rgba(15,23,42,0.08);
    }
    .hero-card h3{
      font-size:18px;
      margin:0 0 12px 0;
      color:#0f172a;
    }
    .hero-list{
      list-style:none;
      padding:0;
      margin:0;
      display:grid;
      gap:12px;
    }
    .hero-list li{
      display:flex;
      align-items:flex-start;
      gap:10px;
      color:#475569;
      line-height:1.5;
    }
    .hero-list i{
      color:#1aa39b;
      margin-top:2px;
    }

    .about-section{
      padding:70px 10%;
      background:#ffffff;
    }
    .about-section.alt{
      background:#f8fbff;
    }
    .section-title{
      font-size:28px;
      margin:0 0 10px 0;
      color:#0f172a;
    }
    .section-sub{
      color:#64748b;
      margin:0 0 24px 0;
      max-width:700px;
      line-height:1.7;
    }
    .about-cards{
      display:grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap:24px;
    }
    .about-card{
      background:#ffffff;
      padding:22px;
      border-radius:16px;
      border:1px solid #e2e8f0;
      box-shadow:0 8px 20px rgba(15,23,42,0.06);
      transition:transform 0.25s ease, box-shadow 0.25s ease;
    }
    .about-card:hover{
      transform:translateY(-6px);
      box-shadow:0 16px 26px rgba(15,23,42,0.12);
    }
    .about-card i{
      font-size:26px;
      color:#1e6de0;
      background:#e8f1ff;
      width:44px;
      height:44px;
      border-radius:12px;
      display:flex;
      align-items:center;
      justify-content:center;
      margin-bottom:14px;
    }
    .about-card h3{
      margin:0 0 8px 0;
      font-size:18px;
      color:#0f172a;
    }
    .about-card p{
      margin:0;
      color:#64748b;
      line-height:1.6;
    }

    .stats-grid{
      display:grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap:18px;
      margin-top:24px;
    }
    .stat-tile{
      background:#0f172a;
      color:#e2e8f0;
      padding:20px;
      border-radius:16px;
    }
    .stat-tile h4{
      margin:0 0 6px 0;
      font-size:22px;
      color:#ffffff;
    }
    .stat-tile p{
      margin:0;
      color:#cbd5f0;
      font-size:14px;
    }

    .timeline{
      display:grid;
      gap:18px;
    }
    .timeline-item{
      display:flex;
      gap:16px;
      align-items:flex-start;
      background:#ffffff;
      border:1px solid #e2e8f0;
      border-radius:14px;
      padding:16px;
    }
    .timeline-step{
      width:36px;
      height:36px;
      border-radius:10px;
      background:#1aa39b;
      color:#ffffff;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      flex-shrink:0;
    }
    .timeline-item h4{
      margin:0 0 6px 0;
      font-size:17px;
      color:#0f172a;
    }
    .timeline-item p{
      margin:0;
      color:#64748b;
      line-height:1.6;
    }

    @media(max-width:980px){
      .about-hero-grid{grid-template-columns:1fr;}
      .about-title{font-size:40px;}
      .about-cards{grid-template-columns:1fr;}
      .stats-grid{grid-template-columns:repeat(2, minmax(0, 1fr));}
    }
    @media(max-width:640px){
      .about-hero{padding:110px 8% 70px;}
      .about-title{font-size:34px;}
      .stats-grid{grid-template-columns:1fr;}
    }
  </style>
</head>
<body>

<!-- NAVIGATION -->
<header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

    <!-- Logo -->
    <a href="index.php" class="inline-flex items-center">
      <div class="flex items-center gap-3 font-extrabold text-lg text-blue-700">
        <span class="w-11 h-11 rounded-full bg-blue-500/10 flex items-center justify-center">
          <i data-lucide="globe" class="w-6 h-6 text-teal-600"></i>
        </span>
       MediEco
      </div>
    </a>

    <!-- Nav Links -->
    <nav class="nav-shell hidden md:flex items-center gap-2 bg-white/70 border border-slate-200 rounded-full px-3 py-2 shadow-sm">

      <a href="index.php" data-nav
        class="nav-link flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
        <i data-lucide="home" class="w-4 h-4"></i>
        Home
      </a>

      <a href="about.php" data-nav
        class="nav-link flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
        About 
      </a>

      <a href="services.php" data-nav
        class="nav-link flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
        <i data-lucide="wrench" class="w-4 h-4"></i>
        Services
      </a>

      <a href="contact.php" data-nav
        class="nav-link flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
        <i data-lucide="info" class="w-4 h-4"></i>
        Contact
      </a>

      <a href="plan.php" data-nav
        class="nav-link flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
        <i data-lucide="info" class="w-4 h-4"></i>
        Plan
      </a>
    </nav>

    <!-- Right side: Theme icon + Login/Dashboard -->
    <div class="flex items-center gap-4">
      <!-- Theme Toggle -->
      <button id="themeToggle"
        class="w-11 h-11 rounded-full border border-slate-200 bg-white/70 hover:bg-slate-100 transition flex items-center justify-center">
        <i id="themeIcon" data-lucide="moon" class="w-5 h-5 text-slate-700"></i>
      </button>

      <?php if ($user): ?>
        <a href="<?php echo htmlspecialchars($dashboardUrl); ?>"
          class="px-6 py-2 rounded-full bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition shadow">
          Dashboard
        </a>
        <a href="<?php echo htmlspecialchars($profileUrl); ?>"
          class="w-11 h-11 rounded-full border border-slate-200 bg-white/70 hover:bg-slate-100 transition flex items-center justify-center"
          aria-label="Open profile">
          <span class="profile-initial"><?php echo htmlspecialchars($emailInitial ?: 'U'); ?></span>
        </a>
      <?php else: ?>
        <a href="login.html"
          class="px-6 py-2 rounded-full bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition shadow">
          Log In
        </a>
      <?php endif; ?>
    </div>

  </div>
</header>
<a href="index.php" class="back-home">
  <i data-lucide="arrow-left" class="w-4 h-4"></i>
  Back to Home
</a>
<!-- Hero -->
<section class="about-hero">
  <div class="about-hero-grid">
    <div>
      <span class="about-kicker">
        <i data-lucide="sparkles" class="w-4 h-4"></i>
        About MediEco
      </span>
      <h1 class="about-title">A smarter, safer way to manage biomedical waste.</h1>
      <p class="about-lead">
        We partner with hospitals and clinics to streamline collection, compliance,
        and disposal using real-time tracking, transparent reporting, and a focus
        on environmental responsibility.
      </p>
    </div>
    <div class="hero-card">
      <h3>What sets us apart</h3>
      <ul class="hero-list">
        <li><i class="fa-solid fa-circle-check"></i> Live visibility into pickups, status, and certificates.</li>
        <li><i class="fa-solid fa-circle-check"></i> End-to-end compliance with audit-ready documentation.</li>
        <li><i class="fa-solid fa-circle-check"></i> GPS-enabled routing for safer, faster collections.</li>
        <li><i class="fa-solid fa-circle-check"></i> Support for hospitals, labs, and multi-site networks.</li>
      </ul>
    </div>
  </div>
</section>

<!-- Mission + Vision -->
<section class="about-section">
  <h2 class="section-title">Who We Are</h2>
  <p class="section-sub">
    MediEco is a modern hospital waste management platform built for safety,
    compliance, and operational clarity. Our tools help healthcare teams track
    waste from segregation to final disposal with zero guesswork.
  </p>

  <div class="about-cards">
    <div class="about-card">
      <i class="fa-solid fa-leaf"></i>
      <h3>Eco-First Operations</h3>
      <p>We minimize environmental impact with safe segregation, transport, and disposal.</p>
    </div>
    <div class="about-card">
      <i class="fa-solid fa-shield-heart"></i>
      <h3>Health & Safety</h3>
      <p>Protecting staff, patients, and collectors with strict protocols and training.</p>
    </div>
    <div class="about-card">
      <i class="fa-solid fa-chart-line"></i>
      <h3>Smart Tracking</h3>
      <p>Digital reporting with real-time analytics and compliance-ready records.</p>
    </div>
  </div>
</section>

<!-- Impact -->
<section class="about-section alt">
  <h2 class="section-title">Our Impact</h2>
  <p class="section-sub">
    We focus on measurable results that matter to hospitals: fewer risks,
    faster compliance, and transparent reporting at every step.
  </p>
  <div class="stats-grid">
    <div class="stat-tile">
      <h4>500+</h4>
      <p>Hospitals served across regions</p>
    </div>
    <div class="stat-tile">
      <h4>1200+</h4>
      <p>Daily pickups and tracked routes</p>
    </div>
    <div class="stat-tile">
      <h4>50k</h4>
      <p>Tons safely treated</p>
    </div>
    <div class="stat-tile">
      <h4>100%</h4>
      <p>Safety and audit readiness</p>
    </div>
  </div>
</section>

<!-- Process -->
<section class="about-section">
  <h2 class="section-title">How It Works</h2>
  <p class="section-sub">
    A simple, clear process designed for busy healthcare teams.
  </p>
  <div class="timeline">
    <div class="timeline-item">
      <div class="timeline-step">1</div>
      <div>
        <h4>Segregate & Report</h4>
        <p>Teams log waste type, quantity, and urgency directly into the system.</p>
      </div>
    </div>
    <div class="timeline-item">
      <div class="timeline-step">2</div>
      <div>
        <h4>Collect & Track</h4>
        <p>Collectors receive assignments with GPS routing and real-time updates.</p>
      </div>
    </div>
    <div class="timeline-item">
      <div class="timeline-step">3</div>
      <div>
        <h4>Dispose & Certify</h4>
        <p>Disposal certificates and audit trails are generated automatically.</p>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="footer-inner">
    <div>
      <div class="footer-logo">
        <span class="footer-logo-icon"><i class="fa-solid fa-check"></i></span>
        <span>MediEco</span>
      </div>
      <p>Leading the way in responsible biomedical waste management. Protecting healthcare workers, communities, and the environment.</p>
    </div>
    <div>
      <div class="footer-title">Links</div>
      <a href="about.php">About Us</a>
      <a href="services.php">Services</a>
      <a href="contact.php">Contact</a>
      <a href="login.html">Portal Login</a>
    </div>
    <div>
      <div class="footer-title">Contact</div>
      <p>123 Green Street, Eco City</p>
      <p>support@ecomed.com</p>
      <p>+1 (555) 123-4567</p>
    </div>
  </div>
  <div class="footer-divider"></div>
  <div class="footer-bottom">&copy; 2026 MediEco Waste Management. All rights reserved.</div>
</footer>
<script>
  lucide.createIcons();
</script>
<script src="theme.js"></script>

</body>
</html>





