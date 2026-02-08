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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MediEco</title>

  <!-- TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Inter Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="theme.css">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body { font-family: Inter, sans-serif; }
    .hover-elevate:hover { transform: translateY(-4px); transition: 200ms; }
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

<body class="min-h-screen flex flex-col bg-[#f4f8ff] text-slate-900">

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

  <!-- TOAST -->
  <div id="toast"
    class="fixed top-24 right-4 z-[999] hidden max-w-sm bg-white border border-slate-200 shadow-2xl rounded-2xl p-4">
    <div class="flex items-start gap-3">
      <div class="w-10 h-10 rounded-xl bg-teal-500/10 flex items-center justify-center">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-teal-600"></i>
      </div>
      <div class="flex-1">
        <p class="font-black text-slate-900">Message Sent</p>
        <p class="text-sm text-slate-500">Our sales team will get back to you within 24 hours.</p>
      </div>
      <button onclick="hideToast()" class="text-slate-400 hover:text-slate-700 font-black">&times;</button>
    </div>
  </div>

  <!-- MAIN -->
  <main class="flex-1 py-20 px-4 sm:px-6 lg:px-8 pt-28">
    <div class="max-w-7xl mx-auto">

      <!-- Back button -->
      <a href="index.php"
        class="fixed top-24 left-6 z-50 flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors group font-bold bg-white/80 backdrop-blur-md px-3 py-2 rounded-full border border-slate-200 shadow-sm">
        <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
        Back to Home
      </a>

      <!-- Header -->
      <div class="text-center mb-16">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Contact Our Sales Team</h1>
        <p class="text-xl text-slate-500 max-w-2xl mx-auto">
          Have questions about our enterprise solutions or custom IoT integrations? We're here to help.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

        <!-- Contact Info -->
        <div class="space-y-8">

          <div>
            <h2 class="text-2xl font-extrabold mb-6">Get in Touch</h2>

            <div class="space-y-6">

              <!-- Email -->
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-teal-500/10 text-teal-600">
                  <i data-lucide="mail" class="w-6 h-6"></i>
                </div>
                <div>
                  <p class="font-black">Email Us</p>
                  <p class="text-slate-500">sales@carboniot.com</p>
                </div>
              </div>

              <!-- Phone -->
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-teal-500/10 text-teal-600">
                  <i data-lucide="phone" class="w-6 h-6"></i>
                </div>
                <div>
                  <p class="font-black">Call Us</p>
                  <p class="text-slate-500">+1 (555) 123-4567</p>
                </div>
              </div>

              <!-- Address -->
              <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-teal-500/10 text-teal-600">
                  <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div>
                  <p class="font-black">Visit Us</p>
                  <p class="text-slate-500">
                    123 Green Tech Way, Sustainability City, ST 12345
                  </p>
                </div>
              </div>

            </div>
          </div>

          <!-- Enterprise Ready Card -->
          <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 hover-elevate">
            <h3 class="text-lg font-black mb-2">Enterprise Ready</h3>
            <p class="text-slate-500">
              Our solutions are designed for large-scale deployments. Ask about our multi-building management,
              advanced analytics API, and dedicated support packages.
            </p>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl p-6 md:p-8 hover-elevate">

          <form id="contactForm" class="space-y-6">

            <!-- Full Name -->
            <div>
              <label class="block text-sm font-black mb-2">Full Name</label>
              <input id="name" type="text" placeholder="John Doe"
                class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500"
              />
              <p id="err-name" class="text-xs mt-2 text-red-600 hidden"></p>
            </div>

            <!-- Work Email -->
            <div>
              <label class="block text-sm font-black mb-2">Work Email</label>
              <input id="email" type="email" placeholder="john@company.com"
                class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500"
              />
              <p id="err-email" class="text-xs mt-2 text-red-600 hidden"></p>
            </div>

            <!-- Company -->
            <div>
              <label class="block text-sm font-black mb-2">Company Name</label>
              <input id="company" type="text" placeholder="Acme Corp"
                class="w-full border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500"
              />
              <p id="err-company" class="text-xs mt-2 text-red-600 hidden"></p>
            </div>

            <!-- Message -->
            <div>
              <label class="block text-sm font-black mb-2">How can we help?</label>
              <textarea id="message" placeholder="Tell us about your project..."
                class="w-full min-h-[120px] resize-none border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500"></textarea>
              <p id="err-message" class="text-xs mt-2 text-red-600 hidden"></p>
            </div>

            <!-- Submit -->
            <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-4 py-3 font-black transition flex items-center justify-center gap-2">
              Send Message <i data-lucide="send" class="w-4 h-4"></i>
            </button>

          </form>
        </div>
      </div>

      <!-- Map -->
      <div class="mt-12 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
          <h2 class="text-xl font-extrabold">Map</h2>
          <p class="text-sm text-slate-500">Find our office and plan your visit.</p>
        </div>
        <div class="w-full">
          <iframe
            title="MediEco Location Map"
            src="https://www.google.com/maps?q=MediEco%20Waste%20Management&output=embed"
            class="w-full h-[260px] md:h-[300px] border-0"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>
  </main>
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
    document.getElementById("year").innerText = new Date().getFullYear();

    function showError(id, msg) {
      const el = document.getElementById("err-" + id);
      el.innerText = msg;
      el.classList.remove("hidden");
    }

    function hideError(id) {
      const el = document.getElementById("err-" + id);
      el.innerText = "";
      el.classList.add("hidden");
    }

    function validateEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showToast() {
      const toast = document.getElementById("toast");
      toast.classList.remove("hidden");
      setTimeout(() => toast.classList.add("hidden"), 4000);
    }

    function hideToast() {
      document.getElementById("toast").classList.add("hidden");
    }

    document.getElementById("contactForm").addEventListener("submit", function (e) {
      e.preventDefault();

      const name = document.getElementById("name").value.trim();
      const email = document.getElementById("email").value.trim();
      const company = document.getElementById("company").value.trim();
      const message = document.getElementById("message").value.trim();

      let ok = true;

      // Name
      if (name.length < 2) { showError("name", "Name must be at least 2 characters"); ok = false; }
      else hideError("name");

      // Email
      if (!validateEmail(email)) { showError("email", "Invalid email address"); ok = false; }
      else hideError("email");

      // Company
      if (company.length < 2) { showError("company", "Company name must be at least 2 characters"); ok = false; }
      else hideError("company");

      // Message
      if (message.length < 10) { showError("message", "Message must be at least 10 characters"); ok = false; }
      else hideError("message");

      if (!ok) return;

      console.log("Form submitted:", { name, email, company, message });

      // Reset form
      document.getElementById("contactForm").reset();

      // Toast
      showToast();
    });

    window.hideToast = hideToast;
  </script>
  <script src="theme.js"></script>

</body>
</html>














