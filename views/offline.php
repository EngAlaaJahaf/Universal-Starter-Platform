<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>أنت غير متصل بالإنترنت | منصة الأخبار التقنية</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0b1120">
    <style>
        :root {
            --bg-body: #060a14;
            --bg-card: rgba(11, 17, 32, 0.95);
            --border: rgba(0, 210, 255, 0.25);
            --cyan: #00d2ff;
            --purple: #9333ea;
            --blue: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --font-family: system-ui, -apple-system, 'Tajawal', Tahoma, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: var(--font-family);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: radial-gradient(circle at 50% 30%, rgba(0, 210, 255, 0.08) 0%, transparent 60%);
        }
        .offline-card {
            width: 100%;
            max-width: 540px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(0, 210, 255, 0.1);
            backdrop-filter: blur(16px);
        }
        .offline-icon-wrap {
            width: 90px;
            height: 90px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, rgba(0, 210, 255, 0.15), rgba(147, 51, 234, 0.15));
            border: 1px solid rgba(0, 210, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.2);
            animation: pulse 2.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.05); opacity: 1; }
        }
        h1 {
            font-size: 1.6rem;
            color: var(--text-main);
            margin-bottom: 12px;
            font-weight: 800;
        }
        p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            color: #fff;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 210, 255, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 210, 255, 0.5);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--cyan);
        }
        .tip-box {
            margin-top: 25px;
            padding: 12px 16px;
            background: rgba(0, 210, 255, 0.05);
            border: 1px dashed rgba(0, 210, 255, 0.25);
            border-radius: 10px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon-wrap">
            📡
        </div>
        <h1>لا يوجد اتصال بالإنترنت</h1>
        <p>يبدو أن جهازك غير متصل بالشبكة حالياً. يمكنك إعادة المحاولة عند عودة الاتصال أو تصفح المقالات المحفوظة في ذاكرة التطبيق.</p>
        
        <div class="actions">
            <button onclick="window.location.reload()" class="btn btn-primary">
                <span>🔄</span>
                <span>إعادة المحاولة</span>
            </button>
            <a href="/" class="btn btn-secondary">
                <span>🏠</span>
                <span>الصفحة المحفوظة</span>
            </a>
            <a href="/bookmarks" class="btn btn-secondary">
                <span>📑</span>
                <span>المفضلة</span>
            </a>
        </div>

        <div class="tip-box">
            💡 <strong>تلميح:</strong> تطبيق المنصة يدعم تصفح الأخبار المخزنة مسبقاً وتثبيت التطبيق على هاتفك أو حاسوبك للوصول الفوري!
        </div>
    </div>
</body>
</html>
