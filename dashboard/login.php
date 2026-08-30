<?php
session_start();
$error = '';

$admin_password = 'Fico@2006'; //PASSWORD ADMIN

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === $admin_password) {
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['login_time'] = time();
      header('Location: index.php');
    exit;
  } else {
        $error = 'Password salah, silakan coba lagi.';
    }
}




?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #0d1117;
      --panel: #101722;
      --border: #263244;
      --border-bright: #34445b;
      --text: #e6edf3;
      --text-muted: #718096;
      --cyan: #22d3ee;
      --cyan-bright: #67e8f9;
      --blue: #3b82f6;
      --mono: "JetBrains Mono", monospace;
      --sans: "Inter", sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: var(--bg);
      color: var(--text);
      font-family: var(--sans);
    }
    .login-card {
      width: 100%;
      max-width: 380px;
      padding: 32px;
      border: 1px solid var(--border);
      border-radius: 14px;
      background: var(--panel);
      box-shadow: 0 18px 55px rgba(0, 0, 0, 0.3);
    }
    .login-header {
      margin-bottom: 24px;
      text-align: center;
    }
    .login-header span {
      display: inline-block;
      padding: 6px 12px;
      margin-bottom: 12px;
      border: 1px solid rgba(34, 211, 238, 0.3);
      border-radius: 7px;
      color: var(--cyan);
      font-family: var(--mono);
      font-size: 0.75rem;
      background: rgba(34, 211, 238, 0.05);
    }
    .login-header h1 {
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: -0.03em;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      color: var(--text-muted);
      font-family: var(--mono);
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    input[type="password"] {
      width: 100%;
      height: 44px;
      padding: 0 14px;
      border: 1px solid var(--border-bright);
      border-radius: 8px;
      background: #080d14;
      color: var(--text);
      font-family: var(--mono);
      font-size: 0.85rem;
      outline: none;
      transition: border-color 0.2s ease;
    }
    input[type="password"]:focus {
      border-color: var(--cyan);
    }
    .button-primary {
      width: 100%;
      height: 44px;
      border: 0;
      border-radius: 8px;
      background: linear-gradient(100deg, var(--cyan-bright), var(--cyan));
      color: #061018;
      font-family: var(--mono);
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s ease, opacity 0.2s ease;
    }
    .button-primary:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }
    .error-msg {
      margin-top: 14px;
      padding: 10px;
      border-radius: 6px;
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #f87171;
      font-family: var(--mono);
      font-size: 0.7rem;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <span>&lt;/&gt; ADMIN ACCESS</span>
      <h1>Dashboard Login</h1>
    </div>
    <form method="POST">
      <div class="form-group">
        <label for="password">Secret Password</label>
        <input type="password" id="password" name="password" placeholder="Enter dashboard password" required autofocus>
      </div>
      <button type="submit" class="button-primary">Authenticate <span>→</span></button>
      <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>