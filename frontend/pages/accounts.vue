<template>
  <div>
    <v-app-bar app dark color="primary">
      <v-toolbar-title>Accounts</v-toolbar-title>
      <template v-slot:extension>
        <tabs initial-tab="accounts" />
      </template>
    </v-app-bar>
    <v-main>
      <client-only>
        <list-accounts />
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
    ListAccounts: () => {
      if (process.client) {
        return import("~/components/accounts/List.vue");
      }
    },
  },
};
</script>