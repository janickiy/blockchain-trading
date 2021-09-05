import {
  configs
} from "~/configs";

export const formMixin = {
  data: () => ({
    error: null,
    isLoading: false,
    creature: {
      fields: {
        id: {
          value: null,
        },
        status: {
          value: 0,
        },
        isCloner: {
          value: false,
        },
        fromAccount: {
          value: null,
          rules: [(v) => !!v || "The field is required."],
        },
        action: {
          value: null,
          rules: [(v) => typeof v === "number" || "The field is required."],
        },
        targetUsername: {
          value: null,
          rules: [(v) => !!v || "The field is required."],
        },
        amountType: {
          value: 0,
        },
        amount: {
          value: null,
          rules: [(v) => !!Number(v) || "The field is required."],
        },
        condition: {
          value: null,
          rules: [(v) => typeof v === "number" || "The field is required."],
        },
        conditionPayloadValue: {
          value: null,
          rules: [(v) => !!Number(v) || "The field is required."],
        },
        conditionPayloadAbtPercent: {
          value: null,
          rules: [(v) => !!Number(v) || "The field is required."],
        },
        conditionPayloadClonerAction: {
          value: null,
          rules: [(v) => typeof v === "number" || "The field is required."],
        },
      },
    },
    conditionTypes: [{
        value: 0,
        text: "Price ~"
      },
      {
        value: 1,
        text: "Price ="
      },
      {
        value: 2,
        text: "Price >="
      },
      {
        value: 3,
        text: "Price <="
      },
      {
        value: 4,
        text: "FRP >="
      },
      {
        value: 5,
        text: "FRP <="
      },
    ],
    actionTypes: [{
        value: 1,
        text: "BUY",
        color: "green"
      },
      {
        value: 0,
        text: "SELL",
        color: "red"
      },
    ],
  }),
  watch: {
    isLoading() {
      this.$emit('loading', this.isLoading);
    }
  },
  computed: {
    amountTypeLabel() {
      return this.creature.fields.amountType.value === 0 ? 'coins' : 'percent';
    }
  },
  methods: {
    changeAmountType() {
      this.creature.fields.amountType.value = this.creature.fields.amountType.value === 0 ? 1 : 0;
    },
    getData() {
      if (!this.$refs["form"].validate()) {
        return;
      }

      let formData = Object.assign({}, this.creature.fields);

      let conditionPayload = {};
      conditionPayload.value = parseFloat(formData.conditionPayloadValue.value);
      if (formData.condition.value === 0) {
        conditionPayload.percent = parseFloat(
          formData.conditionPayloadAbtPercent.value
        );
      }
      if (formData.isCloner.value) {
        conditionPayload.action = formData.conditionPayloadClonerAction.value;
      }

      formData.conditionPayload = conditionPayload;

      return formData;
    },
    setData(data) {
      if (data.id) {
        this.creature.fields.id.value = data.id;
      }
      if (data.status) {
        this.creature.fields.status.value = data.status;
      }
      if (data.is_cloner && typeof data.is_cloner === 'boolean') {
        this.creature.fields.isCloner.value = data.is_cloner;
      }
      if (data.from_account_id) {
        this.creature.fields.fromAccount.value = data.from_account_id;
      }
      if (data.action || data.action === 0) {
        this.creature.fields.action.value = data.action;
      }
      if (data.target_username) {
        this.creature.fields.targetUsername.value = data.target_username;
      }
      if (data.amount_type) {
        this.creature.fields.amountType.value = data.amount_type;
      }
      if (data.amount) {
        this.creature.fields.amount.value = data.amount;
      }
      if (data.condition || data.condition === 0) {
        this.creature.fields.condition.value = data.condition;
      }
      if (data.condition_payload) {
        this.creature.fields.conditionPayloadValue.value =
          data.condition_payload.value;
        if (data.condition_payload.percent !== "undefined") {
          this.creature.fields.conditionPayloadAbtPercent.value =
            data.condition_payload.percent;
        }
        if (data.condition_payload.action !== "undefined") {
          this.creature.fields.conditionPayloadClonerAction.value =
            data.condition_payload.action;
        }
      }
    },
    setError(error) {
      this.error = error;
    },
    reset() {
      this.setError(null);
      this.$refs.form.reset();
    },
    async create() {
      let formData = this.getData();

      if (!formData) {
        throw new Error('Validation fail');
      }

      this.isLoading = true;

      try {
        return await this.$axios
          .$post(`${configs.apiUrl}v1/operations`, {
            from_account_id: formData.fromAccount.value,
            action: formData.action.value,
            target_username: formData.targetUsername.value,
            amount_type: formData.amountType.value,
            amount: formData.amount.value,
            condition: formData.condition.value,
            condition_payload: formData.conditionPayload,
            is_cloner: !!formData.isCloner.value,
          });
      } catch (e) {
        const message = !e.response ? e.message : e.response.data.message;
        this.setError(message);
        throw new Error(message);
      } finally {
        this.isLoading = false;
      }
    },
    async save() {
      let formData = this.getData();

      if (!formData) {
        throw new Error('Validation fail');
      }

      this.isLoading = true;

      try {
        return await this.$axios.$put(`${configs.apiUrl}v1/operations/${formData.id.value}`, {
          from_account_id: formData.fromAccount.value,
          action: formData.action.value,
          target_username: formData.targetUsername.value,
          amount_type: formData.amountType.value,
          amount: formData.amount.value,
          condition: formData.condition.value,
          condition_payload: formData.conditionPayload,
          is_cloner: !!formData.isCloner.value,
          status: formData.status.value,
        });
      } catch (e) {
        const message = !e.response ? e.message : e.response.data.message;
        this.setError(message);
        throw new Error(message);
      } finally {
        this.isLoading = false;
      }
    },
  }
}
