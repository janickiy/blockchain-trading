<template>
  <v-card flat>
    <v-card-title
      >Create {{ "operation" | pluralize(targets.length) }} for
      {{ targets.length }}
      {{ "target" | pluralize(targets.length) }}</v-card-title
    >
    <v-card-text>
      <v-form :disabled="isLoading" ref="form" lazy-validation>
        <v-row>
          <v-col cols="2">
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
          <v-col cols="2">
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
          <v-col cols="2">
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
        <v-row
          v-if="
            successCreated ||
            errorTargets.length ||
            successCreated === targets.length
          "
        >
          <v-col cols="6">
            <v-progress-linear
              v-model="progress"
              color="light-blue"
              height="25"
              striped
            >
              <template v-slot:default="{}">
                <strong>{{ progressLabel }}</strong>
              </template>
            </v-progress-linear>
          </v-col>
        </v-row>
        <v-row v-if="errorTargets.length">
          <v-col cols="6">
            <v-alert dense outlined type="error">
              Errors: {{ errorTargets.join(", ") }}
            </v-alert>
          </v-col>
        </v-row>
      </v-form>
    </v-card-text>
  </v-card>
</template>
<script>
import { formMixin } from "../mixins/form";
export default {
  mixins: [formMixin],
  props: {
    fromAccountId: {},
    targets: {
      type: Array,
    },
  },
  data: () => ({
    errorTargets: [],
    successCreated: 0,
  }),
  created() {
    this.setData({
      from_account_id: this.fromAccountId,
    });
  },
  computed: {
    progress() {
      return Math.ceil((this.successCreated * 100) / this.targets.length);
    },
    progressLabel() {
      return this.successCreated + " of " + this.targets.length;
    },
  },
  methods: {
    async buy() {
      this.setData({
        action: 1,
      });

      this.run();
    },
    async sell() {
      this.setData({
        action: 0,
      });

      this.run();
    },
    async run() {
      this.resetProgress();
      for (let i in this.targets) {
        const target = this.targets[i];
        this.setData({
          target_username: target,
        });
        try {
          await this.createOperation();
          this.successCreated++;
        } catch (e) {
          this.errorTargets.push(target);
        }
      }

      this.reset();
    },
    async createOperation() {
      return await this.create();
    },
    reset() {
      this.$refs.form.reset();
    },
    resetProgress() {
      this.successCreated = 0;
      this.errorTargets = [];
    },
  },
};
</script>

