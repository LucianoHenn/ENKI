<template>
  <multiselect
    v-model="countries"
    :options="useCustomCountries ? customCountries : countriesOptions"
    :class="class"
    :label="label"
    :disabled="disabled"
    :track-by="trackBy"
    :multiple="false"
    :searchable="searchable"
    :placeholder="placeholder"
    :selected-label="selectedLabel"
    :close-on-select="closeOnSelect"
    :select-label="selectLabel"
    :taggable="taggable"
    :deselect-label="deselectLabel"
  />
</template>
<script setup>
import { ref, onMounted } from "vue";
import { showMessage } from "@/utils/toast";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
import countryApi from "@/services/api/countries";

const countries = ref([]);
const countriesOptions = ref([]);

const props = defineProps({
  class: String,
  countries: {
    type: Array,
    default: () => [],
  },
  customCountries: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "name",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  trackBy: {
    type: String,
    default: "id",
  },
  multiple: {
    type: Boolean,
    default: true,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  placeholder: {
    type: String,
    default: "Choose...",
  },
  selectLabel: {
    type: String,
    default: "",
  },
  selectedLabel: {
    type: String,
    default: "",
  },
  taggable: {
    type: Boolean,
    default: false,
  },
  closeOnSelect: {
    type: Boolean,
    default: false,
  },
  deselectLabel: {
    type: String,
    default: "",
  },
  useCustomCountries: {
    type: Boolean,
    default: false,
  },
});

const loadCountries = async () => {
  try {
    const res = await countryApi.getCountries();
    countriesOptions.value = res.data.map((country) => {
      return {
        id: country.id,
        name: country.name,
      };
    });
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  if (!props.useCustomCountries) {
    loadCountries();
  }
});
</script>