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
<style>
.multiselect__tag {
  color: rgb(0, 0, 0) !important;
  background: rgb(228, 228, 228) !important;
}
.multiselect__tag-icon::after {
  color: rgb(0, 0, 0) !important;
}
.multiselect__tag-icon:hover {
  color: rgb(0, 0, 0) !important;
}
.multiselect__tag-icon::after:hover {
  color: rgb(0, 0, 0) !important;
}
</style>
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
  optionName: String,
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

const loadConversionEvents = async () => {
  try {
    const res = await optionsApi.getOption(props.optionName);

    eventsOptions.value = res.data.value.map((option) => {
      return {
        value: option.name,
        name: option.value,
      };
    });
  } catch (error) {
    showMessage("ConvEventSelect" + error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  loadConversionEvents();
});
</script>
