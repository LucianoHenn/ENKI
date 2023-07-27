<template>
  <multiselect
    v-model="categories"
    :options="useCustomCategories ? customCategories : categoriesOptions"
    :class="class"
    :label="label"
    :disabled="disabled"
    :track-by="trackBy"
    :multiple="multiple"
    :searchable="searchable"
    :placeholder="placeholder"
    :selected-label="selectedLabel"
    :close-on-select="closeOnSelect"
    :select-label="selectLabel"
    :deselect-label="deselectLabel"
  />
</template>
<script setup>
import { ref, onMounted } from "vue";
import { showMessage } from "@/utils/toast";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
import categoryApi from "@/services/api/categories";

const categories = ref([]);
const categoriesOptions = ref([]);

const props = defineProps({
  class: String,
  categories: {
    type: Array,
    default: () => [],
  },
  customCategories: {
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

const loadCategories = async () => {
  try {
    const res = await categoryApi.getCategories();
    categoriesOptions.value = res.data.map((category) => {
      return {
        id: category.id,
        name: category.name,
      };
    });
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  if (!props.useCustomCategories) {
    loadCategories();
  }
});
</script>