<template>
  <div>
    <v-card flat :loading="loading">
      <v-card-text>
        <v-chip-group
          v-model="selectedAccount"
          active-class="primary"
          mandatory
        >
          <v-chip
            v-for="item in accounts.items"
            :key="item.id"
            :value="item"
            label
            class="mr-4"
            >{{ item.username }}</v-chip
          >
        </v-chip-group>
      </v-card-text>
      <v-card-text class="pt-0 pb-2">
        <code v-if="selectedAccount" class="subtitle-1">{{
          selectedAccount.public_key
        }}</code>
      </v-card-text>

      <v-card-text class="subtitle-1" v-if="!loading && creators.length">
        Total profit: <b>{{ nanosToBitClout(totals.profit) }}</b> Total value:
        <b>{{ totals.value }}</b> Total spent:
        <b>{{ nanosToBitClout(totals.spent) }}</b> Total sold:
        <b>{{ nanosToBitClout(totals.sold) }}</b>
      </v-card-text>

      <operations-multiple-inline-form
        v-if="selectedTargets.length"
        :from-account-id="selectedAccount.id"
        :targets="selectedTargets"
      />

      <v-alert dense outlined type="error" v-if="error" class="mx-4">
        {{ error }}
      </v-alert>

      <v-card-text v-if="selectedAccount && !loading && !error">
        Filter by 24h CHNG:
        <v-btn-toggle v-model="filter_by_24h_chng">
          <v-btn text x-small>all</v-btn>
          <v-btn text x-small>price up</v-btn>
          <v-btn text x-small>price down</v-btn>
          <v-btn text x-small>no changes</v-btn>
        </v-btn-toggle>
      </v-card-text>

      <v-card-text v-if="selectedAccount && !loading && !error">
        <v-card>
          <v-card-subtitle>Coins Purchased</v-card-subtitle>
          <v-divider></v-divider>
          <v-data-table
            v-model="selectedItems"
            :headers="headers"
            :items="creators"
            item-key="public_key"
            :footer-props="{ itemsPerPageOptions: [10, 20, 100] }"
            show-select
          >
            <template v-slot:item.actions="{ item }">
              <operations-inline-form
                :from-account-id="selectedAccount.id"
                :target-username="item.username"
              />
            </template>
            <template v-slot:item.price="{ item }">
              <!--<span
                v-if="item.change"
                :class="item.change > 0 ? 'green' : 'red'"
                class="pl-1 mr-1 white--text"
              >
                {{ item.change }}%
              </span>-->

              {{ item.price_btclt }}
              ({{ item.price }})
            </template>
            <template v-slot:item.usd_value="{ item }">
              {{ item.btclt_value }}
              ({{ item.usd_value }})
            </template>
            <template v-slot:item.profit="{ item }">
              <span v-if="item.profit">
                <span :class="item.profit > 0 ? 'green--text' : 'red--text'"
                  >{{ nanosToBitClout(item.profit) }} {{ item.profit_usd }}
                </span>
                <br />Spent: {{ nanosToBitClout(item.spent) }} <br />Sold:
                {{ nanosToBitClout(item.sold) }}
              </span>
            </template>
            <template v-slot:item.change24h="{ item }">
              <span v-if="item.change24h && item.change24h !== 0">
                <span :class="item.change24h > 0 ? 'green--text' : 'red--text'"
                  >{{ item.change24h }}%
                </span>
              </span>
            </template>
          </v-data-table>
        </v-card>
      </v-card-text>
    </v-card>
  </div>
