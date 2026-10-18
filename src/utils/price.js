// 税込価格を計算して返す（端数切り捨て）
export function calcTaxIncluded(price, taxRate = 0.1) {
  return Math.floor(price * (1 + taxRate))
}
