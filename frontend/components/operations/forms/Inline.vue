<template>
  <v-form :disabled="isLoading" ref="form" lazy-validation>
    <v-row>
      <v-col cols="3">
        <v-select
          v-model="creature.fields.condition.value"
          :items="conditionTypes"
          :rules="creature.fields.condition.rules"
          dense
          label="Condition"
          outlined
          hide-details
        ></v-select>
      </v-col>
      <v-col cols="3">
        <v-text-field
          v-model="creature.fields.conditionPayloadValue.value"
          :rules="creature.fields.conditionPayloadValue.rules"
          dense
          label="Cond. value"
          outlined
          type="number"
          min="0"
          placeholder="0.01"
          hide-details
        ></v-text-field>
      </v-col>
      <v-col cols="2" v-if="creature.fields.condition.value === 0">
        <v-text-field
          v-model="creature.fields.conditionPayloadAbtPercent.value"
          :rules="creature.fields.conditionPayloadAbtPercent.rules"
          dense
          label="Cond. percent"
          outlined
          type="number"
          min="0"
          max="100"
          step="0.01"
          placeholder="42.24 = 42.24%"
          hide-details
        ></v-text-field>
      </v-col>
      <v-col cols="3">
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
          hide-details
        >
          <template v-slot:append>
            <v-btn class="ml-1" x-small @click="changeAmountType">{{
              amountTypeLabel
            }}</v-btn>
          </template>
        </v-text-field>
      </v-col>
      <v-col>
        <v-btn color="green" dark @click="buy()" :disabled="isLoading"
          >Buy</v-btn
        >
        <v-btn class="red" dark @click="sell()" :disabled="isLoading"
          >Sell</v-btn
        >
      </v-col>
    </v-row>
  </v-form>
</template>
<script>
import { formMixin } from "../mixins/form";
export default {
  mixins: [formMixin],
  props: {
    fromAccountId: {},
    targetUsername: {},
  },
  created() {
    this.setData({
      from_account_id: this.fromAccountId,
      target_username: this.targetUsername,
    });
  },
  methods: {
    async buy() {
      this.setData({
        action: 1,
      });
      await this.createOperation();
    },
    async sell() {
      this.setData({
        action: 0,
      });
      await this.createOperation();
    },
    async createOperation() {
      try {
        await this.create();
      } catch (e) {
        return;
      }
      this.$refs.form.reset();
    },
  },
};
</script>
