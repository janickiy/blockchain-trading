<template>
  <v-form :disabled="isLoading" ref="form" lazy-validation>
    <input type="hidden" name="id" v-model="creature.fields.id" />
    <input type="hidden" name="status" v-model="creature.fields.status" />
    <v-alert
      type="error"
      transition="fade-transition"
      class="mb-6"
      text
      border="left"
      :value="!!error"
    >
      {{ error }}
    </v-alert>
    <v-row>
      <v-col cols="12" class="py-0">
        <v-switch
          v-model="creature.fields.isCloner.value"
          label="Cloner"
        ></v-switch>
      </v-col>
      <v-col cols="12" md="6" xs="12">
        <v-select
          v-model="creature.fields.fromAccount.value"
          :items="accountsForSelectItems"
          :rules="creature.fields.fromAccount.rules"
          dense
          label="From"
          outlined
        ></v-select>
      </v-col>
      <v-col cols="12" md="6" xs="12">
        <v-select
          v-model="creature.fields.action.value"
          :items="actionTypes"
          :rules="creature.fields.action.rules"
          dense
          label="Action"
          outlined
        ></v-select>
      </v-col>
      <v-col cols="12" md="6" xs="12">
        <v-text-field
          v-model="creature.fields.targetUsername.value"
          :rules="creature.fields.targetUsername.rules"
          dense
          label="Target"
          outlined
          placeholder="Username"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="6" xs="12">
        <v-text-field
          v-model="creature.fields.amount.value"
          :rules="creature.fields.amount.rules"
          dense
          label="Amount"
          outlined
          type="number"
          :placeholder="
            creature.fields.amountType.value === 0 ? '0.000001' : '%'
          "
          :step="creature.fields.amountType.value === 0 ? 0.000001 : 1"
          min="0"
        >
          <template v-slot:append>
            <v-btn class="ml-1" x-small @click="changeAmountType">{{
              amountTypeLabel
            }}</v-btn>
          </template>
        </v-text-field>
      </v-col>
      <v-col cols="12" md="6" xs="12" v-if="creature.fields.isCloner.value">
        <v-select
          v-model="creature.fields.conditionPayloadClonerAction.value"
          :items="actionTypes"
          :rules="creature.fields.conditionPayloadClonerAction.rules"
          dense
          label="Condition action"
          outlined
        ></v-select>
      </v-col>
      <v-col cols="12" md="6" xs="12">
        <v-select
          v-model="creature.fields.condition.value"
          :items="conditionTypes"
          :rules="creature.fields.condition.rules"
          dense
          label="Condition"
          outlined
        ></v-select>
      </v-col>
      <v-col cols="12" md="6" xs="12">
        <v-text-field
          v-model="creature.fields.conditionPayloadValue.value"
          :rules="creature.fields.conditionPayloadValue.rules"
          dense
          label="Condition value"
          outlined
          type="number"
          min="0"
          placeholder="0.01"
        ></v-text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        xs="12"
        v-if="creature.fields.condition.value === 0"
      >
        <v-text-field
          v-model="creature.fields.conditionPayloadAbtPercent.value"
          :rules="creature.fields.conditionPayloadAbtPercent.rules"
          dense
          label="Condition value percent"
          outlined
          type="number"
          min="0"
          max="100"
          step="0.01"
          placeholder="42.24 = 42.24%"
        ></v-text-field>
      </v-col>
    </v-row>
  </v-form>
</template>
<script>
import { formMixin } from "../mixins/form";
export default {
  mixins: [formMixin],
  props: ["accounts"],
  computed: {
    accountsForSelectItems() {
      return this.accounts.items.map((account) => ({
        value: account.id,
        text: account.username,
      }));
    },
  },
  methods: {},
};
</script>