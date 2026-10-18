<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 一覧ページの並び替え（latest() / oldest()）を速くするインデックス。
 *
 * ファイル名について: マイグレーションはファイル名順に実行される。posts テーブルを作る
 * マイグレーションが xxxx_create_posts_table.php という名前のため、日付形式の名前にすると
 * posts テーブルより先に実行されて失敗する。そのため xxxx_zz_ で始めて後ろに並べている。
 *
 * 例: select * from posts order by created_at desc limit 10
 *   インデックスなし: 全件（20,000行）を読み込んでから並び替え（Table scan + Sort）
 *   インデックスあり: created_at の新しい順にインデックスを辿り、先頭10件だけ読む
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->index('created_at');
        });

        Schema::table('threads', function (Blueprint $table): void {
            $table->index('created_at');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->index('created_at');
        });

        // スレッド詳細: where thread_id = ? order by created_at（絞り込み + 並び替えを1つのインデックスで）
        Schema::table('replies', function (Blueprint $table): void {
            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
        });

        Schema::table('threads', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['created_at']);
        });

        Schema::table('replies', function (Blueprint $table): void {
            $table->dropIndex(['thread_id', 'created_at']);
        });
    }
};
