<?php
session_start();
include "../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* ==========================
   FETCH DATA
========================== */
$query = $conn->query("
    SELECT 
        d.id AS donor_id,
        u.full_name,
        u.email,
        d.location,
        a.area_name,
        bg.group_name AS blood_group,
        dv.status AS verification_status
    FROM donors d
    JOIN users u ON d.user_id = u.id
    LEFT JOIN areas a ON d.area_id = a.id
    LEFT JOIN blood_groups bg ON d.blood_group_id = bg.id
    JOIN donor_verifications dv ON dv.donor_id = d.id
    ORDER BY 
        CASE WHEN dv.status = 'pending' THEN 0 ELSE 1 END, 
        d.id DESC
");

// Store data in array to calculate stats before rendering
$donors = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'verified' => 0
];

while ($row = $query->fetch_assoc()) {
    $donors[] = $row;
    $stats['total']++;
    if ($row['verification_status'] === 'pending') {
        $stats['pending']++;
    } else {
        $stats['verified']++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Orion</title>
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
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex">

    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col fixed h-full z-10">
        <div class="p-6 border-b border-slate-800 flex items-center gap-3">
            <div class="bg-rose-600 p-2 rounded-lg">
                <i data-lucide="droplet" class="text-white w-5 h-5 fill-current"></i>
            </div>
            <span class="text-xl font-bold tracking-tight">Orion Admin</span>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="flex items-center gap-3 px-4 py-3 bg-rose-600 rounded-xl text-white font-medium shadow-lg shadow-rose-900/20">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                Donor Verification
            </a>
            <a href="recipients.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition font-medium">
                <i data-lucide="users" class="w-5 h-5"></i>
                Recipients
            </a>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <a href="../../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-rose-400 transition font-medium">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                Logout
            </a>
        </div>
    </aside>

    <div class="md:hidden fixed w-full bg-slate-900 text-white z-50 px-4 py-3 flex justify-between items-center">
        <span class="font-bold flex items-center gap-2">
            <i data-lucide="droplet" class="text-rose-500 fill-current"></i> Orion
        </span>
        <a href="../../auth/logout.php"><i data-lucide="log-out" class="w-5 h-5"></i></a>
    </div>

    <main class="flex-1 md:ml-64 p-6 md:p-10 mt-12 md:mt-0">
        
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard Overview</h1>
                <p class="text-slate-500 mt-1">Manage donor verifications and system status.</p>
            </div>
            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-full border shadow-sm">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-sm font-medium text-slate-600">System Online</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass-panel p-6 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Donors</p>
                    <h2 class="text-3xl font-bold text-slate-800"><?= $stats['total'] ?></h2>
                </div>
                <div class="bg-blue-50 p-3 rounded-xl text-blue-600">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-2xl flex items-center justify-between ring-2 ring-amber-100">
                <div>
                    <p class="text-sm font-medium text-amber-600 mb-1">Pending Approval</p>
                    <h2 class="text-3xl font-bold text-slate-800"><?= $stats['pending'] ?></h2>
                </div>
                <div class="bg-amber-50 p-3 rounded-xl text-amber-600">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-2xl flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-600 mb-1">Verified Donors</p>
                    <h2 class="text-3xl font-bold text-slate-800"><?= $stats['verified'] ?></h2>
                </div>
                <div class="bg-emerald-50 p-3 rounded-xl text-emerald-600">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">Recent Registrations</h2>
            <span class="text-sm text-slate-500 bg-white px-3 py-1 rounded-lg border">
                Sort: Pending First
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php foreach ($donors as $d): ?>
            <?php
                $pending = $d['verification_status'] === 'pending';
                // Dynamic styling based on Blood Group
                $bgClass = match($d['blood_group']) {
                    'A+', 'A-' => 'bg-blue-100 text-blue-700',
                    'B+', 'B-' => 'bg-emerald-100 text-emerald-700',
                    'O+', 'O-' => 'bg-rose-100 text-rose-700',
                    'AB+', 'AB-' => 'bg-purple-100 text-purple-700',
                    default => 'bg-slate-100 text-slate-700'
                };
            ?>
            <div class="glass-panel rounded-2xl p-6 relative group transition hover:-translate-y-1 hover:shadow-lg">
                
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg <?= $bgClass ?>">
                            <?= $d['blood_group'] ?? '?' ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 leading-tight">
                                <?= htmlspecialchars($d['full_name']) ?>
                            </h3>
                            <span class="text-xs text-slate-400">ID: #<?= $d['donor_id'] ?></span>
                        </div>
                    </div>
                    <?php if ($pending): ?>
                        <span class="px-2 py-1 rounded-md bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-wide">
                            Pending
                        </span>
                    <?php else: ?>
                        <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wide flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i> Verified
                        </span>
                    <?php endif; ?>
                </div>

                <div class="space-y-3 border-t border-slate-100 pt-4">
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                        <span class="truncate"><?= htmlspecialchars($d['email']) ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                        <span><?= htmlspecialchars($d['area_name']) ?: 'No Area Set' ?></span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-slate-500">
                        <i data-lucide="navigation" class="w-4 h-4 text-slate-400"></i>
                        <span class="text-xs truncate"><?= htmlspecialchars($d['location']) ?></span>
                    </div>
                </div>

                <div class="mt-6">
                    <?php if ($pending): ?>
                        <a href="approve-donor.php?id=<?= $d['donor_id'] ?>" 
                           onclick="return confirm('Are you sure you want to verify this donor?');"
                           class="flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-emerald-600 text-white py-2.5 rounded-xl font-medium transition-colors">
                            <i data-lucide="shield-check" class="w-4 h-4"></i> Approve Donor
                        </a>
                    <?php else: ?>
                        <button disabled class="flex items-center justify-center gap-2 w-full bg-slate-100 text-slate-400 py-2.5 rounded-xl font-medium cursor-not-allowed">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> Verification Complete
                        </button>
                    <?php endif; ?>
                </div>

            </div>
            <?php endforeach; ?>
            
            <?php if (empty($donors)): ?>
                <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-slate-300">
                    <div class="inline-flex bg-slate-50 p-4 rounded-full mb-3">
                        <i data-lucide="users" class="w-8 h-8 text-slate-400"></i>
                    </div>
                    <p class="text-slate-500 font-medium">No donors found in the database.</p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>