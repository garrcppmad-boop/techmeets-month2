import js from '@eslint/js'
import reactPlugin from 'eslint-plugin-react'

export default [
  js.configs.recommended,
  {
    plugins: { react: reactPlugin },
    rules: {
      // 未使用変数をエラーに
      'no-unused-vars': 'error',
      // console.logを警告に（本番コードには残さない）
      'no-console': 'warn',
      // == ではなく === を強制
      'eqeqeq': 'error',
    }
  }
]
