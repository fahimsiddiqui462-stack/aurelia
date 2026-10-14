<?php
session_start();
$admin_password = 'aurelia-demo-2026'; // Replace before publishing.
$logged_in = !empty($_SESSION['aurelia_admin']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (hash_equals($admin_password, (string)($_POST['password'] ?? ''))) {
        $_SESSION['aurelia_admin'] = true;
        $logged_in = true;
    } else {
        $error = 'Incorrect password.';
    }
}
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

$leads = [];
if ($logged_in) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'leads.csv';
    if (is_readable($file) && ($handle = fopen($file, 'rb'))) {
        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 5) $leads[] = array_combine($headers, $row);
        }
        fclose($handle);
        $leads = array_reverse($leads);
    }
}
function e($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lead Admin | Aurelia Athletic Club</title><link rel="stylesheet" href="styles.css"><style>body{min-height:100vh}.admin-wrap{width:min(1150px,calc(100% - 32px));margin:auto;padding:65px 0}.admin-card{background:var(--panel);border:1px solid var(--line);padding:32px}.login-card{max-width:440px;margin:70px auto}.admin-card h1{font-size:clamp(2.2rem,5vw,4rem);margin-top:25px}.admin-form{display:grid;gap:13px;margin-top:25px}.admin-form label{font:10px 'DM Mono',monospace;color:var(--acid);text-transform:uppercase}.admin-form input{padding:13px;background:var(--ink);border:1px solid var(--line);color:var(--bone)}.admin-error{color:#ff8d70;font-size:.82rem;margin-top:18px}.admin-header{display:flex;justify-content:space-between;align-items:start;gap:20px;margin-bottom:30px}.admin-header p,.admin-note{color:var(--muted);font-size:.8rem;margin-top:8px}.admin-actions{display:flex;gap:10px;flex-wrap:wrap}.admin-table-wrap{overflow:auto}.admin-table{border-collapse:collapse;width:100%;min-width:740px}.admin-table th,.admin-table td{padding:14px 10px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top;font-size:.8rem}.admin-table th{font:10px 'DM Mono',monospace;color:var(--acid);text-transform:uppercase}.admin-empty{text-align:center;color:var(--muted);padding:35px}.admin-note{margin-top:24px}@media(max-width:650px){.admin-card{padding:22px}.admin-header{display:block}.admin-actions{margin-top:20px}}</style></head><body><main class="admin-wrap"><?php if (!$logged_in): ?><section class="admin-card login-card"><a class="brand" href="index.html">AURELIA <b>/</b> ATHLETIC</a><h1>Lead admin</h1><p class="lede" style="font-size:.9rem;margin-top:10px">Private dashboard for saved consultation and membership enquiries.</p><?php if ($error): ?><p class="admin-error"><?= e($error) ?></p><?php endif; ?><form method="post" class="admin-form"><label for="password">Admin password</label><input id="password" name="password" type="password" required autofocus><button class="btn btn-acid" type="submit" name="login">Sign in</button></form><p class="admin-note">Demo password: <code>aurelia-demo-2026</code>. Replace it in <code>admin.php</code> before publishing.</p></section><?php else: ?><section class="admin-card"><div class="admin-header"><div><span class="eyebrow">Private area</span><h1>Saved leads</h1><p><?= count($leads) ?> saved lead<?= count($leads) === 1 ? '' : 's' ?></p></div><div class="admin-actions"><a class="btn btn-outline" href="index.html">View site</a><a class="btn btn-acid" href="admin.php?logout=1">Log out</a></div></div><?php if (!$leads): ?><div class="admin-empty">No leads have been saved yet.</div><?php else: ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Received</th><th>Name</th><th>Email</th><th>Interest</th><th>Message</th></tr></thead><tbody><?php foreach ($leads as $lead): ?><tr><td><?= e($lead['received']) ?></td><td><?= e($lead['name']) ?></td><td><?= e($lead['email']) ?></td><td><?= e($lead['interest']) ?></td><td><?= e($lead['message']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?><p class="admin-note">Leads are stored in <code>data/leads.csv</code>. Use HTTPS and replace the demo password before launch.</p></section><?php endif; ?></main></body></html>
