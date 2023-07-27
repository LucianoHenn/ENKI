<template>
  <vue-good-table
    :columns="columns"
    :rows="rows"
    v-model:isLoading.sync="isLoading"
    v-on:page-change="onPageChange"
    v-on:per-page-change="onPerPageChange"
    v-on:column-filter="onColumnFilter"
    v-on:sort-change="onSortChange"
    :totalRows="totalRecords"
    :pagination-options="{
      enabled: true,
      perPage: 10,
      perPageDropdown: [5, 10, 15, 20, 50],
      mode: 'pages',
    }"
  >
    <template #table-row="props">
      <div
        v-if="props.column.field == 'actions'"
        class="dropdown b-dropdown custom-dropdown btn-group"
        id="__BVID__464"
      >
        <a
          aria-haspopup="true"
          aria-expanded="false"
          href="javascript:;"
          target="_self"
          class="btn dropdown-toggle btn-icon-only"
          data-bs-toggle="dropdown"
          boundary="body"
          id="ddlcustom"
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
            class="feather feather-more-horizontal"
          >
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="19" cy="12" r="1"></circle>
            <circle cx="5" cy="12" r="1"></circle></svg
        ></a>
        <ul
          role="menu"
          tabindex="-1"
          class="dropdown-menu dropdown-menu-right"
          aria-labelledby="ddlcustom"
          style=""
        >
          <li role="presentation">
            <a
              role="menuitem"
              href="javascript:"
              target="_self"
              class="dropdown-item"
              >Download</a
            >
          </li>
          <li role="presentation">
            <a
              role="menuitem"
              href="javascript:"
              target="_self"
              class="dropdown-item"
              >Share</a
            >
          </li>
          <li role="presentation">
            <a
              role="menuitem"
              href="javascript:"
              target="_self"
              class="dropdown-item"
              >Edit</a
            >
          </li>
          <li role="presentation">
            <a
              role="menuitem"
              href="javascript:"
              @click.prevent="deleteCampaign(props.row)"
              target="_self"
              class="dropdown-item"
              >Delete</a
            >
          </li>
        </ul>
      </div>
      <span v-else class="tableDataField">
        {{ props.formattedRow[props.column.field] }}
      </span>
    </template>
  </vue-good-table>
</template>

<script setup>
import { ref, onMounted } from "vue";
import taboolaCampaignsApi from "@/services/api/taboola/campaigns";
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
  },
  {
    label: "Brand",
    field: "branding_text",
  },
  {
    label: "CPC",
    field: "cpc",
  },
  {
    label: "Spent",
    field: "spent",
  },
  {
    label: "Conversions",
    field: "spent",
  },
  {
    label: "Clicks",
    field: "spent",
  },
  {
    label: "Is Active",
    field: "is_active",
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
    const res = await taboolaCampaignsApi.getCampaigns(serverParams.value);
    rows.value = res.data?.results.reverse();
    totalRecords.value = res.data?.results?.length;
    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

const deleteCampaign = async (option) => {
  try {
    const confirmation = await askForConfirmation(
      "Are you sure you want to delete this campaign?"
    );
    if (!confirmation.isConfirmed) return;
    const res = await taboolaCampaignsApi.deleteCampaign(option);
    showMessage("Campaign deleted succesully");
    loadItems();
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
