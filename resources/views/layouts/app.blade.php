<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'App') - My App</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f5f5f5; color: #333; }
        nav {
            background: #1e40af;
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            height: 56px;
        }
        nav a { color: #fff; text-decoration: none; font-weight: 500; }
        nav a:hover { opacity: 0.8; }
        nav .brand { font-size: 1.2rem; font-weight: 700; margin-right: auto; }
        .container { max-width: 860px; margin: 32px auto; padding: 0 16px; }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        .alert-success { background: #dbeafe; color: #1e3a8a; border: 1px solid #93c5fd; }
        .btn {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-primary   { background: #1e40af; color: #fff; }
        .btn-secondary { background: #6b7280; color: #fff; }
        .btn-danger    { background: #dc2626; color: #fff; }
        .btn:hover     { opacity: 0.85; }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            margin-bottom: 16px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-group .error { color: #dc2626; font-size: 0.85rem; margin-top: 4px; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            background: #dbeafe;
            color: #1e3a8a;
        }
        .actions { display: flex; gap: 8px; margin-top: 16px; align-items: center; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        th { background: #1e40af; color: #fff; padding: 12px 16px; text-align: left; font-size: 0.9rem; }
        td { padding: 12px 16px; border-bottom: 1px solid #e5e7eb; font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f0f9ff; }
        h1 { margin-bottom: 20px; font-size: 1.6rem; }
        .meta { font-size: 0.85rem; color: #6b7280; margin-top: 6px; }
        .stock-low { color: #dc2626; font-weight: 600; }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="brand">My App</a>
        <a href="{{ route('products.index') }}">商品管理</a>
    </nav>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
