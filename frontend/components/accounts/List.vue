<template>
  <v-card>
    <v-data-table
      :headers="accounts.tableHeaders"
      :items="accounts.items"
      :loading="accounts.isLoading"
      disable-sort
      :footer-props="{ itemsPerPageOptions: [10, 20, 100] }"
    >
      <template v-slot:item.created_at="{ item }">
        {{ formattedDate(item.created_at) }}
      </template>
      <template v-slot:item.updated_at="{ item }">
        {{ formattedDate(item.updated_at) }}
      </template>
      <template v-slot:item.balance="{ item }">
        {{ formattedBalance(item.balance) }}
      </template>
      <template v-slot:item.username="{ item }">
        <v-btn
          text
          small
          target="_blank"
          :href="`https://bitclout.com/u/${item.username}`"
        >
          {{ item.username }}
        </v-btn>
      </template>
      <template v-slot:item.public_key="{ item }">
        <div class="text-truncate d-inline-block justify-center">
          {{ item.public_key }}
        </div>
        <v-btn icon @click="copyPuplicKey(item.public_key)"
          ><v-icon small>mdi-content-copy</v-icon></v-btn
        >
      </template>
    </v-data-table>
    <create-dialog ref="createDialog" />
    <v-fab-transition>
      <v-btn
        color="pink"
        fab
        dark
        class="v-btn--create-record"
        @click="openDialogAccountCreate"
      >
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-fab-transition>
  </v-card>
</template>
<style scoped>
.text-truncate {
  max-width: 150px;
  vertical-align: middle;
}
</style>
<script>
import CreateDialog from "./dialogs/Create.vue";
import { configs } from "~/configs";
export default {
  components: {
    CreateDialog,
  },
  data: () => ({
    tab: "accounts",
    accounts: {
      tableHeaders: [
        { text: "Created", value: "created_at", align: "center" },
        { text: "Updated", value: "updated_at", align: "center" },
        { text: "Username", value: "username", align: "center" },
        { text: "Balance", value: "balance", align: "right" },
        { text: "Public key", value: "public_key", align: "left" },
      ],
      items: [],
      isLoading: false,
      creature: {
        state: false,
        isLoading: false,
        error: null,
        fields: {
          mnemonic: {
            value: null,
            rules: [(v) => !!v || "The field is required."],
          },
          password: {
            value: null,
          },
        },
      },
    },
    ws: null,
  }),
  created() {
    this.fetchAccounts();
    this.ws = setInterval(() => {
      this.fetchAccounts();
    }, 1000);
  },
  beforeDestroy() {
    if (this.ws) {
      clearInterval(this.ws);
    }
  },
  methods: {
    copyPuplicKey(publicKey) {
      window.navigator.clipboard.writeText(publicKey);
    },
    fetchAccounts() {
      this.$axios.$get(`${configs.apiUrl}v1/accounts`).then((data) => {
        this.accounts.items = data;
      });
    },
    formattedDate(value) {
      return `${value.replace(/T/, " ").replace(/\..+/, "")} UTC`;
    },
    formattedBalance(value) {
      return (value / 10 ** 9).toFixed(2);
    },
    openDialogAccountCreate() {
      this.$refs.createDialog.open();
    },
  },
};
</script>