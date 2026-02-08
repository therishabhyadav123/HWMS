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
    <title>MediEco | Hospital Waste Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="theme.css">
    <link rel="stylesheet" href="chatbot.css">
    <!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        inter: ['Inter', 'sans-serif']
      }
    }
  }
}
</script>

</head>
<body class="font-inter bg-[#f4f8ff] text-slate-900">

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
<!-- ================= HERO SECTION ================= -->
<section class="hero min-h-[calc(100vh-80px)] flex items-center justify-center text-center px-6 bg-gradient-to-b from-blue-50 to-white">
  <div class="max-w-4xl">

    <h1 class="text-5xl md:text-6xl font-extrabold leading-tight">
      Safe & Sustainable <br>
      <span class="text-blue-600">Medical Waste</span>
      <span class="text-teal-500">Solutions</span>
    </h1>

    <p class="mt-6 text-lg text-gray-500 leading-relaxed">
      We provide comprehensive biomedical waste management services for
      hospitals, clinics, and laboratories. Ensuring compliance, safety,
      and environmental responsibility.
    </p>

    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-6">
      <a href="signup.html"
        class="flex items-center gap-2 bg-blue-600 text-white px-8 py-4 rounded-full font-semibold shadow-lg hover:bg-blue-700 transition">
        Request Pickup &rarr;
      </a>

      <a href="learn_more.html"
        class="px-8 py-4 rounded-full border border-slate-900 font-semibold hover:bg-slate-100 transition">
        Learn More
      </a>
    </div>

  </div>
</section>

    <section id="waste">
        <div class="head">
            <h1>Types of Waste</h1>
            <p>Correct segregation is the first step to safety.</p>
        </div>
        
        <div class="teams">
            <div class="card" data-title="General Waste" data-desc="Everyday, non-hazardous waste such as paper, food scraps, packaging, and plastics from non-clinical areas. These items are safe to handle with standard precautions but still require proper segregation to keep hazardous waste streams clean.">
                <img src="Generals Waste.jpg" alt="General Waste">
                <div class="card-info">
                    <h3>General Waste</h3>
                    <p>Non-hazardous items like paper and food.</p>
                    <a class="learn-more" href="waste_detail.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <div class="card" data-title="Infectious Waste" data-desc="Pathogen-contaminated materials including blood-soaked dressings, laboratory cultures, and bodily fluids. These require sealed containers, strict handling protocols, and specialized treatment to prevent infection spread.">
                <img src="infectious waste.jpg" alt="Infectious Waste">
                <div class="card-info">
                    <h3>Infectious Waste</h3>
                    <p>Contaminated fluids and pathogens.</p>
                    <a class="learn-more" href="waste_detail.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <div class="card" data-title="Sharps Waste" data-desc="Needles, scalpels, blades, and other sharp instruments that can puncture skin. Sharps must be disposed of in rigid, puncture-proof containers and treated as high-risk items.">
                <img src="sharps waste.jpg" alt="Sharps Waste">
                <div class="card-info">
                    <h3>Sharps Waste</h3>
                    <p>Needles and scalpels.</p>
                    <a class="learn-more" href="waste_detail.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </section>
<!-- Services SECTION -->
<section class="services">
  <h1>Our Services</h1>
  <p class="services-desc">
    Comprehensive biomedical waste management solutions designed to
    meet the highest safety and environmental standards.
  </p>

  <div class="services-grid">

    <div class="service-card" data-title="Infectious Waste" data-desc="Safe handling and treatment of waste contaminated with blood and other bodily fluids, including cultures and stocks of infectious agents. These materials require sealed containers and specialized treatment to prevent infection spread." data-img="infectious waste.jpg">
      <div class="icon red">
        <i class="fa-solid fa-shield-virus"></i>
      </div>
      <h3>Infectious Waste</h3>
      <p>
        Safe handling and treatment of waste contaminated with blood and other
        bodily fluids, including cultures and stocks of infectious agents.
      </p>
      <a class="learn-more" href="infectious_waste.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="service-card" data-title="Sharps Disposal" data-desc="Specialized containers and disposal methods for needles, scalpels, and other sharp instruments to prevent injury and infection. Sharps must be placed in rigid, puncture-proof containers." data-img="sharps waste.jpg">
      <div class="icon orange">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <h3>Sharps Disposal</h3>
      <p>
        Specialized containers and disposal methods for needles, scalpels,
        and other sharp instruments to prevent injury and infection.
      </p>
      <a class="learn-more" href="sharps_disposal.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="service-card" data-title="Chemical Waste" data-desc="Management of hazardous chemicals from diagnostic and experimental work, ensuring neutralisation and safe disposal. This includes proper labeling, containment, and transport to approved facilities." data-img="chemical Waste.jpg">
      <div class="icon purple">
        <i class="fa-solid fa-flask"></i>
      </div>
      <h3>Chemical Waste</h3>
      <p>
        Management of hazardous chemicals from diagnostic and experimental work,
        ensuring neutralisation and safe disposal.
      </p>
      <a class="learn-more" href="chemical_waste.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="service-card" data-title="Pharmaceutical Waste" data-desc="Proper disposal of expired, unused, spilt, and contaminated pharmaceutical products, including drugs and vaccines. Segregation and documentation reduce environmental and compliance risks." data-img="Pharmaceutical Waste.jpg">
      <div class="icon blue">
        <i class="fa-solid fa-pills"></i>
      </div>
      <h3>Pharmaceutical Waste</h3>
      <p>
        Proper disposal of expired, unused, spilt, and contaminated
        pharmaceutical products, including drugs and vaccines.
      </p>
      <a class="learn-more" href="pharmaceutical_waste.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="service-card" data-title="Logistics & Transport" data-desc="Secure transport in specialized vehicles equipped with spill kits and GPS tracking to ensure chain of custody. Scheduled pickups and route optimization keep service reliable." data-img="waste.jpg">
      <div class="icon green">
        <i class="fa-solid fa-truck"></i>
      </div>
      <h3>Logistics & Transport</h3>
      <p>
        Secure transport in specialized vehicles equipped with spill kits
        and GPS tracking to ensure chain of custody.
      </p>
      <a class="learn-more" href="logistics_transport.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="service-card" data-title="Compliance Audits" data-desc="Regular safety audits and documentation services to ensure your facility remains compliant with all local and WHO regulations. Includes checklists, reporting, and corrective action guidance." data-img="image.jpg">
      <div class="icon teal">
        <i class="fa-solid fa-clipboard-check"></i>
      </div>
      <h3>Compliance Audits</h3>
      <p>
        Regular safety audits and documentation services to ensure your
        facility remains compliant with all local and WHO regulations.
      </p>
      <a class="learn-more" href="compliance.html">Learn More <i class="fa-solid fa-chevron-right"></i></a>
    </div>

  </div>
