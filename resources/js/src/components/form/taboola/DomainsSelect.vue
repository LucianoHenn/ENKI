<template>
  <multiselect
    v-model="events"
    :options="eventsOptions"
    :label="label"
    :track-by="trackBy"
    :multiple="multiple"
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
import taboolaDomainsApi from "@/services/api/taboola/domains";

const events = ref([]);
const eventsOptions = ref([]);
const emit = defineEmits(["closeModal", "refreshList"]);

const props = defineProps({
  class: String,
  events: {
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

const loadElements = async () => {
  try {
    const res = await taboolaDomainsApi.getDomains({});
    eventsOptions.value = res.data.map((option) => {
      return {
        id: option.id,
        name: option.name,
        element: option,
      };
    });
  } catch (error) {
    showMessage("DomainSelect" + error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  loadElements();
});
</script>
