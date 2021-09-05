<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card v-if="item">
      <v-card-title>
        <span class="headline">Viewer</span>
      </v-card-title>
      <v-card-text>
        {{ item.result }}
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="close"
          >Close</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
export default {
  data: () => ({
    dialog: false,
    item: {},
  }),
  methods: {
    open(item) {
      this.item = item;
      this.dialog = true;
    },
    close() {
      this.dialog = false;
    },
  },
};
</script>
