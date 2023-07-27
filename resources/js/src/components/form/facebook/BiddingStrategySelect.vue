<template>
    <multiselect
      v-model="events"
      :options="eventsOptions"
      :class="class"
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
  import optionsApi from "@/services/api/options";
  
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
      default: "label",
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
  
  const loadConversionEvents = async () => {
    try {
      const res = await optionsApi.getOption("facebook_bidding_strategies");
      eventsOptions.value = res.data.value.map((option) => {
        return {
          id: option.id,
          label: option.label
        };
      });
    } catch (error) {
      showMessage('AdBiddingSelect' + error.message, "error");
    }
  };
  
  onMounted(() => {
    loadConversionEvents();
  });
  </script>
  