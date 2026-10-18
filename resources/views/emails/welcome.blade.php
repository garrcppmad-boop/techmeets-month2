<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ご登録ありがとうございます</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h1 style="font-size: 20px;">{{ $user->name }} 様</h1>

    <p>この度は <strong>{{ config('app.name') }}</strong> にご登録いただき、ありがとうございます。</p>

    <p>以下のメールアドレスでアカウントが作成されました。</p>

    <p style="background: #f4f4f4; padding: 8px 12px; border-radius: 4px;">
        {{ $user->email }}
    </p>

    <p>
        <a href="{{ route('dashboard') }}"
           style="display: inline-block; background: #2563eb; color: #fff;
                  padding: 10px 20px; border-radius: 6px; text-decoration: none;">
            ダッシュボードを開く
        </a>
    </p>

    <p style="color: #888; font-size: 13px;">
        このメールに心当たりがない場合は破棄してください。
    </p>
</body>
</html>
