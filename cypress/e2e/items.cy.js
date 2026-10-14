describe('商品一覧', () => {
  it('一覧ページが表示され、商品が1件以上ある', () => {
    cy.visit('/')                              // baseUrl にアクセス
    cy.get('ul li').should('have.length.greaterThan', 0)
  })

  it('フォームから商品を登録すると一覧に表示される', () => {
    cy.visit('/')
    cy.get('input[placeholder="商品名"]').type('テスト商品')
    cy.get('button[type="submit"]').click()
    cy.contains('テスト商品')                 // 登録後に一覧へ表示されることを確認
  })
})