<?php
session_start();

// --- DATABASE CONNECTION ---
$host = 'localhost';
$dbname = 'donation';
$username = 'root'; // Update with your DB username
$password = '';     // Update with your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In production, log this error instead of showing it
    die("Database Connection Failed: " . $e->getMessage());
}

// --- FETCH DATA ---

// 1. Get Total Active Donors
try {
    $stmtDonor = $pdo->query("SELECT COUNT(*) FROM donors WHERE is_active = 1");
    $donorCount = $stmtDonor->fetchColumn();
} catch (Exception $e) {
    $donorCount = 0;
}

// 2. Get Total Lives Saved
// Logic: specific count from 'completed' blood requests (assuming 1 request = 1 life for simplicity, or just completed requests)
try {
    $stmtLives = $pdo->query("SELECT COUNT(*) FROM donation_requests WHERE status = 'accepted'");
    $livesSaved = $stmtLives->fetchColumn();
} catch (Exception $e) {
    $livesSaved = 0;
}

// 3. Get Available Blood Groups
try {
    $stmtGroups = $pdo->query("SELECT group_name FROM blood_groups ORDER BY group_name ASC");
    $bgList = $stmtGroups->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $bgList = []; // Fallback
}

// If database is empty, use default list to maintain UI
if (empty($bgList)) {
    $bgList = ["A+","A-","B+","B-","AB+","AB-","O+","O-"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Orion | Blood Donation Network</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#FFF5F6] font-sans antialiased">

<header class="bg-[#D1001C] text-white py-2 px-6 md:px-20 flex justify-between items-center text-sm">
  <div class="flex gap-6">
    <span>📞 +1 (555) 123-4567</span>
    <span>✉ help@orion.com</span>
  </div>
</header>

<nav class="bg-white py-4 px-6 md:px-20 flex justify-between items-center shadow-sm">
  <div class="flex items-center gap-2">
    <div class="bg-[#D1001C] p-2 rounded-lg">🩸</div>
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Orion</h1>
      <p class="text-[10px] text-gray-500 uppercase tracking-widest">Saving Lives Together</p>
    </div>
  </div>

  <div class="hidden md:flex gap-8 text-gray-600 font-medium">
    <a href="#" class="hover:text-red-600">About</a>
    <a href="#" class="hover:text-red-600">How to Donate</a>
    <a href="#" class="hover:text-red-600">Contact</a>
  </div>

  <div class="relative">
    <button onclick="toggleLoginMenu()" class="bg-red-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-red-700">
      <?php if(isset($_SESSION['user_id'])): ?>
        👤 My Account
      <?php else: ?>
        🔐 Login
      <?php endif; ?>
    </button>

    <div id="loginMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-50">
      <a href="auth/login.php?role=admin" class="block px-4 py-2 hover:bg-gray-100">👨‍💼 Admin</a>
      <a href="src/donor/register.php" class="block px-4 py-2 hover:bg-gray-100">❤️ Donor</a>
      <a href="src/receipent/login.php" class="block px-4 py-2 hover:bg-gray-100">🏥 Recipient</a>
    </div>
  </div>
</nav>

<section class="relative h-[85vh] flex items-center justify-center">
  <div class="absolute inset-0">
    <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b"
         class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/60"></div>
  </div>

  <div class="relative z-10 text-center text-white px-6 max-w-3xl">
    <h1 class="text-4xl md:text-6xl font-extrabold mb-6">
      Donate Blood, <span class="text-red-500">Save Lives</span>
    </h1>
    <p class="text-lg md:text-xl mb-8 text-gray-200">
      Orion connects blood donors with patients instantly.
      One donation can save up to three lives.
    </p>

    <div class="flex flex-col md:flex-row gap-4 justify-center">
      <a href="src/donor/register.php" class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-lg font-semibold">
        ❤️ Become a Donor
      </a>
      <a href="src/receipent/login.php" class="bg-white text-red-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100">
        🏥 Request Blood
      </a>
    </div>
  </div>
</section>

<section class="py-20 px-6 md:px-20 bg-white">
  <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
    Why Orion Is Important
  </h2>

  <div class="grid md:grid-cols-3 gap-8">
    <div class="bg-[#FFF5F6] p-6 rounded-xl shadow text-center">
      <h3 class="text-xl font-semibold mb-3">🚑 Emergency Ready</h3>
      <p class="text-gray-600">Instant donor search during critical emergencies.</p>
    </div>

    <div class="bg-[#FFF5F6] p-6 rounded-xl shadow text-center">
      <h3 class="text-xl font-semibold mb-3">🔐 Verified Donors</h3>
      <p class="text-gray-600">Safe & verified donors to ensure reliability.</p>
    </div>

    <div class="bg-[#FFF5F6] p-6 rounded-xl shadow text-center">
      <h3 class="text-xl font-semibold mb-3">🌍 Community Driven</h3>
      <p class="text-gray-600">Connecting humanity through blood donation.</p>
    </div>
  </div>
</section>

<section class="py-16 bg-red-600 text-white text-center">
  <div class="grid md:grid-cols-3 gap-8">
    <div>
      <h3 class="text-4xl font-bold"><?php echo number_format($donorCount); ?>+</h3>
      <p>Registered Donors</p>
    </div>
    <div>
      <h3 class="text-4xl font-bold"><?php echo number_format($livesSaved); ?>+</h3>
      <p>Lives Saved</p>
    </div>
    <div>
      <h3 class="text-4xl font-bold">24/7</h3>
      <p>Emergency Support</p>
    </div>
  </div>
</section>

<section class="py-20 px-6 md:px-20 bg-white">
  <h2 class="text-3xl font-bold text-center mb-10">Available Blood Groups</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    <?php
      // Dynamic Loop from Database
      foreach($bgList as $g){
        echo "<div class='bg-[#FFF5F6] p-6 rounded-xl shadow font-bold text-xl'>".htmlspecialchars($g)."</div>";
      }
    ?>
  </div>
</section>

<section class="py-20 bg-[#D1001C] text-white text-center px-6">
  <h2 class="text-3xl md:text-4xl font-bold mb-6">
    Your Blood Can Save Someone’s Life Today
  </h2>
  <a href="src/donor/register.php"
     class="bg-white text-red-600 px-10 py-4 rounded-lg font-semibold hover:bg-gray-100">
    Become a Donor Now
  </a>
</section>

<footer class="bg-gray-900 text-gray-300 py-10 text-center">
  <p>© <?php echo date("Y"); ?> Orion Blood Donation Network</p>
  <p class="text-sm mt-2">Saving Lives Together 🩸</p>
</footer>

<script>
function toggleLoginMenu() {
  document.getElementById("loginMenu").classList.toggle("hidden");
}
</script>

</body>
</html>