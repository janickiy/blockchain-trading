export const bitclout = {
  formatUSDMemo: {},
  nanosToBitCloutMemo: {},
  rate: {
    "SatoshisPerBitCloutExchangeRate": null,
    "NanosSold": null,
    "USDCentsPerBitcoinExchangeRate": null,
    "USDCentsPerBitCloutExchangeRate": null,
    "USDCentsPerBitCloutReserveExchangeRate": null,
    "BuyBitCloutFeeBasisPoints": null
  },
  setExchangeRate(value) {
    this.rate = value;
  },
  nanosToUSD(e, t) {
    return null == t && (t = 4),
      this.formatUSD(this.nanosToUSDNumber(e), t)
  },
  getPriceToUSD(creator_coins_in_circulation, bitclout_price_in_usd) {
    let value = 0.003 * (creator_coins_in_circulation * creator_coins_in_circulation) * (bitclout_price_in_usd / 100);
    return (value / Math.pow(10, 9)).toFixed(4);
  },
  nanosToUSDNumber(e) {
    return e / this.nanosPerUSDExchangeRate()
  },
  formatUSD(e, t) {
    return this.formatUSDMemo[e] &&
      this.formatUSDMemo[e][t] ||
      (this.formatUSDMemo[e] = this.formatUSDMemo[e] || {},
        this.formatUSDMemo[e][t] = Number(e).toLocaleString("en-US", {
          style: "currency",
          currency: "USD",
          minimumFractionDigits: t
        })
      ),
      this.formatUSDMemo[e][t]
  },
  nanosPerUSDExchangeRate() {
    return 1e9 / (this.rate.USDCentsPerBitCloutExchangeRate / 100)
  },
  usdYouWouldGetIfYouSoldDisplay(e, t, n = !0) {
    if (0 == e) return "$0";
    const o = this.nanosToUSDNumber(this.bitcloutNanosYouWouldGetIfYouSold(e, t));
    return n ? this.abbreviateNumber(o, 2, !0) : this.formatUSD(o, 2)
  },
  bitcloutNanosYouWouldGetIfYouSold(t, n) {
    return n.BitCloutLockedNanos * (1 - Math.pow(1 - t / n.CoinsInCirculationNanos, 1 / this.CREATOR_COIN_RESERVE_RATIO)) * (1e4 - this.CREATOR_COIN_TRADE_FEED_BASIS_POINTS) / 1e4
  },
  creatorCoinNanosToUSDNaive(e, t, n = !1) {
    const o = this.nanosToUSDNumber(e / 1e9 * t);
    return n ? this.abbreviateNumber(o, 2, !0) : this.formatUSD(o, 2)
  },
  CREATOR_COIN_RESERVE_RATIO: .3333333,
  CREATOR_COIN_TRADE_FEED_BASIS_POINTS: 1,
  abbreviateNumber(e, t, n = !1) {
    let o;
    const i = Math.floor((("" + e.toFixed(0)).length - 1) / 3);
    return 0 === i && (t = Math.min(2, t)), o = (e / Math.pow(1e3, i)).toFixed(t), n && (o = this.formatUSD(o, t)), o + ["", "K", "M", "B", "T"][i]
  },
  nanosToBitClout(e, t) {
    return this.nanosToBitCloutMemo[e] && this.nanosToBitCloutMemo[e][t] || (this.nanosToBitCloutMemo[e] = this.nanosToBitCloutMemo[e] || {}, !t && e > 0 && (t = Math.floor(10 - Math.log10(e))), t < 2 && (t = 2), t > 9 && (t = 9), this.nanosToBitCloutMemo[e][t] = Number(e / 1e9).toLocaleString("en-US", {
      style: "decimal",
      currency: "USD",
      minimumFractionDigits: 2,
      maximumFractionDigits: t
    })), this.nanosToBitCloutMemo[e][t]
  }
}
