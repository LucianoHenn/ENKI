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
import optionsApi from "@/services/api/options";

const events = ref([]);
const eventsOptions = ref([]);

const props = defineProps({
  option: String,
  customOptions: {
    type: Array,
    default: () => [],
  },
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
    default: "value",
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

const loadOptions = async () => {
  try {
    if (props.customOptions.length === 0) {
      const res = await optionsApi.getOption(props.option);
      eventsOptions.value = res.data.value.map((option) => {
        return {
          value: option,
          name: option
            .toLowerCase()
            .split("_")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join("_"),
        };
      });
    } else eventsOptions.value = props.customOptions;
  } catch (error) {
    showMessage(props.option + "-Select" + error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  loadOptions();
});
</script>
