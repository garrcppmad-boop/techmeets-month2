function PostItem({ post }) {
  return (
    <li>
      <h3>{post.title}</h3>
      <p>{post.content}</p>
      <p>
        カテゴリ: {post.category} / 投稿者: {post.author ?? '不明'} /{' '}
        {post.created_at}
      </p>
    </li>
  )
}

export default PostItem
