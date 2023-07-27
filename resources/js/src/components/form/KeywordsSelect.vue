<template>
  <multiselect
    v-model="keywords"
    :options="keywordsOptions"
    :clear-on-select="clearOnSelect"
    :class="class"
    :label="label"
    :track-by="trackBy"
    :multiple="multiple"
    :searchable="searchable"
    :selected-label="selectedLabel"
    :close-on-select="closeOnSelect"
    :select-label="selectLabel"
    :taggable="taggable"
    :deselect-label="deselectLabel"
    @search-change="asyncFind"
    placeholder="Type to search keywords"
  >
    <template v-slot:noResult>
      Oops! No items. Please Refine Your Search
    </template>
    <template v-slot:noOptions>
      Oops! No items. Please Refine Your Search
    </template>
  </multiselect>
</template>
<script setup>
import { ref, onMounted, watch } from "vue";
import { showMessage } from "@/utils/toast";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
import keywordApi from "@/services/api/database/keywords";
const keywords = ref([]);
const emit = defineEmits(["keyword", "searchKeyword"]);
const props = defineProps({
  class: String,
  keywords: {
    type: Array,
    default: () => [],
  },
  keywordsOptions: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "name",
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
});
// watch keywords and emit on change
watch(keywords, (value) => {
  if (value) {
    emit("keyword", value);
  }
  keywords.value = "";
});

const asyncFind = async (query) => {
  emit("searchKeyword", query);
};
</script>
