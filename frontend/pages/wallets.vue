<template>
  <div>
    <v-app-bar app dark color="primary">
      <v-toolbar-title>Wallets</v-toolbar-title>
      <template v-slot:extension>
        <tabs initial-tab="wallets" />
      </template>
    </v-app-bar>
    <v-main>
      <client-only>
        <list-wallets />
      </client-only>
    </v-main>
  </div>
</template>

<script>
import Tabs from "~/components/Tabs.vue";
export default {
  middleware: 'auth',
  components: {
    Tabs,
    ListWallets: () => {
      if (process.client) {
        return import("~/components/wallets/List.vue");
      }
    },
  },
};
</script>