<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card :loading="isLoading">
      <v-card-title>
        <span class="headline">Create operation</span>
      </v-card-title>
      <v-card-text>
        <operation-form ref="form" v-bind="$props" @loading="loading" />
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn :disabled="isLoading" color="blue darken-1" text @click="close">
          Close
        </v-btn>
        <v-btn
          :disabled="isLoading"
          color="blue darken-1"
          text
          @click="createOperation"
          >Create</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
import OperationForm from "../forms/Operation.vue";
export default {
  components: {
    OperationForm,
  },
  props: ["accounts"],
  data: () => ({
    dialog: false,
    isLoading: false
  }),
  computed: {
    accountsForSelectItems() {
      return this.accounts.items.map((account) => ({
        value: account.id,
        text: account.username,
      }));
    },
  },
  methods: {
    loading(value) {
      this.isLoading = value;
    },
    open(data) {
      this.dialog = true;
      this.$nextTick(() => {
        if (data) {
          this.$refs.form.setData(data);
        }
      });
    },
    close() {
      this.dialog = false;
    },
    async createOperation() {
      try {
        await this.$refs.form.create()
      } catch (e) {
        return;
      }
      this.$refs.form.reset();
      this.close();
    },
  },
};
</script>