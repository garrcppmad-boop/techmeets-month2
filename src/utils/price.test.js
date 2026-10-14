import { describe, test, expect } from 'vitest'
import { calcTaxIncluded } from './price'

describe('calcTaxIncluded', () => {
  test('税率10%で正しく計算できる', () => {
    expect(calcTaxIncluded(1000)).toBe(1100)
  })

  test('端数は切り捨てになる', () => {
    expect(calcTaxIncluded(100, 0.08)).toBe(108)
  })

  test('0円の商品は0円になる', () => {
    expect(calcTaxIncluded(0)).toBe(0)
  })
})
