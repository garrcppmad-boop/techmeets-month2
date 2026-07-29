import PostItem from './PostItem'

function PostList({ posts, loading, error }) {
  if (loading) return <p>読み込み中...</p>
  if (error) return <p>エラー: {error}</p>

  return (
    <section id="post-list">
      <h2>投稿一覧</h2>
      {posts.length === 0 ? (
        <p>投稿がありません</p>
      ) : (
        <ul>
          {posts.map((post) => (
            <PostItem key={post.id} post={post} />
          ))}
        </ul>
      )}
    </section>
  )
}

export default PostList
