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
        <a href="#" @click.prevent="$emit('editTemplate', props.row)"
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
        <a href="#" @click.prevent="duplicateTemplate(props.row)"
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
            class="feather feather-copy"
        >
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
            <path
                d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"
            /></svg
        ></a>
        <a href="#" @click.prevent="deleteTemplate(props.row)"
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
      <span v-else-if="props.column.field == 'category_id'">
        {{ props.row.category ? props.row.category.name : "" }}
      </span>
      <span v-else-if="props.column.field == 'language_id'">
        {{
          props.row.language
              ? props.row.language.name + "(" + props.row.language.code + ")"
              : null
        }}
      </span>
      <span v-else-if="props.column.field == 'country_id'">
        {{
          props.row.countries?.length > 0
              ? props.row.countries.map((object) => object.name).join(", ")
              : null
        }}
      </span>
      <span v-else class="tableDataField">
        {{ props.formattedRow[props.column.field] }}
      </span>
    </template>

    <template #table-actions>
      <div class="px-3 py-2 d-flex gap-2">
        <button class="btn btn-primary" @click="$emit('addTemplate')">
          Add New Template
        </button>
      </div>
    </template>
  </vue-good-table>
</template>

<script setup>
import { ref, onMounted } from "vue";
import templatesApi from "@/services/api/taboola/templates";
import { showMessage, askForConfirmation } from "@/utils/toast";
import languageApi from "@/services/api/languages";
import categoryApi from "@/services/api/categories";
import countryApi from "@/services/api/countries";
const emit = defineEmits(["editTemplate", "addTemplate"]);

let rows = ref([]);
let isLoading = ref(false);
let totalRecords = ref(0);
let serverParams = ref({
  currentPerPage: 10,
  page: 1,
  perPage: 10,
});
const languageOptions = ref([]);
const categoryOptions = ref([]);
const countryOptions = ref([]);

const columns = ref([
  {
    label: "ID",
    field: "id",
  },
  {
    label: "Description",
    field: "description",
    filterOptions: {
      enabled: true,
      placeholder: "Description",
      trigger: "enter",
    },
  },
  {
    label: "Language",
    field: "language_id",
    filterOptions: {
      enabled: true,
      placeholder: "All languages",
      filterDropdownItems: languageOptions,
    },
  },
  {
    label: "Category",
    field: "category_id",
    filterOptions: {
      enabled: true,
      placeholder: "All categories",
      filterDropdownItems: categoryOptions,
    },
  },
  {
    label: "Countries",
    field: "country_id",
    filterOptions: {
      enabled: true,
      placeholder: "All Countries",
      filterDropdownItems: countryOptions,
    },
  },
  {
    label: "Created At",
    field: "created_at",
  },
  {
    label: "Updated At",
    field: "updated_at",
  },
  {
    label: "Actions",
    field: "actions",
    sortable: false,
    thClass: "text-center",
  },
]);

const filterFn = (data, filterString) => {
  return data ? data.includes(filterString) : false;
};

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
    const res = await templatesApi.getTemplates(serverParams.value);
    rows.value = res.data;
    totalRecords.value = res.meta.total;
    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

// The same as the edit modal, but if we delete the id, then when submiting a new one will be  created
const duplicateTemplate = (row) => {
  const copyWithoutId = { ...row };
  delete copyWithoutId.id;
  copyWithoutId.description += " (CLONE)";
  emit("editTemplate", copyWithoutId);
};

const deleteTemplate = async (option) => {
  try {
    const confirmation = await askForConfirmation(
        "Are you sure you want to delete this option?"
    );
    if (!confirmation.isConfirmed) return;
    const res = await templatesApi.deleteTemplate(option);
    showMessage("Template deleted successfully");
    loadItems();
    onSuccess && onSuccess(response);
  } catch (error) {
    if (error?.response?.status === 404) {
      const errorMessage = error?.response?.data?.message;
      showMessage(errorMessage, "error");
    }
  }
};

const getCategoryOptions = async () => {
  try {
    const res = await categoryApi.getCategories();
    let categories = res.data.map((category) => {
      return {
        text: category.name,
        value: category.id,
      };
    });
    categories.splice(0, 0, {
      text: "No categories",
      value: 0,
    });
    categoryOptions.value = categories;
  } catch (error) {
    showMessage(error.message, "error");
  }
};

const getLanguageOptions = async () => {
  try {
    const res = await languageApi.getLanguages();
    languageOptions.value = res.data.map((language) => {
      return {
        text: language.name,
        value: language.id,
      };
    });
  } catch (error) {
    showMessage(error.message, "error");
  }
};

const getCountryOptions = async () => {
  try {
    const res = await countryApi.getCountries();
    countryOptions.value = res.data.map((country) => {
      return {
        text: country.name,
        value: country.id,
      };
    });
  } catch (error) {
    showMessage(error.message, "error");
  }
};

defineExpose({ loadItems });
onMounted(() => {
  loadItems();
  getCategoryOptions();
  getLanguageOptions();
  getCountryOptions();
});
</script>
