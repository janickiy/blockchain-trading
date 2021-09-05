<template>
  <v-data-table
    :headers="operations.tableHeaders"
    :items="filteredOperations"
    :loading="operations.isLoading"
    disable-sort
    :footer-props="{ itemsPerPageOptions: [10, 20, 100] }"
    flat
    :hide-default-footer="isChild && filteredOperations.length < 10"
    show-expand
  >
    <template v-slot:item.created_at="{ item }">
      {{ formattedDate(item.created_at) }}
    </template>
    <template v-slot:item.updated_at="{ item }">
      {{ formattedDate(item.updated_at) }}
    </template>
    <template v-slot:item.is_cloner="{ item }">
      <div v-if="!isChild">
        <v-icon v-if="item.is_cloner">mdi-check</v-icon>
        <template v-else>-</template>
      </div>
    </template>
    <template v-slot:item.status="{ item }">
      <v-progress-circular
        v-if="item.is_cloner && hasClonedInProccess(item.id)"
        indeterminate
        color="amber"
      ></v-progress-circular>
      <v-chip
        v-else
        :color="getStatus(item.status).color"
        small
        dark
        label
        loading
      >
        <strong>{{ getStatus(item.status).text }}</strong>
      </v-chip>
    </template>
    <template v-slot:item.from="{ item }">
      <v-btn
        text
        small
        :href="`https://bitclout.com/u/${
          item.account.username
        }`"
        target="_blank"
      >
        {{ item.account.username }}
      </v-btn>
    </template>
    <template v-slot:item.action="{ item }">
      <v-chip :color="getAction(item.action).color" small dark label>
        <strong>{{ getAction(item.action).text }}</strong>
      </v-chip>
    </template>
    <template v-slot:item.target="{ item }">
      <v-btn
        text
        small
        :href="`https://bitclout.com/u/${item.target_username}`"
        target="_blank"
      >
        {{ item.target_username }}
      </v-btn>
    </template>
    <template v-slot:item.condition="{ item }">
      <v-list-item class="pl-0">
        <v-list-item-content>
          <v-list-item-title>
            {{ getCondition(item.condition).text }}
            <template v-if="item.condition === 0">
              {{
                getMinPriceForPercent(
                  item.condition_payload.value,
                  item.condition_payload.percent
                )
              }}
              &lt;=>
              {{
                getMaxPriceForPercent(
                  item.condition_payload.value,
                  item.condition_payload.percent
                )
              }}
            </template>
            <template v-else>
              {{ item.condition_payload.value
              }}<template v-if="isOperationFrp(item)">%</template>
            </template>
          </v-list-item-title>
          <v-list-item-subtitle v-if="'last_value' in item.condition_payload">
            Last: {{ item.condition_payload.last_value
            }}<template v-if="isOperationFrp(item)">%</template>
          </v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
    </template>
    <template v-slot:item.actions="{ item }">
      <v-btn icon :disabled="!item.result" @click="openViewerDialog(item)">
        <v-icon>mdi-eye-outline</v-icon>
      </v-btn>
      <v-btn
        icon
        v-if="!isForbiddenEditOperation(item)"
        @click="openEditDialog(item)"
      >
        <v-icon>mdi-square-edit-outline</v-icon>
      </v-btn>
      <v-btn
        icon
        :disabled="isForbiddenDeleteOperation(item)"
        @click="deleteOperation(item)"
      >
        <v-icon>mdi-delete</v-icon>
      </v-btn>
    </template>
    <template v-slot:item.data-table-expand="{ item, isExpanded, expand }">
      <div v-if="!isChild">
        <v-btn
          @click="expand(true)"
          v-if="getCloned(item.id).length && !isExpanded"
          icon
          ><v-icon>mdi-menu-down</v-icon></v-btn
        >
        <v-btn
          @click="expand(false)"
          v-if="getCloned(item.id).length && isExpanded"
          icon
          ><v-icon>mdi-menu-up</v-icon></v-btn
        >
      </div>
      <v-btn icon disabled v-else>
        <v-icon>mdi-menu-right</v-icon>
      </v-btn>
    </template>
    <template v-if="!isChild" v-slot:expanded-item="{ headers, item }">
      <td :colspan="headers.length" class="pa-0">
        <table-operations
          :operations="operations"
          :filtered-operations="getCloned(item.id)"
          :is-child="true"
          :status-types="statusTypes"
          :action-types="actionTypes"
          :condition-types="conditionTypes"
          class="grey lighten-3"
          @openViewerDialog="openViewerDialog"
          @openEditDialog="openEditDialog"
          @deleteOperation="deleteOperation"
        />
      </td>
    </template>
  </v-data-table>
</template>
<script>
export default {
  components: {
    TableOperations: () => import("./Table.vue"),
  },
  props: {
    isChild: {
      type: Boolean,
      default: false,
    },
    operations: {
      type: Object,
    },
    filteredOperations: {},
    statusTypes: {},
    actionTypes: {},
    conditionTypes: {},
  },
  methods: {
    getCloned(id) {
      return this.operations.items.filter((item) => {
        return (
          item.condition_payload.operation_parent_id &&
          item.condition_payload.operation_parent_id === id
        );
      });
    },
    hasClonedInProccess(id) {
      return this.getCloned(id).filter(o => o.status === 1).length;
    },
    getCondition(value) {
      return this.conditionTypes.find((condition) => condition.value === value);
    },
    isForbiddenEditOperation(item) {
      return item.status !== 0;
    },
    isForbiddenDeleteOperation(item) {
      return item.status === 1;
    },
    isOperationFrp(item) {
      return item.condition === 4 || item.condition === 5;
    },
    getMinPriceForPercent(price, percent) {
      return (price - (price / 100) * percent).toFixed(2);
    },
    getMaxPriceForPercent(price, percent) {
      return (price + (price / 100) * percent).toFixed(2);
    },
    formattedDate(value) {
      return `${value.replace(/T/, " ").replace(/\..+/, "")} UTC`;
    },
    getAction(value) {
      return this.actionTypes.find((action) => action.value === value);
    },
    getStatus(value) {
      return this.statusTypes.find((status) => status.value === value);
    },
    openViewerDialog(item) {
      this.$emit("openViewerDialog", item);
    },
    openEditDialog(item) {
      this.$emit("openEditDialog", item);
    },
    deleteOperation(item) {
      this.$emit("deleteOperation", item);
    },
  },
};
</script>
