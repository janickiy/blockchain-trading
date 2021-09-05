<template>
  <v-tabs v-model="tab" align-with-title>
    <v-tab key="operations" to="/">Operations</v-tab>
    <v-tab key="accounts" to="/accounts">Accounts</v-tab>
    <v-tab key="wallets" to="/wallets">Wallets</v-tab>
    <v-tab @click="logout">Logout</v-tab>
  </v-tabs>
</template>
<script>
export default {
  props: {
    initialTab: {
      type: String,
      default: null,
    },
  },
  created() {
    this.tab = this.initialTab;
  },
  methods: {
    async logout() {
      await this.$auth.logout();
      this.$router.push('/login');
    }
  }
};
</script>