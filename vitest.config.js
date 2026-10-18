import { defineConfig } from 'vitest/config'

// Vitest 専用の設定。
// これが無いと vite.config.js（laravel-vite-plugin 入り）が読み込まれ、
// CI 環境では「Vite の開発サーバーを CI で起動するな」というチェックで失敗する。
export default defineConfig({
  test: {
    include: ['src/**/*.{test,spec}.{js,ts}'],
  },
})
