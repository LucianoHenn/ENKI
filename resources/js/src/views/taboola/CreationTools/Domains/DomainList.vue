<template>
  <vue-good-table
      mode="remote"
      :columns="columns"
      :rows="rows"
      v-model:isLoading.sync="isLoading"
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
          v-if="props.column.field == 'actions'"
          class="d-flex gap-2 justify-content-center"
      >
        <a href="#" @click.prevent="$emit('editDomain', props.row)"
        ><svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="feather feather-edit-2"
        >
            <path
                d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"
            ></path></svg
        ></a>
        <a href="#" @click.prevent="deleteDomain(props.row)"
        ><svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="feather feather-trash-2"
        >
            <polyline points="3 6 5 6 21 6"></polyline>
            <path
                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
            ></path>
            <line x1="10" y1="11" x2="10" y2="17"></line>
            <line x1="14" y1="11" x2="14" y2="17"></line></svg
        ></a>
      </span>
      <span v-else class="tableDataField">
        {{ props.formattedRow[props.column.field] }}
      </span>
    </template>

    <template #table-actions>
      <div class="px-3 py-2 d-flex gap-2">
        <button class="btn btn-primary" @click="$emit('addDomain')">
          Add Domain
        </button>
      </div>
    </template>
  </vue-good-table>
</template>

<script setup>
import { ref, onMounted } from "vue";
import taboolaDomainsApi from "@/services/api/taboola/domains";
import { showMessage, askForConfirmation } from "@/utils/toast";
const emit = defineEmits(["editDomain", "addDomain"]);

let rows = ref([]);
let isLoading = ref(false);
let totalRecords = ref(0);
let serverParams = ref({
  currentPerPage: 10,
  page: 1,
  perPage: 10,
});

const columns = ref([
  {
    label: "ID",
    field: "id",
  },
  {
    label: "Name",
    field: "name",
    filterOptions: {
      enabled: true,
      placeholder: "Name",
      trigger: "enter",
    },
  },
  {
    label: "URL",
    field: "url",
  },
  // {
  //   label: "Partnership",
  //   field: "partnership",
  //   formatFn: (value) => {
  //     return value ? value.name : "";
  //   },
  //   filterOptions: {
  //     enabled: true,
  //     placeholder: "Partnership",
  //     trigger: "enter",
  //   },
  // },
  {
    label: "Status",
    field: "status",
    filterOptions: {
      enabled: true,
      placeholder: "All Statuses",
      filterDropdownItems: [
        {
          text: "Active",
          value: "active",
        },
        {
          text: "Inactive",
          value: "inactive",
        },
      ],
    },
  },
  {
    label: "Created On",
    field: "created_at",
  },
  {
    label: "Actions",
    field: "actions",
    sortable: false,
    thClass: "text-center",
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
    const res = await taboolaDomainsApi.getDomains(serverParams.value);
    rows.value = res.data;
    totalRecords.value = res.meta.total;
    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

const deleteDomain = async (option) => {
  try {
    const confirmation = await askForConfirmation(
        "Are you sure you want to delete this domain?"
    );
    if (!confirmation.isConfirmed) return;
    const res = await taboolaDomainsApi.deleteDomain(option);
    showMessage("Domain deleted succesully");
    loadItems();
    onSuccess && onSuccess(response);
  } catch (error) {
    if (error?.response?.status === 404) {
      const errorMessage = error?.response?.data?.message;
      showMessage(errorMessage, "error");
    }
  }
};

defineExpose({ loadItems });
onMounted(loadItems);
</script>
