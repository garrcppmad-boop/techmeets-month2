import { useState } from 'react'
import axios from 'axios'

const API_URL = 'http://localhost/api/posts'
const CATEGORIES = ['技術', 'ライフスタイル', '学習', 'その他']

const initialForm = {
  title: '',
  content: '',
  category: CATEGORIES[0],
}

function PostForm({ onCreated }) {
  const [form, setForm] = useState(initialForm)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState(null)

  const handleChange = (e) => {
    const { name, value } = e.target
    setForm((prev) => ({ ...prev, [name]: value }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSubmitting(true)
    setError(null)

    try {
      await axios.post(API_URL, form)
      setForm(initialForm)
      await onCreated()
    } catch (err) {
      setError(err.response?.data?.message ?? err.message)
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <section id="post-form">
      <h2>新規投稿</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="title">タイトル</label>
          <input
            id="title"
            name="title"
            type="text"
            value={form.title}
            onChange={handleChange}
            maxLength={200}
            required
          />
        </div>

        <div>
          <label htmlFor="category">カテゴリ</label>
          <select
            id="category"
            name="category"
            value={form.category}
            onChange={handleChange}
          >
            {CATEGORIES.map((category) => (
              <option key={category} value={category}>
                {category}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label htmlFor="content">本文</label>
          <textarea
            id="content"
            name="content"
            value={form.content}
            onChange={handleChange}
            maxLength={10000}
            required
          />
        </div>

        {error && <p className="error">エラー: {error}</p>}

        <button type="submit" disabled={submitting}>
          {submitting ? '送信中...' : '投稿する'}
        </button>
      </form>
    </section>
  )
}

export default PostForm