</section>

    <!-- PLANS SECTION -->
<div class="wrapper">
  <h1>Choose Your Subscription</h1>
  <p>Safe &bull; Smart &bull; Sustainable Hospital Waste Management</p>

  <div class="plans">

    <!-- BASIC PLAN -->
    <div class="plan">
      <h2>Basic</h2>
      <div class="price">&#8377;199 / Month</div>
      <ul>
        <li><i class="fa fa-check"></i> Waste Reports</li>
        <li><i class="fa fa-check"></i> Basic Dashboard</li>
        <li><i class="fa fa-check"></i> Manual Tracking</li>
      </ul>
      <form action="subscribe.php" method="POST">
        <input type="hidden" name="plan" value="Basic">
        <input type="hidden" name="price" value="199">
        <input type="hidden" name="duration" value="30">
        <button>Subscribe Now</button>
      </form>
    </div>

    <!-- PREMIUM PLAN -->
    <div class="plan popular">
      <h2>Premium</h2>
      <div class="price">&#8377;999 / Year</div>
      <ul>
        <li><i class="fa fa-check"></i> Live Collector Tracking</li>
        <li><i class="fa fa-check"></i> Certificates</li>
        <li><i class="fa fa-check"></i> Analytics Dashboard</li>
        <li><i class="fa fa-check"></i> Priority Support</li>
      </ul>
      <form action="subscribe.php" method="POST">
        <input type="hidden" name="plan" value="Premium">
        <input type="hidden" name="price" value="999">
        <input type="hidden" name="duration" value="365">
        <button>Go Premium</button>
      </form>
    </div>

<!-- Enterprise PLAN -->
    <div class="plan popular">
      <h2>Enterprises</h2>
      <div class="price">10000 / Year</div>
      <ul>
        <li><i class="fa fa-check"></i> Live Collector Tracking</li>
        <li><i class="fa fa-check"></i>  Certificates</li>
        <li><i class="fa fa-check"></i>  Analytics Dashboard</li>
        <li><i class="fa fa-check"></i>  Priority Support</li>
        <li><i class="fa fa-check"></i>  Multi-Hospital Support</li>
        <li><i class="fa fa-check"></i>  Admin Control</li>
        <li><i class="fa fa-check"></i>  Audit Logs</li>
        <li><i class="fa fa-check"></i>  24x7 Support</li>
      </ul>
      <form action="subscribe.php" method="POST">
        <input type="hidden" name="plan" value="Premium">
        <input type="hidden" name="price" value="10000">
        <input type="hidden" name="duration" value="365">
        <button>Go Enterprises</button>
      </form>
    </div>

  </div>
</div>


<section class="stats">
  <div class="stat-box">
    <h1>500+</h1>
    <p>Hospitals Served</p>
  </div>

  <div class="stat-box">
    <h1>1200+</h1>
    <p>Daily Pickups</p>
  </div>

  <div class="stat-box">
    <h1>50k Tons</h1>
    <p>Waste Treated</p>
  </div>

  <div class="stat-box">
    <h1>100%</h1>
    <p>Safety Rating</p>
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
        <div class="footer-bottom">&copy; 2026 MediEco Waste Management. All rights reserved.</div>
    </footer>

    <div class="detail" id="detailModal">
        <button id="closeBtn"><i class="fa-solid fa-xmark"></i></button>
        <div class="content" id="modalContent">
            </div>
    </div>
<!-- CHATBOT ICON -->
<div id="chatbot-toggle">
    <i class="fa-solid fa-comments"></i>
</div>

<!-- CHATBOT BOX -->
<div id="chatbot">
    <div class="chat-header">
        MediEco Assistant 🤖
        <span id="chat-close">&times;</span>
    </div>

    <div class="chat-body" id="chatBody">
        <div class="bot-msg">Hello 👋<br>
        I'm MediEco Assistant.  
        Ask me about hospital waste management.</div>
    </div>

    <div class="chat-input">
        <input type="text" id="userInput" placeholder="Type your question...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>



    <script src="main.js"></script>
    <script src="chatbot.js"></script>
    <script src="theme.js"></script>

</body>
</html>









