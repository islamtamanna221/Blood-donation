<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$recipient_id = (int)$_SESSION['recipient_id'];

/* ================= RECIPIENT INFO ================= */
$q = $conn->query("
    SELECT u.full_name, r.location, r.total_requests, r.active_requests
    FROM users u
    JOIN recipients r ON r.user_id = u.id
    WHERE u.id = $uid
");
$recipient = $q->fetch_assoc();

/* ================= AREAS ================= */
$areas = $conn->query("SELECT id, area_name FROM areas ORDER BY area_name");

/* ================= SEARCH DONORS ================= */
$searched_donors = null;
if (!empty($_GET['area_id'])) {
    $area_id = (int)$_GET['area_id'];

    $searched_donors = $conn->query("
        SELECT 
            u.full_name, 
            u.email, 
            bg.group_name AS blood_group, 
            d.total_donations
        FROM donors d
        JOIN users u ON d.user_id = u.id
        JOIN donor_verifications dv ON dv.donor_id = d.id
        JOIN blood_groups bg ON d.blood_group_id = bg.id
        WHERE dv.status = 'approved' 
          AND d.is_active = 1 
          AND d.area_id = $area_id
        ORDER BY d.total_donations DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipient Dashboard | Orion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel {
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen text-slate-800">

<nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-2">
                <div class="bg-rose-600 p-2 rounded-lg">
                    <i data-lucide="droplet" class="text-white w-5 h-5 fill-current"></i>
                </div>
                <span class="text-xl font-bold text-slate-900 tracking-tight">Orion</span>
            </div>

            <div class="flex items-center gap-6">
                <div class="hidden md:flex flex-col items-end mr-2">
                    <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($recipient['full_name']) ?></span>
                    <span class="text-xs text-slate-500 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i> <?= htmlspecialchars($recipient['location']) ?>
                    </span>
                </div>
                <a href="../../auth/logout.php" class="text-slate-500 hover:text-rose-600 transition p-2 rounded-full hover:bg-rose-50">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="md:col-span-1 glass-panel rounded-2xl p-6 bg-gradient-to-br from-rose-600 to-rose-700 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-2xl font-bold mb-2">Welcome Back!</h1>
                <p class="text-rose-100 text-sm mb-4">Manage your requests and find donors quickly.</p>
                <div class="inline-flex items-center gap-2 bg-white/20 px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Online
                </div>
            </div>
            <i data-lucide="activity" class="absolute -bottom-4 -right-4 w-32 h-32 text-white/10"></i>
        </div>

        <div class="glass-panel rounded-2xl p-6 flex items-center gap-4">
            <div class="bg-blue-50 p-3 rounded-xl text-blue-600">
                <i data-lucide="file-text" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Total Requests</p>
                <p class="text-3xl font-bold text-slate-800"><?= $recipient['total_requests'] ?></p>
            </div>
        </div>

        <div class="glass-panel rounded-2xl p-6 flex items-center gap-4">
            <div class="bg-amber-50 p-3 rounded-xl text-amber-600">
                <i data-lucide="clock" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Active Requests</p>
                <p class="text-3xl font-bold text-slate-800"><?= $recipient['active_requests'] ?></p>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        
        <div class="glass-panel rounded-2xl p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i data-lucide="search" class="w-6 h-6 text-rose-500"></i> Find Donors
            </h2>
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-grow">
                    <i data-lucide="map" class="absolute left-4 top-3.5 text-slate-400 w-5 h-5"></i>
                    <select name="area_id" class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition appearance-none text-slate-700 font-medium">
                        <option value="">Select Area to search donors...</option>
                        <?php while ($a = $areas->fetch_assoc()): ?>
                            <option value="<?= $a['id'] ?>" <?= (isset($_GET['area_id']) && $_GET['area_id'] == $a['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['area_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button class="bg-rose-600 text-white px-8 py-3.5 rounded-xl font-bold hover:bg-rose-700 transition flex items-center justify-center gap-2 shadow-lg shadow-rose-200 md:w-auto w-full">
                    Search Now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

        <?php if ($searched_donors !== null): ?>
            <div>
                <h3 class="text-xl font-bold text-slate-800 mb-6">Search Results</h3>
                
                <?php if ($searched_donors->num_rows === 0): ?>
                    <div class="bg-white rounded-2xl p-12 text-center border border-dashed border-slate-300">
                        <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="users" class="text-slate-400 w-8 h-8"></i>
                        </div>
                        <p class="text-slate-500 text-lg">No verified donors found in this area.</p>
                        <p class="text-sm text-slate-400">Try searching in a different location.</p>
                    </div>
                <?php else: ?>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($d = $searched_donors->fetch_assoc()): ?>
                            <div class="glass-panel rounded-2xl p-6 hover-lift group relative overflow-hidden">
                                <div class="absolute top-0 right-0 p-4">
                                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i> Verified
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="bg-rose-100 w-14 h-14 rounded-full flex items-center justify-center text-rose-700 font-bold text-xl shadow-inner">
                                        <?= htmlspecialchars($d['blood_group']) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-lg text-slate-800"><?= htmlspecialchars($d['full_name']) ?></h4>
                                        <p class="text-sm text-slate-500"><?= htmlspecialchars($d['email']) ?></p>
                                    </div>
                                </div>
                                <div class="border-t border-slate-100 pt-4 flex justify-between items-center">
                                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wide">History</span>
                                    <span class="text-sm font-bold text-slate-700"><?= $d['total_donations'] ?> Donations</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div>
            <h3 class="text-xl font-bold text-slate-800 mb-6">Quick Actions</h3>
            <div class="grid md:grid-cols-2 gap-6">
                <a href="view-donors.php" class="glass-panel p-8 rounded-2xl flex items-center gap-6 hover-lift group transition-all">
                    <div class="bg-blue-100 p-5 rounded-2xl text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition duration-300 shadow-sm">
                        <i data-lucide="users" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xl text-slate-800 mb-1">Available Donors</h4>
                        <p class="text-slate-500">Browse the full directory of donors and send requests.</p>
                    </div>
                </a>

                <a href="your-acceptances.php" class="glass-panel p-8 rounded-2xl flex items-center gap-6 hover-lift group transition-all">
                    <div class="bg-emerald-100 p-5 rounded-2xl text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition duration-300 shadow-sm">
                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-xl text-slate-800 mb-1">Your Acceptances</h4>
                        <p class="text-slate-500">View donors who have accepted your blood requests.</p>
                    </div>
                </a>
            </div>
        </div>

    </div>

</main>

<script>
    // Initialize Icons
    lucide.createIcons();
</script>

</body>
</html>