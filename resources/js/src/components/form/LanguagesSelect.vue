<template>
  <multiselect
    v-model="languages"
    :options="useCustomLanguages ? customLanguages : languagesOptions"
    :class="class"
    :label="label"
    :track-by="trackBy"
    :multiple="multiple"
    :searchable="searchable"
    :placeholder="placeholder"
    :selected-label="selectedLabel"
    :select-label="selectLabel"
    :taggable="taggable"
    :close-on-select="closeOnSelect"
    :deselect-label="deselectLabel"
  />
</template>
<script setup>
import { ref, onMounted } from "vue";
import { showMessage } from "@/utils/toast";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
import languageApi from "@/services/api/languages";

const languages = ref([]);
const languagesOptions = ref([]);
const props = defineProps({
  class: String,
  languages: {
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
    default: false,
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
    default: true,
  },
  deselectLabel: {
    type: String,
    default: "",
  },
  customLanguages: {
    type: Array,
    default: () => [],
  },
  useCustomLanguages: {
    type: Boolean,
    default: false,
  },
});

const loadLanguages = async () => {
  try {
    const res = await languageApi.getLanguages();
    languagesOptions.value = res.data.map((language) => {
      return {
        id: language.id,
        name: language.name,
      };
    });
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  if (!props.useCustomLanguages) loadLanguages();
});
</script>
