<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Nomos — Aplikasi kewangan peribadi yang bersih dan bijak. Jejaki perbelanjaan, urus bajet, dan fahami corak kewangan anda.">
    <meta property="og:type"         content="website">
    <meta property="og:title"        content="Nomos — Jejaki Kewangan Anda">
    <meta property="og:description"  content="Jejaki perbelanjaan, urus bajet, dan fahami corak kewangan anda dengan Nomos — bersih, pantas, dan sepenuhnya untuk anda.">
    <meta property="og:url"          content="{{ url('/') }}">
    <meta property="og:site_name"    content="Nomos">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="Nomos — Jejaki Kewangan Anda">
    <meta name="twitter:description" content="Jejaki perbelanjaan, urus bajet, dan fahami corak kewangan anda.">
    <title>Nomos — Jejaki Kewangan Anda</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Prevent FOUC: apply saved theme before first paint -->
    <script>
        (function() {
            var saved = localStorage.getItem('nomos-theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        /* ─── TOKENS ─────────────────────────────────────────── */
        :root {
            --bg: #ffffff;
            --bg-2: #fafafa;
            --bg-3: #f5f5f5;
            --text: #0d0d0d;
            --text-2: #333333;
            --text-3: #666666;
            --text-4: #888888;
            --brand: #18E299;
            --brand-light: #d4fae8;
            --brand-deep: #0fa76e;
            --border: rgba(0,0,0,0.05);
            --border-md: rgba(0,0,0,0.09);
            --shadow-card: rgba(0,0,0,0.03) 0px 2px 4px;
            --shadow-btn: rgba(0,0,0,0.06) 0px 1px 2px;
            --font: 'Inter', system-ui, -apple-system, sans-serif;
            --mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
            --r-sm:4px; --r-md:8px; --r-lg:16px; --r-xl:24px; --r-full:9999px;
        }

        [data-theme="dark"] {
            --bg: #0d0d0d;
            --bg-2: #141414;
            --bg-3: #1a1a1a;
            --text: #ededed;
            --text-2: #c0c0c0;
            --text-3: #888888;
            --text-4: #555555;
            --brand: #18E299;
            --brand-light: rgba(24,226,153,0.13);
            --brand-deep: #18E299;
            --border: rgba(255,255,255,0.06);
            --border-md: rgba(255,255,255,0.10);
            --shadow-card: rgba(0,0,0,0.35) 0px 2px 8px;
            --shadow-btn: rgba(0,0,0,0.5) 0px 1px 3px;
        }

        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                --bg: #0d0d0d; --bg-2: #141414; --bg-3: #1a1a1a;
                --text: #ededed; --text-2: #c0c0c0; --text-3: #888888; --text-4: #555555;
                --brand: #18E299; --brand-light: rgba(24,226,153,0.13); --brand-deep: #18E299;
                --border: rgba(255,255,255,0.06); --border-md: rgba(255,255,255,0.10);
                --shadow-card: rgba(0,0,0,0.35) 0px 2px 8px; --shadow-btn: rgba(0,0,0,0.5) 0px 1px 3px;
            }
        }

        /* Force light mode on mockup "screenshots" so they always look like the app */
        .mockup-light {
            --bg:#ffffff; --bg-2:#fafafa; --bg-3:#f5f5f5;
            --text:#0d0d0d; --text-2:#333333; --text-3:#666666; --text-4:#888888;
            --brand-light:#d4fae8; --brand-deep:#0fa76e;
            --border:rgba(0,0,0,0.05); --border-md:rgba(0,0,0,0.09);
            color-scheme: light;
        }

        /* ─── BASE ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font); background: var(--bg); color: var(--text); line-height: 1.5; -webkit-font-smoothing: antialiased; transition: background 0.2s, color 0.2s; }

        /* ─── NAV ────────────────────────────────────────────── */
        .nav { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.88); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); transition: background 0.2s; }
        [data-theme="dark"] .nav { background: rgba(13,13,13,0.88); }
        .nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 32px; height: 58px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .logo { font-size: 18px; font-weight: 600; letter-spacing: -0.36px; color: var(--text); text-decoration: none; flex-shrink: 0; }
        .logo .o { color: var(--brand-deep); }
        .nav-links { display: flex; align-items: center; gap: 2px; list-style: none; }
        .nav-links a { font-size: 14px; font-weight: 500; color: var(--text-3); text-decoration: none; padding: 6px 11px; border-radius: var(--r-md); transition: color 0.15s; }
        .nav-links a:hover, .nav-links a.active { color: var(--text); }
        .nav-links a.active { background: var(--bg-3); }
        .nav-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

        /* Theme toggle */
        .theme-btn { width: 32px; height: 32px; border: 1px solid var(--border-md); border-radius: var(--r-full); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-3); transition: color 0.15s, border-color 0.15s, background 0.15s; flex-shrink: 0; }
        .theme-btn:hover { color: var(--text); background: var(--bg-2); }
        .theme-btn svg { width: 15px; height: 15px; }
        .icon-moon { display: block; }
        .icon-sun  { display: none; }
        [data-theme="dark"] .icon-moon { display: none; }
        [data-theme="dark"] .icon-sun  { display: block; }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) .icon-moon { display: none; }
            :root:not([data-theme="light"]) .icon-sun  { display: block; }
        }

        .btn-ghost { font-family: var(--font); font-size: 14px; font-weight: 500; color: var(--text-3); background: none; border: none; padding: 6px 12px; border-radius: var(--r-full); cursor: pointer; text-decoration: none; transition: color 0.15s; }
        .btn-ghost:hover { color: var(--text); }
        .btn-dark { font-family: var(--font); font-size: 14px; font-weight: 500; color: #fff; background: var(--text); border: none; padding: 7px 20px; border-radius: var(--r-full); cursor: pointer; text-decoration: none; box-shadow: var(--shadow-btn); transition: opacity 0.15s; display: inline-flex; align-items: center; gap: 5px; }
        [data-theme="dark"] .btn-dark { color: #0d0d0d; background: #ededed; }
        .btn-dark:hover { opacity: 0.82; }
        .btn-brand { font-family: var(--font); font-size: 15px; font-weight: 500; color: #0d0d0d; background: var(--brand); border: none; padding: 11px 28px; border-radius: var(--r-full); cursor: pointer; text-decoration: none; box-shadow: var(--shadow-btn); transition: opacity 0.15s; display: inline-flex; align-items: center; gap: 7px; }
        .btn-brand:hover { opacity: 0.85; }
        .btn-outline { font-family: var(--font); font-size: 15px; font-weight: 500; color: var(--text); background: var(--bg); border: 1px solid var(--border-md); padding: 10px 24px; border-radius: var(--r-full); cursor: pointer; text-decoration: none; transition: opacity 0.15s; display: inline-flex; align-items: center; gap: 7px; }
        .btn-outline:hover { opacity: 0.7; }

        /* Mobile nav */
        .hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 6px; background: none; border: none; }
        .hamburger span { display: block; width: 20px; height: 1.5px; background: var(--text); border-radius: 2px; }
        .mobile-menu { display: none; position: fixed; top: 58px; left: 0; right: 0; background: var(--bg); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border); padding: 12px 24px 20px; z-index: 99; }
        .mobile-menu.open { display: block; }
        .mobile-menu a { display: block; font-size: 15px; font-weight: 500; color: var(--text-2); text-decoration: none; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .mobile-menu a:last-child { border-bottom: none; }

        /* ─── HERO ───────────────────────────────────────────── */
        .hero { position: relative; padding: 96px 32px 0; text-align: center; overflow: hidden; }
        .hero-glow { position: absolute; top: -100px; left: 50%; transform: translateX(-50%); width: 1000px; height: 640px; background: radial-gradient(ellipse 55% 55% at 50% 42%, rgba(24,226,153,0.13) 0%, rgba(24,226,153,0.04) 55%, transparent 100%); pointer-events: none; }
        [data-theme="dark"] .hero-glow { background: radial-gradient(ellipse 55% 55% at 50% 42%, rgba(24,226,153,0.07) 0%, rgba(24,226,153,0.02) 55%, transparent 100%); }
        .hero-badge { display: inline-flex; align-items: center; gap: 7px; background: var(--brand-light); color: var(--brand-deep); padding: 5px 13px; border-radius: var(--r-full); font-family: var(--mono); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 28px; }
        .badge-dot { width: 6px; height: 6px; background: var(--brand-deep); border-radius: 50%; animation: blink 2.2s ease-in-out infinite; }
        @keyframes blink { 0%,100%{opacity:1}50%{opacity:0.25} }
        .hero h1 { font-size: clamp(38px, 5.5vw, 62px); font-weight: 600; line-height: 1.08; letter-spacing: -1.24px; color: var(--text); max-width: 740px; margin: 0 auto 20px; }
        .hero h1 em { font-style: normal; color: var(--brand-deep); }
        .hero-sub { font-size: 18px; font-weight: 400; line-height: 1.65; color: var(--text-3); max-width: 480px; margin: 0 auto 40px; }
        .hero-actions { display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap; }
        .hero-note { margin-top: 16px; font-size: 13px; color: var(--text-4); }

        /* Hero visual */
        .hero-visual-wrap { position: relative; max-width: 900px; margin: 52px auto 0; padding: 0 32px; }
        .hero-visual-perspective { perspective: 1600px; }
        .hero-visual-inner { transform: rotateX(14deg) scale(0.97); transform-origin: center top; border-radius: var(--r-xl); overflow: hidden; border: 1px solid rgba(0,0,0,0.10); box-shadow: 0 48px 96px rgba(0,0,0,0.11), 0 20px 40px rgba(0,0,0,0.07); transition: transform 0.7s cubic-bezier(0.16,1,0.3,1); background: #fff; }
        [data-theme="dark"] .hero-visual-inner { border-color: rgba(255,255,255,0.08); box-shadow: 0 48px 96px rgba(0,0,0,0.5), 0 20px 40px rgba(0,0,0,0.3); }
        .hero-visual-wrap:hover .hero-visual-inner { transform: rotateX(6deg) scale(0.98); }
        .hero-visual-fade { position: absolute; bottom: -2px; left: 0; right: 0; height: 160px; background: linear-gradient(to bottom, transparent 0%, var(--bg) 100%); pointer-events: none; z-index: 2; }

        /* Mini browser chrome */
        .mini-bar { height: 38px; border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 14px; gap: 7px; }
        .mini-dot { width: 9px; height: 9px; border-radius: 50%; }
        .mini-dot:nth-child(1){background:#ff5f57} .mini-dot:nth-child(2){background:#febc2e} .mini-dot:nth-child(3){background:#28c840}
        .mini-url { flex:1; max-width:200px; margin:0 auto; border:1px solid var(--border); border-radius:var(--r-full); height:22px; display:flex; align-items:center; justify-content:center; font-family:var(--mono); font-size:10px; color:var(--text-4); }
        /* mini body — always light */
        .mini-body { display:grid; grid-template-columns:180px 1fr; height:360px; background:#fff; }
        .mini-sidebar { background:#fafafa; border-right:1px solid rgba(0,0,0,0.05); padding:16px 12px; }
        .mini-logo { font-size:13px; font-weight:600; color:#0d0d0d; letter-spacing:-0.26px; padding:4px 6px; margin-bottom:16px; }
        .mini-logo .o { color:#0fa76e; }
        .mini-nav { display:flex; align-items:center; gap:6px; padding:7px 8px; border-radius:var(--r-md); font-size:11px; font-weight:500; color:#888; margin-bottom:2px; }
        .mini-nav.active { background:#fff; color:#0d0d0d; border:1px solid rgba(0,0,0,0.05); box-shadow:rgba(0,0,0,0.03) 0 2px 4px; }
        .mini-nav-dot { width:13px; height:13px; background:rgba(0,0,0,0.08); border-radius:3px; flex-shrink:0; }
        .mini-nav.active .mini-nav-dot { background:#18E299; }
        .mini-content { padding:20px; overflow:hidden; background:#fff; }
        .mini-title { font-size:16px; font-weight:600; letter-spacing:-0.32px; color:#0d0d0d; margin-bottom:14px; }
        .mini-cards { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:14px; }
        .mini-card { background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:var(--r-lg); padding:12px 13px; }
        .mini-card-lbl { font-size:9px; font-weight:500; color:#888; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:4px; }
        .mini-card-val { font-size:15px; font-weight:600; letter-spacing:-0.3px; color:#0d0d0d; }
        .mini-card-val.inc{color:#0fa76e} .mini-card-val.exp{color:#d45656}
        .mini-bottom { display:grid; grid-template-columns:1.3fr 1fr; gap:8px; }
        .mini-chart { background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:var(--r-lg); padding:12px 13px; }
        .mini-chart-lbl { font-size:9px; font-weight:500; color:#888; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:10px; }
        .mini-bars { display:flex; align-items:flex-end; gap:4px; height:60px; }
        .mini-bar-item { flex:1; border-radius:2px 2px 0 0; background:#d4fae8; }
        .mini-bar-item.hi { background:#18E299; }
        .mini-recent { background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:var(--r-lg); overflow:hidden; }
        .mini-recent-lbl { font-size:9px; font-weight:500; color:#888; text-transform:uppercase; letter-spacing:0.4px; padding:10px 12px 6px; border-bottom:1px solid rgba(0,0,0,0.05); }
        .mini-txn { display:flex; justify-content:space-between; padding:8px 12px; border-bottom:1px solid rgba(0,0,0,0.05); font-size:10px; }
        .mini-txn:last-child{border-bottom:none}
        .mini-txn-name{color:#333;font-weight:500} .mini-txn-cat{color:#888;font-size:9px}
        .mini-txn-amt{font-weight:600;font-size:10px}
        .mini-txn-amt.inc{color:#0fa76e} .mini-txn-amt.exp{color:#d45656}

        /* ─── STATS ──────────────────────────────────────────── */
        .stats { border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding:0 32px; }
        .stats-inner { max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(3,1fr); }
        .stat { padding:32px 24px; text-align:center; border-right:1px solid var(--border); }
        .stat:last-child{border-right:none}
        .stat-num { font-size:38px; font-weight:600; letter-spacing:-0.76px; color:var(--text); line-height:1; margin-bottom:7px; }
        .stat-num span{color:var(--brand-deep)}
        .stat-lbl{font-size:14px;color:var(--text-3)}

        /* ─── SECTIONS ───────────────────────────────────────── */
        .section{padding:88px 32px}
        .section-inner{max-width:1200px;margin:0 auto}
        .sec-label { display:block; font-family:var(--mono); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.8px; color:var(--brand-deep); margin-bottom:12px; }
        .sec-title { font-size:clamp(26px,3.2vw,38px); font-weight:600; letter-spacing:-0.76px; color:var(--text); line-height:1.1; margin-bottom:16px; }
        .sec-desc{font-size:17px;color:var(--text-3);line-height:1.65;max-width:460px}
        .section-header-centered{text-align:center}
        .section-header-centered .sec-desc{margin:0 auto}

        /* ─── FEATURES ───────────────────────────────────────── */
        .features-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:56px; }
        .feat-card { background:var(--bg); border:1px solid var(--border); border-radius:var(--r-lg); padding:28px 26px; box-shadow:var(--shadow-card); transition:border-color 0.2s, box-shadow 0.2s, transform 0.2s; }
        .feat-card:hover { border-color:var(--border-md); box-shadow:rgba(0,0,0,0.07) 0 6px 16px; transform:translateY(-2px); }
        [data-theme="dark"] .feat-card:hover { box-shadow:rgba(0,0,0,0.4) 0 6px 20px; }
        .feat-icon { width:42px; height:42px; background:var(--brand-light); border-radius:var(--r-md); display:flex; align-items:center; justify-content:center; margin-bottom:20px; }
        .feat-icon svg{width:20px;height:20px;stroke:var(--brand-deep)}
        .feat-title{font-size:16px;font-weight:600;letter-spacing:-0.16px;color:var(--text);margin-bottom:8px}
        .feat-desc{font-size:14px;color:var(--text-3);line-height:1.65}

        /* ─── DASHBOARD PREVIEW ──────────────────────────────── */
        .preview-section { padding:88px 32px; background:var(--bg-2); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
        .preview-header { max-width:1200px; margin:0 auto 52px; display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:end; }
        .mockup-wrap { max-width:960px; margin:0 auto; background:#ffffff; border:1px solid rgba(0,0,0,0.09); border-radius:var(--r-xl); box-shadow:rgba(0,0,0,0.08) 0 16px 48px, rgba(0,0,0,0.04) 0 4px 8px; overflow:hidden; }
        [data-theme="dark"] .mockup-wrap { border-color:rgba(255,255,255,0.08); box-shadow:rgba(0,0,0,0.5) 0 16px 64px, rgba(0,0,0,0.3) 0 4px 16px; }
        .mockup-bar { height:44px; background:#fafafa; border-bottom:1px solid rgba(0,0,0,0.05); display:flex; align-items:center; padding:0 16px; gap:8px; }
        .m-dot{width:10px;height:10px;border-radius:50%}
        .m-dot:nth-child(1){background:#ff5f57} .m-dot:nth-child(2){background:#febc2e} .m-dot:nth-child(3){background:#28c840}
        .m-url { flex:1; max-width:220px; margin:0 auto; background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:var(--r-full); height:24px; display:flex; align-items:center; justify-content:center; font-family:var(--mono); font-size:10px; color:#888; letter-spacing:0.2px; }
        .mockup-body { display:grid; grid-template-columns:200px 1fr; min-height:440px; background:#fff; }
        .m-sidebar { background:#fafafa; border-right:1px solid rgba(0,0,0,0.05); padding:20px 14px; }
        .m-logo{font-size:15px;font-weight:600;letter-spacing:-0.3px;color:#0d0d0d;padding:6px 8px;margin-bottom:20px}
        .m-logo .o{color:#0fa76e}
        .m-nav-item { display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:var(--r-md); font-size:12px; font-weight:500; color:#888; margin-bottom:2px; cursor:default; }
        .m-nav-item.active { background:#fff; color:#0d0d0d; border:1px solid rgba(0,0,0,0.05); box-shadow:rgba(0,0,0,0.03) 0 2px 4px; }
        .m-nav-icon{width:16px;height:16px;background:rgba(0,0,0,0.08);border-radius:4px;flex-shrink:0}
        .m-nav-item.active .m-nav-icon{background:#18E299}
        .m-content{padding:24px;overflow:hidden;background:#fff}
        .m-page-title{font-size:18px;font-weight:600;letter-spacing:-0.36px;color:#0d0d0d;margin-bottom:20px}
        .m-cards{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:16px}
        .m-card{background:#fff;border:1px solid rgba(0,0,0,0.05);border-radius:var(--r-lg);padding:14px 16px}
        .m-card-lbl{font-size:10px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px}
        .m-card-val{font-size:20px;font-weight:600;letter-spacing:-0.4px;color:#0d0d0d}
        .m-card-val.inc{color:#0fa76e} .m-card-val.exp{color:#d45656}
        .m-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .m-chart{background:#fff;border:1px solid rgba(0,0,0,0.05);border-radius:var(--r-lg);padding:14px 16px}
        .m-chart-lbl{font-size:10px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px}
        /* Animated chart bars */
        .chart-bars{display:flex;align-items:flex-end;gap:5px;height:72px}
        .bar{flex:1;border-radius:3px 3px 0 0;background:#d4fae8;transform:scaleY(0);transform-origin:bottom}
        .bar.hi{background:#18E299}
        .bars-play .bar{animation:growBar 0.55s cubic-bezier(0.16,1,0.3,1) forwards}
        .bars-play .bar:nth-child(1){animation-delay:.00s} .bars-play .bar:nth-child(2){animation-delay:.07s}
        .bars-play .bar:nth-child(3){animation-delay:.14s} .bars-play .bar:nth-child(4){animation-delay:.21s}
        .bars-play .bar:nth-child(5){animation-delay:.28s} .bars-play .bar:nth-child(6){animation-delay:.35s}
        .bars-play .bar:nth-child(7){animation-delay:.42s}
        @keyframes growBar{from{transform:scaleY(0)}to{transform:scaleY(1)}}
        .m-txn-list{background:#fff;border:1px solid rgba(0,0,0,0.05);border-radius:var(--r-lg);overflow:hidden}
        .m-txn-lbl{font-size:10px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px;padding:12px 14px 8px;border-bottom:1px solid rgba(0,0,0,0.05)}
        .m-txn-item{display:flex;align-items:center;justify-content:space-between;padding:9px 14px;border-bottom:1px solid rgba(0,0,0,0.05);font-size:11px}
        .m-txn-item:last-child{border-bottom:none}
        .m-txn-name{color:#333;font-weight:500} .m-txn-cat{color:#888;font-size:10px}
        .m-txn-amt{font-weight:600} .m-txn-amt.inc{color:#0fa76e} .m-txn-amt.exp{color:#d45656}

        /* ─── BUDGET ─────────────────────────────────────────── */
        .budget-section{padding:88px 32px;border-top:1px solid var(--border)}
        .budget-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}
        .budget-checks{display:flex;flex-direction:column;gap:12px;margin-bottom:36px}
        .budget-check{display:flex;align-items:flex-start;gap:10px;font-size:15px;color:var(--text-2)}
        .check-icon{width:20px;height:20px;background:var(--brand-light);border-radius:var(--r-full);flex-shrink:0;display:flex;align-items:center;justify-content:center;margin-top:2px}
        .check-icon svg{width:10px;height:10px;stroke:var(--brand-deep);stroke-width:2.5}
        /* Budget card — always light (product screenshot) */
        .budget-card { background:#fff; border:1px solid rgba(0,0,0,0.07); border-radius:var(--r-xl); padding:28px; box-shadow:rgba(0,0,0,0.05) 0 8px 24px; }
        [data-theme="dark"] .budget-card { box-shadow:rgba(0,0,0,0.5) 0 8px 32px; border-color:rgba(0,0,0,0.12); }
        .budget-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
        .budget-card-title{font-size:14px;font-weight:600;color:#0d0d0d}
        .budget-month{font-family:var(--mono);font-size:11px;font-weight:500;color:#888;text-transform:uppercase;letter-spacing:0.5px}
        .budget-item{margin-bottom:18px}
        .budget-item:last-child{margin-bottom:0}
        .budget-item-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:7px}
        .budget-item-name{font-size:13px;font-weight:500;color:#333;display:flex;align-items:center;gap:6px}
        .budget-cat-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .budget-item-amounts{font-size:12px;color:#888}
        .budget-item-amounts strong{color:#333;font-weight:600}
        .budget-track{height:6px;background:#f0f0f0;border-radius:var(--r-full);overflow:hidden}
        .budget-fill{height:100%;border-radius:var(--r-full);transform:scaleX(0);transform-origin:left}
        .budget-animated .budget-fill{animation:fillBar 0.7s cubic-bezier(0.16,1,0.3,1) forwards}
        .budget-item:nth-child(2) .budget-fill{animation-delay:.10s}
        .budget-item:nth-child(3) .budget-fill{animation-delay:.20s}
        .budget-item:nth-child(4) .budget-fill{animation-delay:.30s}
        .budget-item:nth-child(5) .budget-fill{animation-delay:.40s}
        .budget-item:nth-child(6) .budget-fill{animation-delay:.50s}
        @keyframes fillBar{from{transform:scaleX(0)}to{transform:scaleX(1)}}
        .budget-summary{margin-top:24px;padding-top:20px;border-top:1px solid rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:space-between}
        .budget-summary-lbl{font-size:13px;color:#888}
        .budget-summary-val{font-size:16px;font-weight:600;letter-spacing:-0.32px;color:#0d0d0d}

        /* ─── CATEGORIES ─────────────────────────────────────── */
        .categories-section{padding:88px 32px;background:var(--bg-2);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
        .cat-grid{display:flex;flex-wrap:wrap;gap:8px;margin-top:40px;justify-content:center}
        .cat-pill { display:inline-flex; align-items:center; gap:7px; padding:8px 14px; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-full); font-size:13px; font-weight:500; color:var(--text-2); box-shadow:var(--shadow-card); cursor:default; opacity:0; transform:translateY(6px); }
        .cat-pill.pill-in { animation: pillReveal 0.4s ease forwards; }
        .cat-pill span{font-size:16px;line-height:1}
        .cat-pill:hover{border-color:var(--border-md)}
        .cat-pill.highlight { background:var(--brand-light); border-color:rgba(15,167,110,0.2); color:var(--brand-deep); }
        @keyframes pillReveal { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
        .cat-note{text-align:center;margin-top:20px;font-size:13px;color:var(--text-4)}

        /* ─── PHONE MOCKUP ───────────────────────────────────── */
        .mobile-section { padding:88px 32px; border-top:1px solid var(--border); }
        .mobile-inner { max-width:1200px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center; }
        .phone-wrap { display:flex; justify-content:center; }
        .phone { width:220px; background:#1a1a1a; border-radius:40px; padding:12px; position:relative; box-shadow: 0 0 0 1px rgba(255,255,255,0.08), 0 32px 80px rgba(0,0,0,0.35), 0 8px 16px rgba(0,0,0,0.2); }
        [data-theme="dark"] .phone { box-shadow: 0 0 0 1px rgba(255,255,255,0.12), 0 32px 80px rgba(0,0,0,0.6), 0 8px 16px rgba(0,0,0,0.4); }
        .phone-notch { position:absolute; top:12px; left:50%; transform:translateX(-50%); width:72px; height:22px; background:#1a1a1a; border-radius:0 0 16px 16px; z-index:5; }
        .phone-screen { background:#fff; border-radius:30px; overflow:hidden; min-height:440px; position:relative; }
        /* Phone screen contents — always light */
        .phone-status { height:36px; background:#fff; display:flex; align-items:flex-end; justify-content:space-between; padding:0 16px 6px; font-size:10px; font-weight:600; color:#0d0d0d; }
        .phone-status-icons { display:flex; gap:4px; align-items:center; }
        .phone-signal { display:flex; gap:1px; align-items:flex-end; }
        .phone-signal span { width:3px; background:#0d0d0d; border-radius:1px; }
        .phone-signal span:nth-child(1){height:4px} .phone-signal span:nth-child(2){height:6px} .phone-signal span:nth-child(3){height:9px} .phone-signal span:nth-child(4){height:12px}
        .phone-header { height:48px; background:#fff; border-bottom:1px solid rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:space-between; padding:0 16px; }
        .phone-header-title { font-size:15px; font-weight:600; color:#0d0d0d; letter-spacing:-0.3px; }
        .phone-header-icon { width:28px; height:28px; background:#fafafa; border:1px solid rgba(0,0,0,0.07); border-radius:var(--r-full); display:flex; align-items:center; justify-content:center; }
        .phone-header-icon svg { width:14px; height:14px; stroke:#666; }
        .phone-body { padding:14px; }
        .phone-summary-card { background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:var(--r-lg); padding:14px; margin-bottom:10px; }
        .phone-summary-card-lbl { font-size:9px; font-weight:500; color:#888; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px; }
        .phone-summary-card-val { font-size:22px; font-weight:600; letter-spacing:-0.44px; color:#0d0d0d; }
        .phone-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:10px; }
        .phone-mini-card { background:#fff; border:1px solid rgba(0,0,0,0.05); border-radius:var(--r-lg); padding:10px 12px; }
        .phone-mini-lbl { font-size:9px; color:#888; font-weight:500; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:3px; }
        .phone-mini-val { font-size:14px; font-weight:600; letter-spacing:-0.28px; }
        .phone-mini-val.inc{color:#0fa76e} .phone-mini-val.exp{color:#d45656}
        .phone-txn-section { }
        .phone-txn-lbl { font-size:9px; font-weight:500; color:#888; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; }
        .phone-txn { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(0,0,0,0.04); }
        .phone-txn:last-child { border-bottom:none; }
        .phone-txn-name { font-size:11px; font-weight:500; color:#333; }
        .phone-txn-cat { font-size:9px; color:#888; }
        .phone-txn-amt { font-size:11px; font-weight:600; }
        .phone-txn-amt.inc{color:#0fa76e} .phone-txn-amt.exp{color:#d45656}
        /* FAB button */
        .phone-fab { position:absolute; bottom:16px; right:14px; width:44px; height:44px; background:#18E299; border-radius:var(--r-full); display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(24,226,153,0.4); }
        .phone-fab svg { width:20px; height:20px; stroke:#0d0d0d; stroke-width:2; }

        /* ─── STEPS ──────────────────────────────────────────── */
        .steps-section{padding:88px 32px}
        .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:48px;margin-top:60px;position:relative}
        .steps::before{content:'';position:absolute;top:22px;left:calc(16.66% + 20px);right:calc(16.66% + 20px);height:1px;background:var(--border-md);pointer-events:none}
        .step-num{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:var(--r-full);background:var(--brand-light);font-family:var(--mono);font-size:13px;font-weight:600;color:var(--brand-deep);margin-bottom:20px;border:1px solid rgba(15,167,110,0.15)}
        .step-title{font-size:18px;font-weight:600;letter-spacing:-0.36px;color:var(--text);margin-bottom:10px}
        .step-desc{font-size:14px;color:var(--text-3);line-height:1.7}

        /* ─── QUOTE ──────────────────────────────────────────── */
        .quote-strip{border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:64px 32px;text-align:center;background:var(--bg-2)}
        blockquote{font-size:22px;font-weight:500;letter-spacing:-0.44px;color:var(--text);max-width:600px;margin:0 auto 20px;line-height:1.4}
        blockquote::before{content:'\201C';color:var(--brand);margin-right:2px}
        blockquote::after{content:'\201D';color:var(--brand);margin-left:2px}
        .quote-attr{font-size:13px;color:var(--text-4);font-family:var(--mono);text-transform:uppercase;letter-spacing:0.6px}

        /* ─── CTA ────────────────────────────────────────────── */
        .cta-section { padding:104px 32px; background:#111111; text-align:center; position:relative; overflow:hidden; }
        [data-theme="dark"] .cta-section { background:#1a1a1a; }
        .cta-glow{position:absolute;inset:0;background:radial-gradient(ellipse 70% 70% at 50% 50%, rgba(24,226,153,0.10) 0%, transparent 100%);pointer-events:none}
        .cta-section h2{font-size:clamp(30px,4.2vw,50px);font-weight:600;letter-spacing:-1px;color:#fff;max-width:540px;margin:0 auto 16px;line-height:1.1;position:relative}
        .cta-sub{font-size:16px;color:rgba(255,255,255,0.45);margin-bottom:44px;position:relative}
        .cta-btns{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;position:relative}
        .btn-white{font-family:var(--font);font-size:15px;font-weight:500;color:#0d0d0d;background:#fff;border:none;padding:11px 28px;border-radius:var(--r-full);cursor:pointer;text-decoration:none;box-shadow:var(--shadow-btn);transition:opacity 0.15s;display:inline-flex;align-items:center;gap:7px}
        .btn-white:hover{opacity:0.88}
        .btn-ghost-white{font-family:var(--font);font-size:15px;font-weight:500;color:rgba(255,255,255,0.7);background:transparent;border:1px solid rgba(255,255,255,0.14);padding:10px 24px;border-radius:var(--r-full);cursor:pointer;text-decoration:none;transition:border-color 0.15s,color 0.15s;display:inline-flex;align-items:center;gap:7px}
        .btn-ghost-white:hover{border-color:rgba(255,255,255,0.3);color:#fff}

        /* ─── FOOTER ─────────────────────────────────────────── */
        footer{padding:40px 32px;border-top:1px solid var(--border)}
        .footer-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .footer-logo{font-size:16px;font-weight:600;letter-spacing:-0.32px;color:var(--text);text-decoration:none}
        .footer-logo .o{color:var(--brand-deep)}
        .footer-copy{font-size:13px;color:var(--text-4)}
        .footer-links{display:flex;gap:20px}
        .footer-links a{font-size:13px;color:var(--text-4);text-decoration:none;transition:color 0.15s}
        .footer-links a:hover{color:var(--text)}

        /* ─── REVEAL ANIMATIONS ──────────────────────────────── */
        .reveal{opacity:0;transform:translateY(18px);transition:opacity 0.55s ease,transform 0.55s ease}
        .reveal.in{opacity:1;transform:translateY(0)}

        /* ─── RESPONSIVE ─────────────────────────────────────── */
        @media(max-width:1024px){
            .features-grid{grid-template-columns:repeat(2,1fr)}
            .preview-header{grid-template-columns:1fr;gap:16px}
            .budget-inner{gap:48px}
            .mobile-inner{gap:48px}
            .steps::before{left:calc(16.66% + 14px);right:calc(16.66% + 14px)}
        }
        @media(max-width:900px){
            .budget-inner,.mobile-inner{grid-template-columns:1fr}
            .phone-wrap{order:-1}
            .mini-body{grid-template-columns:1fr;height:auto}
            .mini-sidebar{display:none}
            .mini-cards{grid-template-columns:repeat(2,1fr)}
            .mini-bottom{grid-template-columns:1fr}
        }
        @media(max-width:768px){
            .nav-links,.nav-right .btn-ghost{display:none}
            .hamburger{display:flex}
            .hero{padding:72px 24px 0}
            .hero-visual-wrap{padding:0 16px;margin-top:40px}
            .hero-visual-inner{transform:none}
            .hero-visual-wrap:hover .hero-visual-inner{transform:none}
            .stats-inner{grid-template-columns:1fr}
            .stat{border-right:none;border-bottom:1px solid var(--border);padding:24px}
            .stat:last-child{border-bottom:none}
            .section,.steps-section,.budget-section,.categories-section,.preview-section,.mobile-section{padding:64px 24px}
            .features-grid{grid-template-columns:1fr}
            .steps{grid-template-columns:1fr;gap:32px}
            .steps::before{display:none}
            .mockup-body{grid-template-columns:1fr}
            .m-sidebar{display:none}
            .m-cards{grid-template-columns:1fr}
            .m-row{grid-template-columns:1fr}
            .cta-section{padding:72px 24px}
            .footer-inner{flex-direction:column;text-align:center}
            .footer-links{justify-content:center}
        }
        @media(max-width:480px){
            .hero-actions,.cta-btns{flex-direction:column;align-items:stretch}
            .hero-actions a,.cta-btns a{text-align:center;justify-content:center}
            .mini-cards{grid-template-columns:1fr}
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
    <div class="nav-inner">
        <a href="{{ route('home') }}" class="logo">nom<span class="o">o</span>s</a>
        <ul class="nav-links">
            <li><a href="#features">Ciri-Ciri</a></li>
            <li><a href="#preview">Dashboard</a></li>
            <li><a href="#bajet">Bajet</a></li>
            <li><a href="#cara-kerja">Cara Kerja</a></li>
        </ul>
        <div class="nav-right">
            <!-- Dark mode toggle -->
            <button class="theme-btn" id="themeBtn" onclick="toggleTheme()" aria-label="Toggle dark mode">
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke-linecap="round"/></svg>
            </button>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-dark">Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Log Masuk</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-dark">Mulakan →</a>
                @endif
            @endauth
        </div>
        <button class="hamburger" onclick="toggleNav()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<div class="mobile-menu" id="mobileNav">
    <a href="#features">Ciri-Ciri</a>
    <a href="#preview">Dashboard</a>
    <a href="#bajet">Bajet</a>
    <a href="#cara-kerja">Cara Kerja</a>
    @auth
        <a href="{{ route('dashboard') }}">Dashboard →</a>
    @else
        <a href="{{ route('login') }}">Log Masuk</a>
        @if (Route::has('register'))<a href="{{ route('register') }}">Daftar Sekarang</a>@endif
    @endauth
</div>


<!-- HERO -->
<section class="hero" id="hero">
    <div class="hero-glow"></div>
    <div class="hero-badge reveal"><span class="badge-dot"></span>Kewangan Peribadi</div>
    <h1 class="reveal" style="transition-delay:.07s">Kawalan Penuh Ke Atas<br>Kewangan <em>Anda</em></h1>
    <p class="hero-sub reveal" style="transition-delay:.14s">Jejaki perbelanjaan, urus bajet, dan fahami corak kewangan anda dengan Nomos — bersih, pantas, dan sepenuhnya untuk anda.</p>
    <div class="hero-actions reveal" style="transition-delay:.21s">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-brand">Ke Dashboard →</a>
        @else
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-brand">Mulakan Percuma →</a>
            @endif
            <a href="#preview" class="btn-outline">Lihat Dashboard</a>
        @endauth
    </div>
    <p class="hero-note reveal" style="transition-delay:.28s">Percuma sepenuhnya · Tiada had transaksi · Data peribadi anda</p>

    <!-- Tilted product preview -->
    <div class="hero-visual-wrap reveal" style="transition-delay:.38s">
        <div class="hero-visual-perspective">
            <div class="hero-visual-inner mockup-light">
                <div class="mini-bar">
                    <div class="mini-dot"></div><div class="mini-dot"></div><div class="mini-dot"></div>
                    <div class="mini-url">nomos.test/dashboard</div>
                </div>
                <div class="mini-body">
                    <div class="mini-sidebar">
                        <div class="mini-logo">nom<span class="o">o</span>s</div>
                        <div class="mini-nav active"><div class="mini-nav-dot"></div>Dashboard</div>
                        <div class="mini-nav"><div class="mini-nav-dot"></div>Transaksi</div>
                        <div class="mini-nav"><div class="mini-nav-dot"></div>Bajet</div>
                        <div class="mini-nav"><div class="mini-nav-dot"></div>Laporan</div>
                        <div class="mini-nav"><div class="mini-nav-dot"></div>Insights</div>
                        <div class="mini-nav"><div class="mini-nav-dot"></div>Berulang</div>
                        <div class="mini-nav"><div class="mini-nav-dot"></div>Kategori</div>
                    </div>
                    <div class="mini-content">
                        <div class="mini-title">Dashboard</div>
                        <div class="mini-cards">
                            <div class="mini-card"><div class="mini-card-lbl">Baki Semasa</div><div class="mini-card-val">RM 4,280</div></div>
                            <div class="mini-card"><div class="mini-card-lbl">Perbelanjaan</div><div class="mini-card-val exp">RM 1,842</div></div>
                            <div class="mini-card"><div class="mini-card-lbl">Pendapatan</div><div class="mini-card-val inc">RM 6,500</div></div>
                            <div class="mini-card"><div class="mini-card-lbl">Simpanan</div><div class="mini-card-val inc">RM 4,658</div></div>
                        </div>
                        <div class="mini-bottom">
                            <div class="mini-chart">
                                <div class="mini-chart-lbl">Perbelanjaan Mingguan</div>
                                <div class="mini-bars">
                                    <div class="mini-bar-item" style="height:35%"></div>
                                    <div class="mini-bar-item" style="height:58%"></div>
                                    <div class="mini-bar-item" style="height:44%"></div>
                                    <div class="mini-bar-item" style="height:72%"></div>
                                    <div class="mini-bar-item hi" style="height:90%"></div>
                                    <div class="mini-bar-item" style="height:64%"></div>
                                    <div class="mini-bar-item" style="height:40%"></div>
                                </div>
                            </div>
                            <div class="mini-recent">
                                <div class="mini-recent-lbl">Transaksi Terkini</div>
                                <div class="mini-txn"><div><div class="mini-txn-name">Gaji Bulanan</div><div class="mini-txn-cat">Pendapatan</div></div><div class="mini-txn-amt inc">+RM 6,500</div></div>
                                <div class="mini-txn"><div><div class="mini-txn-name">Tesco Online</div><div class="mini-txn-cat">Makanan</div></div><div class="mini-txn-amt exp">-RM 124</div></div>
                                <div class="mini-txn"><div><div class="mini-txn-name">TNB Bil</div><div class="mini-txn-cat">Utiliti</div></div><div class="mini-txn-amt exp">-RM 88</div></div>
                                <div class="mini-txn"><div><div class="mini-txn-name">Netflix</div><div class="mini-txn-cat">Hiburan</div></div><div class="mini-txn-amt exp">-RM 55</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-visual-fade"></div>
    </div>
</section>


<!-- STATS -->
<div class="stats">
    <div class="stats-inner">
        <div class="stat reveal">
            <div class="stat-num">21<span>+</span></div>
            <div class="stat-lbl">Kategori lalai tersedia</div>
        </div>
        <div class="stat reveal" style="transition-delay:.08s">
            <div class="stat-num">RM <span>0</span></div>
            <div class="stat-lbl">Kos untuk bermula</div>
        </div>
        <div class="stat reveal" style="transition-delay:.16s">
            <div class="stat-num">100<span>%</span></div>
            <div class="stat-lbl">Privasi data anda terjaga</div>
        </div>
    </div>
</div>


<!-- FEATURES -->
<section class="section" id="features">
    <div class="section-inner">
        <div class="section-header-centered reveal">
            <span class="sec-label">Ciri-Ciri</span>
            <h2 class="sec-title">Semua yang anda perlukan<br>untuk mengurus wang</h2>
            <p class="sec-desc">Dari rekod harian hingga analitik bulanan — Nomos berikan gambaran lengkap kewangan anda.</p>
        </div>
        <div class="features-grid">
            <div class="feat-card reveal" style="transition-delay:.05s">
                <div class="feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6" stroke-linecap="round"/></svg></div>
                <div class="feat-title">Jejak Transaksi</div>
                <div class="feat-desc">Catat setiap pendapatan dan perbelanjaan. Tambah nota, kategori, dan lampiran untuk rekod lengkap.</div>
            </div>
            <div class="feat-card reveal" style="transition-delay:.10s">
                <div class="feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                <div class="feat-title">Analitik & Insights</div>
                <div class="feat-desc">Carta perbelanjaan mingguan dan bulanan. Kenal pasti corak dan kawasan pemborosan.</div>
            </div>
            <div class="feat-card reveal" style="transition-delay:.15s">
                <div class="feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"/></svg></div>
                <div class="feat-title">Urus Bajet</div>
                <div class="feat-desc">Tetapkan had perbelanjaan mengikut kategori. Pantau penggunaan bajet secara real-time.</div>
            </div>
            <div class="feat-card reveal" style="transition-delay:.20s">
                <div class="feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                <div class="feat-title">Transaksi Berulang</div>
                <div class="feat-desc">Tetapkan bil bulanan dan pendapatan tetap. Nomos jejak semua bayaran berulang tanpa kerja manual.</div>
            </div>
            <div class="feat-card reveal" style="transition-delay:.25s">
                <div class="feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                <div class="feat-title">Kategori Tersuai</div>
                <div class="feat-desc">21 kategori lalai tersedia, atau buat sendiri. Susun transaksi mengikut cara hidup anda.</div>
            </div>
            <div class="feat-card reveal" style="transition-delay:.30s">
                <div class="feat-icon"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.6"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                <div class="feat-title">Laporan Kewangan</div>
                <div class="feat-desc">Jana laporan perbelanjaan mengikut tempoh. Semak ringkasan bulanan dan tahunan dalam satu klik.</div>
            </div>
        </div>
    </div>
</section>


<!-- DASHBOARD PREVIEW -->
<div class="preview-section" id="preview">
    <div class="preview-header reveal">
        <div>
            <span class="sec-label">Dashboard</span>
            <h2 class="sec-title">Paparan bersih.<br>Data bermakna.</h2>
        </div>
        <p class="sec-desc">Dashboard ringkas yang berikan gambaran kewangan penuh dalam satu pandangan — baki, perbelanjaan, pendapatan, dan trend.</p>
    </div>
    <div class="mockup-wrap reveal" style="transition-delay:.12s">
        <div class="mockup-bar">
            <div class="m-dot"></div><div class="m-dot"></div><div class="m-dot"></div>
            <div class="m-url">nomos.test/dashboard</div>
        </div>
        <div class="mockup-body">
            <div class="m-sidebar">
                <div class="m-logo">nom<span class="o">o</span>s</div>
                <div class="m-nav-item active"><div class="m-nav-icon"></div>Dashboard</div>
                <div class="m-nav-item"><div class="m-nav-icon"></div>Transaksi</div>
                <div class="m-nav-item"><div class="m-nav-icon"></div>Bajet</div>
                <div class="m-nav-item"><div class="m-nav-icon"></div>Laporan</div>
                <div class="m-nav-item"><div class="m-nav-icon"></div>Insights</div>
                <div class="m-nav-item"><div class="m-nav-icon"></div>Berulang</div>
                <div class="m-nav-item"><div class="m-nav-icon"></div>Kategori</div>
            </div>
            <div class="m-content">
                <div class="m-page-title">Dashboard</div>
                <div class="m-cards">
                    <div class="m-card"><div class="m-card-lbl">Baki Semasa</div><div class="m-card-val">RM 4,280</div></div>
                    <div class="m-card"><div class="m-card-lbl">Perbelanjaan Bulan Ini</div><div class="m-card-val exp">RM 1,842</div></div>
                    <div class="m-card"><div class="m-card-lbl">Pendapatan Bulan Ini</div><div class="m-card-val inc">RM 6,500</div></div>
                    <div class="m-card"><div class="m-card-lbl">Simpanan Bulan Ini</div><div class="m-card-val inc">RM 4,658</div></div>
                </div>
                <div class="m-row">
                    <div class="m-chart">
                        <div class="m-chart-lbl">Perbelanjaan Mingguan</div>
                        <div class="chart-bars" id="mainChart">
                            <div class="bar"    style="height:35%"></div>
                            <div class="bar"    style="height:58%"></div>
                            <div class="bar"    style="height:44%"></div>
                            <div class="bar"    style="height:72%"></div>
                            <div class="bar hi" style="height:92%"></div>
                            <div class="bar"    style="height:64%"></div>
                            <div class="bar"    style="height:42%"></div>
                        </div>
                    </div>
                    <div class="m-txn-list">
                        <div class="m-txn-lbl">Transaksi Terkini</div>
                        <div class="m-txn-item"><div><div class="m-txn-name">Gaji Bulanan</div><div class="m-txn-cat">Pendapatan</div></div><div class="m-txn-amt inc">+RM 6,500</div></div>
                        <div class="m-txn-item"><div><div class="m-txn-name">Tesco Online</div><div class="m-txn-cat">Makanan & Minuman</div></div><div class="m-txn-amt exp">-RM 124</div></div>
                        <div class="m-txn-item"><div><div class="m-txn-name">TNB Bil Letrik</div><div class="m-txn-cat">Utiliti</div></div><div class="m-txn-amt exp">-RM 88</div></div>
                        <div class="m-txn-item"><div><div class="m-txn-name">Netflix</div><div class="m-txn-cat">Hiburan</div></div><div class="m-txn-amt exp">-RM 55</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- BUDGET -->
<section class="budget-section" id="bajet">
    <div class="budget-inner section-inner">
        <div class="budget-text reveal">
            <span class="sec-label">Bajet</span>
            <h2 class="sec-title">Tetapkan had.<br>Pantau perbelanjaan.</h2>
            <p class="sec-desc" style="margin-bottom:32px">Tetapkan bajet untuk setiap kategori dan tengok dalam masa nyata berapa banyak yang masih tinggal — sebelum terlebih belanja.</p>
            <div class="budget-checks">
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Bajet boleh ditetapkan untuk mana-mana kategori</span></div>
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Bar kemajuan tunjuk penggunaan berbanding had</span></div>
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Indikator merah apabila melebihi had bajet</span></div>
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Reset automatik setiap bulan baru</span></div>
            </div>
            @auth
                <a href="{{ route('budget') }}" class="btn-brand" style="font-size:14px;padding:9px 22px">Urus Bajet Saya →</a>
            @else
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-brand" style="font-size:14px;padding:9px 22px">Cuba Percuma →</a>
                @endif
            @endauth
        </div>
        <div class="reveal" style="transition-delay:.15s">
            <div class="budget-card mockup-light" id="budgetCard">
                <div class="budget-card-header">
                    <div class="budget-card-title">Bajet Mei 2026</div>
                    <div class="budget-month">RM 2,000 had</div>
                </div>
                <div class="budget-item">
                    <div class="budget-item-header">
                        <div class="budget-item-name"><div class="budget-cat-dot" style="background:#18E299"></div>Makanan & Minuman</div>
                        <div class="budget-item-amounts"><strong>RM 480</strong> / RM 600</div>
                    </div>
                    <div class="budget-track"><div class="budget-fill" style="width:80%;background:#18E299"></div></div>
                </div>
                <div class="budget-item">
                    <div class="budget-item-header">
                        <div class="budget-item-name"><div class="budget-cat-dot" style="background:#3772cf"></div>Pengangkutan</div>
                        <div class="budget-item-amounts"><strong>RM 140</strong> / RM 300</div>
                    </div>
                    <div class="budget-track"><div class="budget-fill" style="width:46.7%;background:#3772cf"></div></div>
                </div>
                <div class="budget-item">
                    <div class="budget-item-header">
                        <div class="budget-item-name"><div class="budget-cat-dot" style="background:#d45656"></div>Hiburan</div>
                        <div class="budget-item-amounts"><strong>RM 210</strong> / RM 200 <span style="color:#d45656;font-size:11px;font-weight:600;margin-left:4px">+RM 10</span></div>
                    </div>
                    <div class="budget-track"><div class="budget-fill" style="width:100%;background:#d45656"></div></div>
                </div>
                <div class="budget-item">
                    <div class="budget-item-header">
                        <div class="budget-item-name"><div class="budget-cat-dot" style="background:#c37d0d"></div>Membeli-belah</div>
                        <div class="budget-item-amounts"><strong>RM 195</strong> / RM 400</div>
                    </div>
                    <div class="budget-track"><div class="budget-fill" style="width:48.75%;background:#c37d0d"></div></div>
                </div>
                <div class="budget-item">
                    <div class="budget-item-header">
                        <div class="budget-item-name"><div class="budget-cat-dot" style="background:#7c3aed"></div>Kesihatan</div>
                        <div class="budget-item-amounts"><strong>RM 65</strong> / RM 250</div>
                    </div>
                    <div class="budget-track"><div class="budget-fill" style="width:26%;background:#7c3aed"></div></div>
                </div>
                <div class="budget-summary">
                    <div class="budget-summary-lbl">Jumlah bulan ini</div>
                    <div class="budget-summary-val">RM 1,090 <span style="font-size:13px;color:#888;font-weight:400">/ RM 2,000</span></div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- CATEGORIES -->
<section class="categories-section" id="kategori">
    <div class="section-inner">
        <div class="section-header-centered reveal">
            <span class="sec-label">Kategori</span>
            <h2 class="sec-title">21 kategori lalai, siap untuk digunakan</h2>
            <p class="sec-desc">Tidak perlu setup manual. Dari hari pertama, anda sudah ada semua kategori yang diperlukan untuk menjejaki kewangan harian.</p>
        </div>
        <div class="cat-grid" id="catGrid">
            <div class="cat-pill"><span>🍔</span>Makanan & Minuman</div>
            <div class="cat-pill"><span>🚗</span>Pengangkutan</div>
            <div class="cat-pill"><span>🏠</span>Perumahan</div>
            <div class="cat-pill"><span>💡</span>Utiliti</div>
            <div class="cat-pill"><span>🎬</span>Hiburan</div>
            <div class="cat-pill"><span>🛍️</span>Membeli-belah</div>
            <div class="cat-pill"><span>💊</span>Kesihatan</div>
            <div class="cat-pill"><span>📚</span>Pendidikan</div>
            <div class="cat-pill"><span>👗</span>Pakaian</div>
            <div class="cat-pill"><span>✈️</span>Pelancongan</div>
            <div class="cat-pill"><span>🎁</span>Hadiah</div>
            <div class="cat-pill"><span>💪</span>Sukan & Hobi</div>
            <div class="cat-pill"><span>💰</span>Gaji</div>
            <div class="cat-pill"><span>📈</span>Pelaburan</div>
            <div class="cat-pill"><span>🏦</span>Simpanan</div>
            <div class="cat-pill"><span>💼</span>Perniagaan</div>
            <div class="cat-pill"><span>💅</span>Kecantikan</div>
            <div class="cat-pill"><span>🐾</span>Haiwan Peliharaan</div>
            <div class="cat-pill"><span>📱</span>Teknologi</div>
            <div class="cat-pill"><span>🔧</span>Pelbagai</div>
            <div class="cat-pill highlight"><span>➕</span>Buat Kategori Sendiri</div>
        </div>
        <p class="cat-note reveal" style="transition-delay:.3s">Tambah kategori sendiri bila-bila masa — tiada had.</p>
    </div>
</section>


<!-- MOBILE SECTION -->
<section class="mobile-section">
    <div class="mobile-inner section-inner">
        <div class="reveal">
            <span class="sec-label">Mudah Di Mobile</span>
            <h2 class="sec-title">Tambah transaksi<br>dalam 5 saat.</h2>
            <p class="sec-desc" style="margin-bottom:32px">FAB button tersedia di setiap halaman. Ketuk, isi jumlah, pilih kategori, simpan. Selesai — tanpa perlu navigasi ke mana-mana.</p>
            <div class="budget-checks">
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Butang tambah (+) sentiasa kelihatan</span></div>
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Dashboard penuh dalam skrin kecil</span></div>
                <div class="budget-check"><div class="check-icon"><svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/></svg></div><span>Dioptimumkan untuk sentuhan, bukan klik</span></div>
            </div>
        </div>
        <div class="phone-wrap reveal" style="transition-delay:.15s">
            <div class="phone">
                <div class="phone-notch"></div>
                <div class="phone-screen">
                    <!-- Status bar -->
                    <div class="phone-status">
                        <span>9:41</span>
                        <div class="phone-status-icons">
                            <div class="phone-signal">
                                <span></span><span></span><span></span><span></span>
                            </div>
                        </div>
                    </div>
                    <!-- App header -->
                    <div class="phone-header">
                        <div class="phone-header-title">Dashboard</div>
                        <div class="phone-header-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>
                    <!-- Body -->
                    <div class="phone-body">
                        <div class="phone-summary-card">
                            <div class="phone-summary-card-lbl">Baki Semasa</div>
                            <div class="phone-summary-card-val">RM 4,280</div>
                        </div>
                        <div class="phone-row">
                            <div class="phone-mini-card">
                                <div class="phone-mini-lbl">Pendapatan</div>
                                <div class="phone-mini-val inc">RM 6,500</div>
                            </div>
                            <div class="phone-mini-card">
                                <div class="phone-mini-lbl">Perbelanjaan</div>
                                <div class="phone-mini-val exp">RM 1,842</div>
                            </div>
                        </div>
                        <div class="phone-txn-section">
                            <div class="phone-txn-lbl">Terkini</div>
                            <div class="phone-txn"><div><div class="phone-txn-name">Gaji Bulanan</div><div class="phone-txn-cat">Pendapatan</div></div><div class="phone-txn-amt inc">+RM 6,500</div></div>
                            <div class="phone-txn"><div><div class="phone-txn-name">Tesco Online</div><div class="phone-txn-cat">Makanan</div></div><div class="phone-txn-amt exp">-RM 124</div></div>
                            <div class="phone-txn"><div><div class="phone-txn-name">TNB Bil</div><div class="phone-txn-cat">Utiliti</div></div><div class="phone-txn-amt exp">-RM 88</div></div>
                        </div>
                    </div>
                    <!-- FAB -->
                    <div class="phone-fab">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- HOW IT WORKS -->
<section class="steps-section" id="cara-kerja" style="background:var(--bg-2);border-top:1px solid var(--border)">
    <div class="section-inner">
        <div class="section-header-centered reveal">
            <span class="sec-label">Cara Kerja</span>
            <h2 class="sec-title">Mulakan dalam tiga langkah</h2>
        </div>
        <div class="steps">
            <div class="step reveal" style="transition-delay:.05s">
                <div class="step-num">01</div>
                <div class="step-title">Daftar Akaun</div>
                <div class="step-desc">Buat akaun percuma dalam beberapa saat. Tiada kad kredit diperlukan. 21 kategori lalai sudah tersedia.</div>
            </div>
            <div class="step reveal" style="transition-delay:.15s">
                <div class="step-num">02</div>
                <div class="step-title">Rekod Transaksi</div>
                <div class="step-desc">Catat perbelanjaan dan pendapatan setiap hari. Pilih kategori, tambah jumlah, simpan dalam satu klik.</div>
            </div>
            <div class="step reveal" style="transition-delay:.25s">
                <div class="step-num">03</div>
                <div class="step-title">Fahami Corak Anda</div>
                <div class="step-desc">Tengok carta dan analitik untuk faham ke mana perginya wang anda. Buat keputusan kewangan lebih bijak.</div>
            </div>
        </div>
    </div>
</section>


<!-- QUOTE -->
<div class="quote-strip reveal">
    <blockquote>Kalau tak catat, tak tahu ke mana perginya wang.</blockquote>
    <div class="quote-attr">Prinsip Nomos — Jejak Dulu, Faham Kemudian</div>
</div>


<!-- CTA -->
<section class="cta-section">
    <div class="cta-glow"></div>
    <h2 class="reveal">Ambil kawalan kewangan anda hari ini</h2>
    <p class="cta-sub reveal" style="transition-delay:.08s">Percuma. Tiada had. Data anda, untuk anda sahaja.</p>
    <div class="cta-btns reveal" style="transition-delay:.16s">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-brand">Ke Dashboard →</a>
        @else
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-brand">Mulakan Sekarang →</a>
            @endif
            <a href="{{ route('login') }}" class="btn-ghost-white">Log Masuk</a>
        @endauth
    </div>
</section>


<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <a href="{{ route('home') }}" class="footer-logo">nom<span class="o">o</span>s</a>
        <p class="footer-copy">© {{ date('Y') }} Nomos. Dibina dengan Laravel &amp; Livewire.</p>
        <div class="footer-links">
            <a href="{{ route('login') }}">Log Masuk</a>
            @if (Route::has('register'))<a href="{{ route('register') }}">Daftar</a>@endif
        </div>
    </div>
</footer>


<script>
    /* ── THEME ─────────────────────────────────────────────── */
    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('nomos-theme', next);
    }

    /* ── MOBILE NAV ────────────────────────────────────────── */
    function toggleNav() {
        document.getElementById('mobileNav').classList.toggle('open');
    }
    document.querySelectorAll('#mobileNav a').forEach(function(a) {
        a.addEventListener('click', function() {
            document.getElementById('mobileNav').classList.remove('open');
        });
    });

    /* ── SMOOTH SCROLL ─────────────────────────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            var href = a.getAttribute('href');
            if (href.length > 1) {
                var target = document.querySelector(href);
                if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            }
        });
    });

    /* ── SCROLL REVEAL ─────────────────────────────────────── */
    var revealObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('in');
                revealObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function(el) { revealObs.observe(el); });

    /* ── SCROLLSPY ─────────────────────────────────────────── */
    var spySections = document.querySelectorAll('#features, #preview, #bajet, #cara-kerja');
    var navLinks    = document.querySelectorAll('.nav-links a[href^="#"]');
    var spyObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var id = '#' + entry.target.id;
                navLinks.forEach(function(link) {
                    link.classList.toggle('active', link.getAttribute('href') === id);
                });
            }
        });
    }, { threshold: 0.35, rootMargin: '-10% 0px -55% 0px' });
    spySections.forEach(function(s) { spyObs.observe(s); });

    /* ── CHART BAR ANIMATION ───────────────────────────────── */
    var chartEl = document.getElementById('mainChart');
    if (chartEl) {
        var chartObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('bars-play');
                    chartObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        chartObs.observe(chartEl);
    }

    /* ── BUDGET BAR ANIMATION ──────────────────────────────── */
    var budgetEl = document.getElementById('budgetCard');
    if (budgetEl) {
        var budgetObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('budget-animated');
                    budgetObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        budgetObs.observe(budgetEl);
    }

    /* ── CATEGORY PILLS STAGGER ────────────────────────────── */
    var catGrid = document.getElementById('catGrid');
    if (catGrid) {
        var pills = catGrid.querySelectorAll('.cat-pill');
        var catObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    pills.forEach(function(pill, i) {
                        pill.style.animationDelay = (i * 35) + 'ms';
                        pill.classList.add('pill-in');
                    });
                    catObs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        catObs.observe(catGrid);
    }
</script>

</body>
</html>
