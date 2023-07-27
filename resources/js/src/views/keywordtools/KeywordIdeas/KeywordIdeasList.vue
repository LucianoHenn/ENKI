<template>
  <div class="px-3 py-2 d-flex gap-2 flex-row-reverse">
    <button
      class="btn btn-primary"
      @click="$emit('addKeywords', selectedKeywordIdeas)"
    >
      Add Keywords
    </button>
  </div>

  <form class="px-3 py-2">
    <div class="input-group mb-2">
      <span class="input-group-text">Keywords</span>
      <input
        type="text"
        class="form-control"
        placeholder="Keywords to search (comma separated)"
        v-model="keywords"
      />

      <!-- <select v-model="language" class="form-select">
        <option selected value="">Choose a language</option>
        <option
          v-for="option in languageOptions"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select> -->
    </div>
    <div class="row">
      <div class="col-10">
        <multiselect
          v-model="country"
          :options="countryOptions"
          :allowEmpty="false"
          :searchable="true"
          :preselect-first="true"
          :label="'name'"
          :trackBy="'id'"
          selected-label=""
          select-label=""
          deselect-label=""
        ></multiselect>
      </div>
      <div class="col-2">
        <button
          @click="handleSearch"
          type="button"
          class="btn btn-primary btn-block w-100"
        >
          SEARCH
        </button>
      </div>
    </div>
  </form>
  <vue-good-table
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
import { showMessage } from "@/utils/toast";
import languageApi from "@/services/api/languages";
import keywordApi from "@/services/api/google/keyword-tools";
import countryApi from "@/services/api/countries";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";

const emit = defineEmits(["addKeywords"]);
const selectedKeywordIdeas = ref([]);
const keywords = ref("");
const country = ref({ id: null, name: "Global / Worlwide" });
//const language = ref("");
const languageOptions = ref([]);
const countryOptions = ref([]);
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
    label: "Keyword",
    field: "keyword",
  },
  {
    label: "Search Volume",
    field: "avg_monthly_searches",
    formatFn: (value) => {
      if (!value) return "0";
      return value.toLocaleString();
    },
  },
  {
    label: "Trend",
    field: "monthly_search_volumes",
    formatFn: (value) => {
      if (!value.length) return "+0.00%";
      const [month1, month2] = value.slice(-2);
      const trend =
        Math.abs(
          month2.avg_monthly_searches / month1.avg_monthly_searches - 1
        ) * 100;
      const sign =
        month2.avg_monthly_searches >= month1.avg_monthly_searches ? "+" : "-";
      return sign + trend.toFixed(2) + "%";
    },
  },
  {
    label: "Low Top of Page Bid (USD)",
    field: "low_top_of_page_bid",
    formatFn: (value) => {
      if (!value) return "$0.00";
      return "$" + value.toFixed(2);
    },
  },
  {
    label: "High Top of Page Bid (USD)",
    field: "high_top_of_page_bid",
    formatFn: (value) => {
      if (!value) return "$0.00";
      return "$" + value.toFixed(2);
    },
  },
  {
    label: "Competition",
    field: "competition_index",
    formatFn: (value, rowObj) => {
      if (!rowObj) return "";
      let label =
        rowObj.competition_label?.charAt(0) +
        rowObj.competition_label?.slice(1).toLowerCase();
      return value + " (" + label + ")";
    },
  },
]);

const onSelectionChanged = (data) => {
  selectedKeywordIdeas.value = data.selectedRows.map((row) => row.keyword);
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
  handleSearch();
};

const handleSearch = async () => {
  isLoading.value = true;
  if (!keywords.value.trim()) {
    showMessage("At least one keyword is required", "error");
    isLoading.value = false;
    return;
  }
  const mergedObj = Object.assign(
    {},
    {
      country: country.value.id,
      keywords: keywords.value,
      //language: language.value,
    },
    serverParams.value
  );
  try {
    const res = await keywordApi.getKeywordIdeas(mergedObj);
    if (res.success) {
      rows.value = res.data.keywords;
      totalRecords.value = res.data.keywords.length;
    } else
      showMessage(
        "Ups! Data could not be retrieved. Please try again later",
        "error"
      );
  } catch (error) {
    showMessage(error.message, "error");
  }
  isLoading.value = false;
};

const getGoogleLanguages = async () => {
  try {
    const res = await languageApi.getGoogleLanguages();
    if (res.length)
      languageOptions.value = res.map((language) => {
        return {
          value: language.criterion_id,
          label: language.language_name,
        };
      });
    else showMessage("Could not load Google Languages", "error");
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

const getGoogleCountries = async () => {
  try {
    const res = await countryApi.getGoogleCountries();

    if (res.length)
      countryOptions.value = [{ id: null, name: "Global / Worlwide" }].concat(
        res.map((country) => {
          return {
            id: country.criteria_id,
            name: country.name,
          };
        })
      );
    else showMessage("Could not load Google Countries", "error");
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  //getGoogleLanguages();
  getGoogleCountries();
});
</script>
