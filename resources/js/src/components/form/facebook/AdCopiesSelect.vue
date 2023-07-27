<template>
    <multiselect
      v-model="events"
      :options="eventsOptions"
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
      :taggable="taggable"
      :deselect-label="deselectLabel"
    />
  </template>
    <script setup>
  import { ref, onMounted } from "vue";
  import { showMessage } from "@/utils/toast";
  import Multiselect from "@suadelabs/vue3-multiselect";
  import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
  import facebookSiteApi from "@/services/api/facebook/adcopies";
  
  const events = ref([]);
  const eventsOptions = ref([]);
  
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
    disabled: {
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
      default: false,
    },
    deselectLabel: {
      type: String,
      default: "",
    },
  });
  
  const loadElements = async () => {
    try {
      const res = await facebookSiteApi.getAdCopies();
      eventsOptions.value = res.data.map((option) => {
        return {
          id: option.id,
          name: option.description,
          element: option,
        };
      });
    } catch (error) {
      showMessage('AdCopiesSelect' + error.message, "error");
      console.log(error);
    }
  };
  
  onMounted(() => {
    loadElements();
  });
  </script>
  