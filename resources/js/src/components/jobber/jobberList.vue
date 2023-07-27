<template>
  <vue-good-table
    mode="remote"
    :columns="columns"
    :rows="rows"
    :isLoading.sync="isLoading"
    v-on:page-change="onPageChange"
    v-on:per-page-change="onPerPageChange"
    :totalRows="totalRecords"
    v-on:column-filter="onColumnFilter"
    v-on:sort-change="onSortChange"
    :pagination-options="{
      enabled: true,
      perPage: 10,
      perPageDropdown: [5, 10, 15, 20, 50],
      mode: 'pages',
    }"
  >
    <template #table-row="props">
      <span
        class="pl-3 py-2 d-flex gap-2"
        v-if="props.column.field == 'actions'"
      >
        <button
          class="btn btn-outline-primary"
          @click="$emit('showLogs', props.row.id)"
        >
          View Logs
        </button>
        <a
          v-if="props.row.file"
          :href="props.row.file"
          class="btn btn-success me-2"
          role="button"
        >
          Download File
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            style="margin-left: 10px"
            class="feather feather-download"
          >
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
        </a>
      </span>
      <span v-else class="tableDataField">
        {{ props.formattedRow[props.column.field] }}
      </span>
    </template>

    <template #table-actions>
      <div class="px-3 py-2 d-flex gap-2" v-if="!props.hideButton">
        <button class="btn btn-primary" @click="$emit('addJobber')">
          New Request
        </button>
      </div>
    </template>
  </vue-good-table>
</template>

<script setup>
import { ref, onMounted } from "vue";
import jobberApi from "@/services/api/jobber";
import { showMessage } from "@/utils/toast";

const emit = defineEmits(["addJobber", "showLogs"]);

const props = defineProps({
  className: String,
  hideButton: {
    type: Boolean,
    default: false,
  },
});

let rows = ref([]);
let isLoading = ref(false);
let totalRecords = ref(0);
let serverParams = ref({
  currentPerPage: 10,
  page: 1,
  perPage: 10,
  "filters.class": props.className,
});

const columns = ref([
  {
    field: "id",
    label: "Id",
    filterOptions: {
      enabled: true,
      trigger: "enter",
    },
  },
  {
    field: "creator",
    label: "Creator",
    formatFn: function (creator) {
      return creator.name;
    },
    filterOptions: {
      enabled: true,
      trigger: "enter",
    },
  },
  {
    field: "description",
    label: "Description",
    filterOptions: {
      enabled: true,
      trigger: "enter",
    },
  },
  {
    field: "created_at",
    label: "Created At",
    formatFn: function (created_at_date) {
      const [date, time] = created_at_date.split("T");
      return `${date}  ${time.slice(0, 5)}`;
    },
  },
  {
    field: "status",
    label: "Status",
  },
  {
    field: "actions",
    label: "Actions",
  },
]);

const onPageChange = (params) => {
  updateParams({ page: params.currentPage });
};

const onPerPageChange = (params) => {
  updateParams({ perPage: params.currentPerPage });
};

const onColumnFilter = (params) => {
  updateParams(params);
};

const onSortChange = (params) => {
  updateParams({ sorts: params });
};

const updateParams = (params) => {
  serverParams.value = Object.assign({}, serverParams.value, params);
  loadItems();
};

const loadItems = async () => {
  isLoading.value = true;
  try {
    const res = await jobberApi.getJobbers(serverParams.value);
    rows.value = res.data;
    totalRecords.value = res.total;
    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
  }
};

defineExpose({ loadItems });
onMounted(() => {
  loadItems();
});
</script>
