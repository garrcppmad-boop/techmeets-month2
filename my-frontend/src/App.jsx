import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import PostForm from './PostForm'
import PostList from './PostList'
import './App.css'

const API_URL = 'http://localhost/api/posts'

function App() {
  const [posts, setPosts] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const fetchPosts = useCallback(() => {
    setLoading(true)
    return axios
      .get(API_URL)
      .then((response) => setPosts(response.data.data))
      .catch((err) => setError(err.message))
      .finally(() => setLoading(false))
  }, [])

  useEffect(() => {
    fetchPosts()
  }, [fetchPosts])

  return (
    <>
      <section id="center">
        <div>
          <h1>投稿一覧アプリ</h1>
          <p>Laravel API から取得した投稿を表示します</p>
        </div>
      </section>

      <PostForm onCreated={fetchPosts} />
      <PostList posts={posts} loading={loading} error={error} />
    </>
  )
}

export default App
