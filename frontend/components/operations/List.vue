<template>
  <v-card :loading="isLoading">
    <v-card color="grey lighten-3">
      <v-card-text>
        <v-row>
          <v-col sm="3" cols="12"
            ><v-select
              label="Status"
              flat
              hide-details
              v-model="filter.status"
              :items="statusTypes"
              item-text="text"
              item-value="value"
              clearable
              outlined
              dense
              multiple
            ></v-select
          ></v-col>
          <v-col sm="3" cols="12"
            ><v-select
              label="From"
              flat
              hide-details
              v-model="filter.from"
              :items="accountsForFilter"
              item-text="text"
              item-value="value"
              clearable
              outlined
              dense
              multiple
            ></v-select
          ></v-col>
          <v-col sm="3" cols="12"
            ><v-select
              label="Target"
              flat
              hide-details
              v-model="filter.target"
              :items="targetsForFilter"
              item-text="text"
              item-value="value"
              clearable
              outlined
              dense
              multiple
            ></v-select
          ></v-col>
          <v-col sm="3" cols="12"
            ><v-select
              label="Cloner"
              flat
              hide-details
              v-model="filter.cloner"
              :items="clonersForFilter"
              item-text="text"
              item-value="value"
              clearable
              outlined
              dense
            ></v-select
          ></v-col>
        </v-row>
      </v-card-text>
    </v-card>
    <table-operations
      :operations="operations"
      :filtered-operations="filteredOperations"
      :filter="filter"
      :status-types="statusTypes"
      :action-types="actionTypes"
      :condition-types="conditionTypes"
      @openViewerDialog="openViewerDialog"
      @openEditDialog="openEditDialog"
      @deleteOperation="deleteOperation"
      class="mb-12"
    />
    <viewer-dialog ref="viewerDialog" />
    <edit-dialog
      ref="editDialog"
      :condition-types="conditionTypes"
      :accounts="accounts"
    />
    <create-dialog
      ref="createDialog"
      :condition-types="conditionTypes"
      :accounts="accounts"
    />
    <v-fab-transition>
      <v-btn
        color="pink"
        fab
        dark
        class="v-btn--create-record"
        @click="openDialogOperationCreate"
      >
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-fab-transition>
  </v-card>
</template>
<script>
import ViewerDialog from "./dialogs/Viewer.vue";
import CreateDialog from "./dialogs/Create.vue";
import EditDialog from "./dialogs/Edit.vue";
import TableOperations from "./Table.vue";
import { configs } from "~/configs";
export default {
  components: {
    ViewerDialog,
    CreateDialog,
    EditDialog,
    TableOperations,
  },
  data: () => ({
    isLoading: false,
    isExpanded: false,
    actionTypes: [
      { value: 1, text: "BUY", color: "green" },
      { value: 0, text: "SELL", color: "red" },
    ],
    conditionTypes: [
      { value: 0, text: "Price ~" },
      { value: 1, text: "Price =" },
      { value: 2, text: "Price >=" },
      { value: 3, text: "Price <=" },
      { value: 4, text: "FRP >=" },
      { value: 5, text: "FRP <=" },
    ],
    statusTypes: [
      { value: 0, text: "WAIT", color: "grey" },
      { value: 1, text: "IN PROCESS", color: "orange" },
      { value: 2, text: "FAILED", color: "red" },
      { value: 3, text: "SUCCESS", color: "green" },
    ],
    accounts: {
      items: [],
    },
    operations: {
      current: null,
      tableHeaders: [
        {
          text: "Created",
          value: "created_at",
          align: "center",
          width: "120px",
        },
        {
          text: "Updated",
          value: "updated_at",
          align: "center",
          width: "120px",
        },
        { text: "Cloner", value: "is_cloner", align: "center", width: "70px" },
        { text: "Status", value: "status", align: "center", width: "140px" },
        { text: "From", value: "from", align: "center", width: "120px" },
        { text: "Action", value: "action", align: "center", width: "85px" },
        { text: "Target", value: "target", align: "center", width: "120px" },
        { text: "Amount", value: "amount", align: "right", width: "100px" },
        {
          text: "Condition",
          value: "condition",
          align: "left",
          width: "200px",
        },
        { text: "Actions", value: "actions", align: "left" },
      ],
      items: [],
    },
    ws: null,
    filter: {
      status: [],
      from: [],
      target: [],
      cloner: null,
    },
  }),
  computed: {
    accountsForFilter() {
      return this.accounts.items.map((item) => {
        return { value: item.id, text: item.username };
      });
    },
    targetsForFilter() {
      const unique = (e, i, arr) => arr.indexOf(e) === i;

      return this.operations.items
        .map((item) => item.target_username)
        .filter(unique)
        .map((item) => {
          return { value: item, text: item };
        });
    },
    clonersForFilter() {
      return [
        { value: true, text: "Yes" },
        { value: false, text: "No" },
      ];
    },
    filteredOperations() {
      return this.operations.items
        .filter((item) => {
          return !item.condition_payload.operation_parent_id;
        })
        .filter((item) => {
          if (
            this.filter.status.length &&
            this.filter.status.indexOf(item.status) === -1
          ) {
            return false;
          }
          if (
            this.filter.from.length &&
            this.filter.from.indexOf(item.from_account_id) === -1
          ) {
            return false;
          }
          if (
            this.filter.target.length &&
            this.filter.target.indexOf(item.target_username) === -1
          ) {
            return false;
          }
          if (
            this.filter.cloner !== null &&
            this.filter.cloner !== item.is_cloner
          ) {
            return false;
          }
          return true;
        });
    },
  },
  created() {
    this.isLoading = true;

    this.fetchAccounts();
    this.fetcherOperations();
    this.ws = setInterval(() => {
      this.fetcherOperations();
    }, 1000);
  },
  beforeDestroy() {
    if (this.ws) {
      clearInterval(this.ws);
    }
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
    openEditDialog(item) {
      this.$refs.editDialog.open(item);
    },
    openDialogOperationCreate() {
      this.$refs.createDialog.open();
    },
    openViewerDialog(item) {
      this.$refs.viewerDialog.open(item);
    },
    fetchAccounts() {
      this.$axios.$get(`${configs.apiUrl}v1/accounts`).then((data) => {
        this.accounts.items = data;
      });
    },
    fetcherOperations() {
      this.$axios
        .$get(`${configs.apiUrl}v1/operations`)
        .then((data) => {
          this.operations.items = data;
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    async deleteOperation(item) {
      try {
        await this.$axios
          .$delete(`${configs.apiUrl}v1/operations/${item.id}`)
          .then(() => {
            this.operations.items = this.operations.items.filter(
              (operation) => operation.id !== item.id
            );
          });
      } catch (e) {
        console.log(e.response);
      }
    },
  },
};
</script>