</template>
<script>
import OperationsInlineForm from "~/components/operations/forms/Inline.vue";
import OperationsMultipleInlineForm from "~/components/operations/forms/MultipleInline.vue";
import { bitclout } from "~/utils/bitclout";
import { configs } from "~/configs";
export default {
  components: {
    OperationsInlineForm,
    OperationsMultipleInlineForm,
  },
  data: () => ({
    error: null,
    headers: [
      { text: "Name", value: "username", class: "px-0", cellClass: "px-0" },
      {
        text: "Price Btclt (usd)",
        value: "price",
        class: "text-no-wrap",
        cellClass: "text-no-wrap",
      },
      {
        text: "24h CHNG",
        value: "change24h",
        class: "text-no-wrap",
        cellClass: "text-no-wrap",
      },
      {
        text: "Coins",
        value: "coins",
        class: "text-no-wrap px-0",
        cellClass: "px-0",
      },
      {
        text: "Value Btclt (usd)",
        value: "usd_value",
        class: "text-no-wrap",
        cellClass: "text-no-wrap",
      },
      {
        text: "Profit",
        value: "profit",
        class: "text-no-wrap",
        cellClass: "text-no-wrap",
      },
      { text: "Actions", value: "actions", sortable: false },
    ],
    ws: null,
    selectedItems: [],
    selectedAccount: null,
    usersHODL: [],
    loading: false,
    accounts: {
      items: [],
    },
    changes24h: [],
    filter_by_24h_chng: 0,
  }),
  async created() {
    await this.fetchExchangeRate();
    this.fetchAccounts();
  },
  beforeDestroy() {
    if (this.wsHolds) {
      clearTimeout(this.wsHolds);
    }
  },
  watch: {
    selectedAccount(val) {
      if (val) {
        this.selectedItems = [];
        this.loading = true;
        this.filter_by_24h_chng = 0;
        this.fetchUsersHODL(this.selectedAccount.public_key);
      }
    },
  },
  computed: {
    totals() {
      let totals = {
        spent: 0,
        sold: 0,
        profit: 0,
        value: 0,
      };

      const items = this.selectedItems.length
        ? this.selectedItems
        : this.creators;

      for (let i = 0; i < items.length; i++) {
        totals.spent += items[i].spent;
        totals.sold += items[i].sold;
        totals.profit += items[i].profit;
        totals.value += items[i].btclt_value * 1;
      }

      return totals;
    },
    creators() {
      return this.usersHODL
        .map((item) => {
          return {
            public_key: item.public_key,
            username: item.username,
            change: item.change24h,
            price: this.nanosToUSD(item.price),
            price_btclt: bitclout.nanosToBitClout(item.price, 4),
            profit: this.getProfit(item),
            profit_usd: this.getProfitToUSD(item),
            spent: item.pivot.spent,
            sold: item.pivot.sold,
            coins: (item.pivot.balance / Math.pow(10, 9)).toFixed(4),
            value: item.pivot.balance * item.price,
            usd_value: this.getPriceToUSD(item.pivot.balance, item.price),
            change24h: parseFloat(item.change24h),
            btclt_value: (
              bitclout.nanosToBitClout(item.price, 4) *
              (item.pivot.balance / Math.pow(10, 9))
            ).toFixed(6),
          };
        })
        .filter((v) => {
          if (this.filter_by_24h_chng === 1) {
            return v.change24h && v.change24h > 0;
          }
          if (this.filter_by_24h_chng === 2) {
            return v.change24h && v.change24h < 0;
          }
          if (this.filter_by_24h_chng === 3) {
            return !v.change24h;
          }
          return true;
        });
    },
    selectedTargets() {
      return this.selectedItems.map((o) => {
        return o.username;
      });
    },
  },
  methods: {
    async fetchExchangeRate() {
      this.loading = true;
      await this.$axios
        .$get(`${configs.apiUrl}v1/exchange-rate`)
        .then((data) => {
          bitclout.setExchangeRate(data);
        })
        .catch((e) => {
          const message = `exchange-rate: ${
            !e.response ? e.message : e.response.data.message
          }`;
          this.error = message;
        })
        .finally(() => {
          this.loading = false;
        });
    },
    getProfit(item) {
      return item.pivot.sold + item.pivot.balance - item.pivot.spent;
    },
    getProfitToUSD(item) {
      const profit = this.getProfit(item);
      return this.getPriceToUSD(profit, item.price);
    },
    nanosToBitClout(v) {
      return bitclout.nanosToBitClout(v, 4);
    },
    nanosToUSD(v) {
      return bitclout.nanosToUSD(v, 2);
    },
    getPriceToUSD(BalanceNanos, CoinPriceBitCloutNanos) {
      return bitclout.creatorCoinNanosToUSDNaive(
        BalanceNanos,
        CoinPriceBitCloutNanos
      );
    },
    fetchUsersHODL(publicKey) {
      this.$axios
        .$get(`${configs.apiUrl}v1/creators/${publicKey}`)
        .then((data) => {
          this.usersHODL = data;
        })
        .catch((e) => {
          const message = `WALLET_API: ${
            !e.response ? e.message : e.response.data.message
          }`;
          alert(message);
        })
        .finally(() => {
          this.loading = false;
          this.wsHolds = setTimeout(() => {
            this.fetchUsersHODL(this.selectedAccount.public_key);
          }, 20000);
        });
    },
    fetchAccounts() {
      this.$axios.$get(`${configs.apiUrl}v1/accounts`).then((data) => {
        this.accounts.items = data;
      });
    },
    getChange(item) {
      if (
        !item.PrevPrice ||
        (item.PrevPrice &&
          item.PrevPrice === item.ProfileEntryResponse.CoinPriceBitCloutNanos)
      ) {
        return null;
      }

      if (item.PrevPrice > item.ProfileEntryResponse.CoinPriceBitCloutNanos) {
        return (
          (Math.round(
            (item.ProfileEntryResponse.CoinPriceBitCloutNanos * 100) /
              item.PrevPrice
          ) /
            100) *
          -1
        );
      } else {
        return (
          Math.round(
            (item.PrevPrice * 100) /
              item.ProfileEntryResponse.CoinPriceBitCloutNanos
          ) / 100
        );
      }
    },
  },
};
</script>
