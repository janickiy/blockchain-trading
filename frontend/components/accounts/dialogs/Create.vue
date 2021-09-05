<template>
  <v-dialog v-model="dialog" max-width="400px" persistent>
    <v-card :loading="isLoading">
      <v-card-title>
        <span class="headline">Create account</span>
      </v-card-title>
      <v-card-text>
        <v-form
          :disabled="isLoading"
          ref="formAccountsCreature"
          lazy-validation
        >
          <v-alert
            type="error"
            transition="fade-transition"
            class="mb-6"
            text
            border="left"
            :value="!!creature.error"
          >
            {{ creature.error }}
          </v-alert>
          <v-row>
            <v-col cols="12" md="12" xs="12">
              <v-textarea
                v-model="creature.fields.mnemonic.value"
                :rules="creature.fields.mnemonic.rules"
                dense
                label="Mnemonic"
                outlined
                rows="2"
              ></v-textarea>
            </v-col>
            <v-col cols="12" md="12" xs="12">
              <v-text-field
                v-model="creature.fields.password.value"
                dense
                label="Password"
                outlined
                hint="If there is no password, leave the field blank"
              ></v-text-field>
            </v-col>
          </v-row>
        </v-form>
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
          @click="createAccount"
          >Create</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
import { configs } from "~/configs";
export default {
  data: () => ({
    dialog: false,
    isLoading: false,
    creature: {
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
  }),
  methods: {
    open() {
      this.dialog = true;
    },
    close() {
      this.dialog = false;
    },
    async createAccount() {
      this.creature.error = null;
      if (!this.$refs["formAccountsCreature"].validate()) {
        return;
      }
      try {
        this.isLoading = true;
        await this.$axios
          .$post(`${configs.apiUrl}v1/accounts`, {
            mnemonic: this.creature.fields.mnemonic.value,
            password: this.creature.fields.password.value
              ? this.creature.fields.password.value
              : "",
          })
          .then(() => {
            this.$refs["formAccountsCreature"].reset();
          });
      } catch (e) {
        const message = !e.response ? e.message : e.response.data.message;
        this.creature.error = message;
        return;
      } finally {
        this.isLoading = false;
      }
      this.creature.error = null;
      this.close();
    },
  },
};
</script>