<template>
  <div>
    <v-app-bar app dark color="primary">
      <v-app-bar-title>Trading</v-app-bar-title>
      <template v-slot:extension>
        <tabs />
      </template>
    </v-app-bar>
    <v-main>
      <client-only>
        <list-operations />
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
    ListOperations: () => {
      if (process.client) {
        return import("~/components/operations/List.vue");
      }
    },
  },
};
</script>
