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
<title>Subscription Plans | MediEco</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="theme.css">

<style>
/* ---------- GLOBAL ---------- */
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family: 'Poppins', sans-serif;
}
body{
  background: linear-gradient(135deg, #eef5ff, #f6fbff);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  align-items:stretch;
  justify-content:flex-start;
  color:#0f172a;
}

/* ---------- ANIMATIONS ---------- */
@keyframes fadeUp {
  from { opacity:0; transform:translateY(40px); }
  to { opacity:1; transform:translateY(0); }
}

@keyframes glow {
  0% { box-shadow:0 0 10px rgba(255,255,255,0.2); }
  50% { box-shadow:0 0 25px rgba(255,255,255,0.6); }
  100% { box-shadow:0 0 10px rgba(255,255,255,0.2); }
}

/* ---------- CONTAINER ---------- */
.wrapper{
  width:100%;
  max-width:1100px;
  margin:80px auto 60px;
  text-align:center;
  animation: fadeUp 1.2s ease forwards;
}

.wrapper h1{
  font-size:42px;
  margin-bottom:10px;
}

.wrapper p{
  font-size:18px;
  margin-bottom:40px;
  opacity:0.9;
}

/* ---------- PLANS ---------- */

.plans{
  display:flex;
  flex-wrap:wrap;
  gap:30px;
  justify-content:center;
}

.plan{
  background:#fff;
  color:#333;
  width:300px;
  padding:35px 25px;
  border-radius:18px;
  box-shadow:0 10px 25px rgba(0,0,0,0.25);
  animation: fadeUp 1.4s ease forwards;
  transition:0.4s ease;
  position:relative;
  overflow:hidden;
}

.plan::before{
  content:"";
  position:absolute;
  top:0;
  left:-100%;
  width:100%;
  height:100%;
  background:linear-gradient(120deg, transparent, rgba(255,255,255,0.4), transparent);
  transition:0.6s;
}

.plan:hover::before{
  left:100%;
}

.plan:hover{
  transform: translateY(-15px) scale(1.03);
}

/* ---------- HIGHLIGHT ---------- */
.plan.popular{
  border:3px solid #1e6de0;
  animation: glow 2.5s infinite;
}

.plan h2{
  font-size:26px;
  margin-bottom:10px;
  color:#1e6de0;
}

.price{
  font-size:32px;
  margin:15px 0;
  font-weight:bold;
}

.plan ul{
  list-style:none;
  margin:20px 0;
}

.plan ul li{
  margin:12px 0;
}

.plan ul li i{
  color:#1e6de0;
  margin-right:8px;
}

/* ---------- BUTTON ---------- */
.plan button{
  margin-top:20px;
  width:100%;
  padding:14px;
  border:none;
  border-radius:30px;
  background:#1e6de0;
  color:#fff;
  font-size:17px;
  cursor:pointer;
  transition:0.3s;
}

.plan button:hover{
  background:#165bb8;
  letter-spacing:1px;
}

/* ---------- FOOTER ---------- */
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
}
.back-home{
  position:fixed;
  top:24px;
  left:24px;
  display:flex;
  align-items:center;
  gap:8px;
  color:#64748b;
  font-weight:700;
  text-decoration:none;
  transition:color 0.2s ease;
  z-index:60;
  background:rgba(255,255,255,0.8);
  backdrop-filter:blur(6px);
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #e2e8f0;
}
.back-home:hover{
  color:#0f172a;
}
.back-home i{
  transition:transform 0.2s ease;
}
.back-home:hover i{
  transform:translateX(-4px);
}
</style>
</head>

<body>
  <a href="index.php" class="back-home">
    <i class="fa fa-arrow-left"></i> Back to Home
  </a>


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
<script src="theme.js"></script>
</body>
</html>










