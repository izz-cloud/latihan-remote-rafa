<?php

/**
 * login.php — Halaman Login Sistem Inventaris siswa
 */
session_start();

// Redirect jika sudah login
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        require_once __DIR__ . '/config/db.php';
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND aktif = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['peran']    = $user['peran'];

            // Catat riwayat login
            $pdo->prepare('INSERT INTO riwayat (aksi, user_id) VALUES (?, ?)')
                ->execute(["Login: {$user['nama']} ({$user['peran']})", $user['id']]);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Harap isi semua kolom!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Inventaris Sekolah</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0d0f1a;
            --surface: #13162a;
            --surface2: #1a1f38;
            --border: rgba(79, 138, 255, 0.12);
            --accent: #4f8aff;
            --accent2: #a78bfa;
            --text: #e8eaf6;
            --text-muted: #8b92b3;
            --danger: #f87171;
            --success: #34d399;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Background orbs */
        body::before {
            content: '';
            position: fixed;
            top: -200px;
            left: -200px;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79, 138, 255, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -200px;
            right: -200px;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(167, 139, 250, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 24px;
            z-index: 1;
            animation: fadeUp 0.5s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 36px;
        }

        .login-logo .icon {
            font-size: 52px;
            display: block;
            margin-bottom: 12px;
            filter: drop-shadow(0 0 20px rgba(79, 138, 255, 0.4));
        }

        .login-logo h1 {
            font-family: 'Syne', sans-serif;
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, #4f8aff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .login-logo p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(20px);
        }


        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px 12px 42px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79, 138, 255, 0.12);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        .error-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.25);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--danger);
            margin-bottom: 18px;
            animation: shake 0.4s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-6px);
            }

            75% {
                transform: translateX(6px);
            }
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f8aff, #7c5cfc);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 20px rgba(79, 138, 255, 0.35);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(79, 138, 255, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <!-- Logo -->
        <div class="login-logo">
            <span class="icon">📦</span>
            <h1>INVENTARIS</h1>
            <p>Sistem Inventaris Sekolah — Multi Peran</p>
        </div>

        <!-- Card -->
        <div class="login-card">

            <!-- Error message -->
            <?php if ($error): ?>
                <div class="error-box">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon">👤</span>
                        <input type="text" id="username" name="username"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password"
                            placeholder="Masukkan password"
                            required>
                    </div>
                </div>

                <button type="submit" class="btn-login">Masuk ke Sistem →</button>
            </form>
        </div>
</body>

</html>