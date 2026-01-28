<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#16a34a">
    <title>オフライン - 北東京CO-OP Hub</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            padding-bottom: env(safe-area-inset-bottom);
        }
        
        .offline-container {
            text-align: center;
            max-width: 400px;
            width: 100%;
            background: white;
            border-radius: 24px;
            padding: 48px 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .offline-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .offline-icon svg {
            width: 50px;
            height: 50px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }
        
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .offline-message {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        
        .retry-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #16a34a;
            color: white;
            border: none;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 50px;
            min-width: 160px;
            touch-action: manipulation;
        }
        
        .retry-button:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(22, 163, 74, 0.3);
        }
        
        .retry-button:active {
            transform: translateY(0);
        }
        
        .retry-button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .retry-button svg {
            width: 20px;
            height: 20px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding: 10px 20px;
            background: #fef3c7;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            color: #92400e;
        }
        
        .status-badge.online {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #f59e0b;
        }
        
        .status-badge.online .status-dot {
            background: #10b981;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .spin {
            animation: spin 1s linear infinite;
        }
        
        .tips {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            text-align: left;
        }
        
        .tips h2 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }
        
        .tips ul {
            list-style: none;
            font-size: 14px;
            color: #6b7280;
        }
        
        .tips li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .tips li::before {
            content: '💡';
            flex-shrink: 0;
        }
        
        .logo {
            margin-bottom: 24px;
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
        }
        
        @media (max-width: 480px) {
            .offline-container {
                padding: 32px 24px;
                border-radius: 16px;
            }
            
            .offline-icon {
                width: 80px;
                height: 80px;
            }
            
            .offline-icon svg {
                width: 40px;
                height: 40px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            .offline-message {
                font-size: 15px;
            }
        }
    </style>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="offline-container">
        <div class="logo">
            <img src="/images/icons/icon-192x192.png" alt="CO-OP Hub" onerror="this.style.display='none'">
        </div>
        
        <div class="offline-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 20h.01" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10.71 5.05A16 16 0 0 1 22.58 9" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="m2 2 20 20" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <h1>インターネットに接続できません</h1>
        
        <p class="offline-message">
            現在オフラインです。<br>
            インターネット接続を確認してください。<br>
            接続が回復すると自動的にページが更新されます。
        </p>
        
        <button class="retry-button" id="retryBtn" onclick="checkConnection()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"/>
                <polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            <span id="retryText">再試行</span>
        </button>
        
        <div class="status-badge" id="statusBadge">
            <span class="status-dot"></span>
            <span id="statusText">オフライン</span>
        </div>
        
        <div class="tips">
            <h2>トラブルシューティング</h2>
            <ul>
                <li>Wi-Fiまたはモバイルデータがオンになっているか確認</li>
                <li>機内モードがオフになっているか確認</li>
                <li>ルーターを再起動してみる</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Update status when online/offline changes
        function updateStatus() {
            const badge = document.getElementById('statusBadge');
            const text = document.getElementById('statusText');
            
            if (navigator.onLine) {
                badge.classList.add('online');
                text.textContent = 'オンライン - 更新中...';
                
                // Reload after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                badge.classList.remove('online');
                text.textContent = 'オフライン';
            }
        }
        
        // Listen for online/offline events
        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);
        
        // Manual retry
        function checkConnection() {
            const btn = document.getElementById('retryBtn');
            const text = document.getElementById('retryText');
            
            btn.disabled = true;
            text.textContent = '接続確認中...';
            btn.querySelector('svg').classList.add('spin');
            
            fetch('/', { 
                method: 'HEAD', 
                cache: 'no-store',
                mode: 'no-cors'
            })
            .then(() => {
                window.location.reload();
            })
            .catch(() => {
                btn.disabled = false;
                text.textContent = '再試行';
                btn.querySelector('svg').classList.remove('spin');
                updateStatus();
            });
            
            // Timeout fallback
            setTimeout(() => {
                if (btn.disabled) {
                    btn.disabled = false;
                    text.textContent = '再試行';
                    btn.querySelector('svg').classList.remove('spin');
                }
            }, 5000);
        }
        
        // Initial status check
        updateStatus();
        
        // Periodic check every 5 seconds
        setInterval(() => {
            if (!navigator.onLine) {
                // Silent check
                fetch('/', { method: 'HEAD', cache: 'no-store', mode: 'no-cors' })
                    .then(() => window.location.reload())
                    .catch(() => {});
            }
        }, 5000);
    </script>
</body>
</html>