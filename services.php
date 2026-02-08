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
<title>Our Services | MediEco</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="theme.css">

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: "Segoe UI", Arial, sans-serif;
}

body{
    background:#f4f8ff;
    color:#0f172a;
}
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

/* SERVICES */
.services{
    padding:80px 8%;
    text-align:center;
    margin-top:80px; /* header fix */
}

.services h1{
    font-size:42px;
    font-weight:700;
    margin-bottom:15px;
}

.services p{
    font-size:18px;
    color:#64748b;
    max-width:750px;
    margin:0 auto 60px;
}

/* GRID */
.service-grid{
    display:grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap:30px;
}

/* CARD */
.service-card{
    background:#fff;
    border-radius:18px;
    padding:35px 30px;
    text-align:left;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
    transition:0.3s ease;
}
.service-card .learn-more{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:14px;
    color:#0f766e;
    font-weight:600;
    text-decoration:none;
    transition:0.3s ease;
}
.service-card .learn-more:hover{
    color:#115e59;
    transform:translateX(2px);
}

.service-card:hover{
    transform:translateY(-8px);
    box-shadow:0 18px 35px rgba(0,0,0,0.1);
}

.icon-box{
    width:55px;
    height:55px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    margin-bottom:25px;
}

.red{background:#e8f1ff;color:#1e6de0;}
.orange{background:#e6f7f6;color:#1aa39b;}
.purple{background:#e8f1ff;color:#1e6de0;}
.blue{background:#e8f1ff;color:#1e6de0;}
.green{background:#e6f7f6;color:#1aa39b;}
.teal{background:#e6f7f6;color:#1aa39b;}

@media(max-width:600px){
    .services h1{font-size:30px;}
    .services p{font-size:16px;}
}
@media(max-width:980px){
    .service-grid{
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media(max-width:640px){
    .service-grid{
        grid-template-columns: 1fr;
    }
}
.footer{
  background:#0b132b;
  color:#e2e8f0;
  padding:60px 10% 30px;
}
.footer-inner{
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap:40px;
  align-items:start;
}
.footer-logo{
  display:flex;
  align-items:center;
  gap:12px;
  font-weight:700;
  font-size:20px;
  color:#ffffff;
  margin-bottom:16px;
}
.footer-logo-icon{
  width:38px;
  height:38px;
  border-radius:999px;
  background:#1e6de0;
  display:flex;
  align-items:center;
  justify-content:center;
  color:#ffffff;
  font-size:16px;
}
.footer-title{
  color:#ffffff;
  font-weight:700;
  margin-bottom:14px;
}
.footer a{
  color:#cbd5f0;
  text-decoration:none;
  display:block;
  margin:10px 0;
}
.footer a:hover{
  color:#ffffff;
}
.footer p{
  color:#cbd5f0;
  line-height:1.7;
}
.footer-divider{
  height:1px;
  background:rgba(148,163,184,0.25);
  margin:30px 0 18px;
}
.footer-bottom{
  text-align:center;
  color:#94a3b8;
  font-size:14px;
}</style>
</head>

<body class="min-h-screen flex flex-col">

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
<!-- SERVICES -->
<section class="services">
<h1>Our Services</h1>
<p>Comprehensive biomedical waste management solutions designed to meet the highest safety and environmental standards.</p>

<div class="service-grid">

<div class="service-card" data-title="Infectious Waste" data-desc="Safe handling and treatment of infectious biomedical waste, including contaminated dressings, cultures, and fluids. Requires sealed containers and regulated treatment protocols." data-img="infectious waste.jpg">
  <div class="icon-box red"><i class="fa-solid fa-shield-virus"></i></div>
  <h3>Infectious Waste</h3>
  <p>Safe handling and treatment of infectious biomedical waste.</p>
  <a class="learn-more" href="infectious_waste.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
</div>

<div class="service-card" data-title="Sharps Disposal" data-desc="Needles, blades and sharp instruments disposal using rigid, puncture-proof containers to prevent injuries and cross-contamination." data-img="sharps waste.jpg">
  <div class="icon-box orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
  <h3>Sharps Disposal</h3>
  <p>Needles, blades and sharp instruments disposal.</p>
  <a class="learn-more" href="sharps_disposal.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
</div>

<div class="service-card" data-title="Chemical Waste" data-desc="Neutralisation and safe disposal of chemical waste from labs and diagnostics, with secure containment, labeling, and authorized processing." data-img="chemical Waste.jpg">
  <div class="icon-box purple"><i class="fa-solid fa-flask"></i></div>
  <h3>Chemical Waste</h3>
  <p>Neutralisation and safe disposal of chemical waste.</p>
  <a class="learn-more" href="chemical_waste.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
</div>

<div class="service-card" data-title="Pharmaceutical Waste" data-desc="Expired and unused medicines disposal with proper segregation and documentation to protect public health and the environment." data-img="Pharmaceutical Waste.jpg">
  <div class="icon-box blue"><i class="fa-solid fa-pills"></i></div>
  <h3>Pharmaceutical Waste</h3>
  <p>Expired and unused medicines disposal.</p>
  <a class="learn-more" href="pharmaceutical_waste.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
</div>

<div class="service-card" data-title="Logistics & Transport" data-desc="GPS-enabled secure transport system with spill kits and chain-of-custody tracking for every pickup." data-img="waste.jpg">
  <div class="icon-box green"><i class="fa-solid fa-truck"></i></div>
  <h3>Logistics & Transport</h3>
  <p>GPS-enabled secure transport system.</p>
  <a class="learn-more" href="logistics_transport.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
</div>

<div class="service-card" data-title="Compliance Audits" data-desc="WHO & local regulation compliance audits with checklists, reporting, and corrective action guidance." data-img="image.jpg">
  <div class="icon-box teal"><i class="fa-solid fa-clipboard-check"></i></div>
  <h3>Compliance Audits</h3>
  <p>WHO & local regulation compliance audits.</p>
  <a class="learn-more" href="compliance.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
</div>

</div>
</section>

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
  <div class="footer-bottom">&copy; 2026 MediEcoWaste Management. All rights reserved.</div>
</footer>

<script>
lucide.createIcons();
</script>
<script src="theme.js"></script>

</body>
</html>


