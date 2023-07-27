<template>
  <div class="mt-4"></div>
  <div class="px-3 py-2 d-flex gap-2 fixedHeader">
    <button class="btn btn-primary" @click="exportToCSV">
      Download as CSV
    </button>
    <button class="btn btn-primary" @click="$emit('associateCategories')">
      Associate Categories
    </button>
    <button class="btn btn-primary" @click="$emit('bulkEditKeyword')">
      Edit Keywords
    </button>
    <button class="btn btn-primary" @click="$emit('addKeyword')">
      Add Keyword
    </button>
  </div>
  <vue-good-table
    mode="remote"
    :columns="columns"
    :rows="rows"
    v-model:isLoading.sync="isLoading"
    v-on:page-change="onPageChange"
    v-on:per-page-change="onPerPageChange"
    v-on:selected-rows-change="onSelectionChanged"
    v-on:column-filter="onColumnFilter"
    v-on:sort-change="onSortChange"
    :totalRows="totalRecords"
    :select-options="{
      enabled: true,
      selectOnCheckboxOnly: true,
      selectionInfoClass: 'text-primary',
      clearSelectionText: 'Clear Selection',
      selectAllByGroup: true,
    }"
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
        <a
          href="javascript:void"
          class="btn btn-primary"
          @click="$emit('viewKeyword', props.row)"
          >Open</a
        >
        <a href="#" @click.prevent="deleteKeyword(props.row)"
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
      <span v-else-if="props.column.field == 'images'">
        {{ props.row.images.length }}
      </span>
      <span v-else-if="props.column.field == 'category_id'">
        {{ props.row.category ? props.row.category.name : "" }}
      </span>
      <span v-else-if="props.column.field == 'country_id'">
        {{ props.row.country.name }}
        <span class="text-uppercase">({{ props.row.country.code }})</span>
      </span>
      <span v-else-if="props.column.field == 'language_id'">
        {{ props.row.language.name }} ({{ props.row.language.code }})
      </span>
      <span v-else>
        {{ props.formattedRow[props.column.field] }}
      </span>
    </template>
  </vue-good-table>
</template>

<style>
.fixedHeader {
  position: fixed;
  background: white !important;
  width: 100%;
  right: 0;
  top: 104px;
  z-index: 1;
  padding-top: 10px;
  justify-content: end;
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import imageApi from "@/services/api/database/keywords";
import languageApi from "@/services/api/languages";
import countryApi from "@/services/api/countries";
import categoryApi from "@/services/api/categories";
import keywordApi from "@/services/api/database/keywords";
import { showMessage, askForConfirmationWithHtml } from "@/utils/toast";

const emit = defineEmits([
  "editKeyword",
  "addKeyword",
  "bulkEditKeyword",
  "viewKeyword",
  "selectedRows",
]);

let rows = ref([]);
let isLoading = ref(false);
let totalRecords = ref(0);
let serverParams = ref({
  currentPerPage: 10,
  page: 1,
  perPage: 10,
});
const countryOptions = ref([]);
const languageOptions = ref([]);
const categoryOptions = ref([]);

const columns = ref([
  {
    label: "Keyword",
    field: "keyword",
    filterOptions: {
      enabled: true,
      placeholder: "Keyword",
      trigger: "enter",
    },
  },
  {
    label: "English Translation",
    field: "english_translation",
    filterOptions: {
      enabled: true,
      placeholder: "English Translation",
      trigger: "enter",
    },
  },
  {
    label: "Country",
    field: "country_id",
    filterOptions: {
      enabled: true,
      placeholder: "All countries",
      filterDropdownItems: countryOptions,
    },
    width: "20%",
  },
  {
    label: "Language",
    field: "language_id",
    filterOptions: {
      enabled: true,
      placeholder: "All languages",
      filterDropdownItems: languageOptions,
    },
    width: "20%",
  },
  {
    label: "Category",
    field: "category_id",
    filterOptions: {
      enabled: true,
      placeholder: "All categories",
      filterDropdownItems: categoryOptions,
    },
    width: "20%",
  },
  {
    label: "Images",
    field: "images",
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
    const res = await imageApi.getKeywords(serverParams.value);
    rows.value = res.data;
    totalRecords.value = res.meta.total;
    isLoading.value = false;
  } catch (error) {
    showMessage(error.message, "error");
  }
};

const exportToCSV = () => {
  let csv = "Keyword,English Translation,Country,Language,Category,Images\n";
  let data;
  csv =
    csv +
    rows.value
      .map((row) => {
        data = row.keyword;
        data += "," + row.english_translation;
        data +=
          "," + row.country.name + "(" + row.country.code.toUpperCase() + ")";
        data += "," + row.language.name + "(" + row.language.code + ")";
        data += "," + (row.category ? row.category.name : "");
        data += "," + row.images_count;
        return data;
      })
      .join("\n");
  let file = new Blob([csv], { type: "text/csv" });
  let link = document.createElement("a");
  link.href = URL.createObjectURL(file);
  link.download = "data.csv";
  link.click();
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

const onSelectionChanged = (data) => {
  const ids = data.selectedRows.map((row) => row.id);
  emit("selectedRows", ids);
};

const deleteKeyword = async (keyword) => {
  try {
    let res = await keywordApi.checkImagesRelated(keyword);
    let html = "";
    if (res.data.length) {
      let text = res.data.length > 1 ? "These images" : "This image";
      html +=
        '<div class="swal2-html-container" id="swal2-html-container" style="display: block;">' +
        text +
        ' will have no keyword associated</div><div class="row gap-3 w-100 px-3">';
      res.data.forEach((e) => {
        html +=
          ' <div class="col-md-3 border border-1 rounded-1 p-1 image-item position-relative"><img src="' +
          e.url +
          '" class="img-fluid" alt="' +
          e.hash +
          '" /></div>';
      });
      html += "</div>";
    }

    const confirmation = await askForConfirmationWithHtml(html);
    if (!confirmation.isConfirmed) return;
    res = await keywordApi.deleteKeyword(keyword);
    showMessage(res.message);
    loadItems();
  } catch (error) {
    showMessage(error.message, "error");
  }
};

defineExpose({ loadItems });
onMounted(() => {
  loadItems();
  getCountryOptions();
  getLanguageOptions();
  getCategoryOptions();
});
</script>
