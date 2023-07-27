<template>
  <multiselect
    v-model="tags"
    :options="tagOptions"
    :class="class"
    :multiple="multiple"
    :taggable="taggable"
    :searchable="searchable"
    :placeholder="placeholder"
    :selected-label="selectedLabel"
    :select-label="selectLabel"
    :deselect-label="deselectLabel"
    :close-on-select="closeOnSelect"
    @tag="$emit('tag', $event)"
  />
</template>
<style scoped>
.flex-grow-1 {
  flex: 1;
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import { showMessage } from "@/utils/toast";
import Multiselect from "@suadelabs/vue3-multiselect";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
import tagApi from "@/services/api/tags";

const tags = ref([]);
const tagOptions = ref([]);
const emit = defineEmits(["tag"]);

const props = defineProps({
  class: String,
  tags: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "",
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
  selectedLabel: {
    type: String,
    default: "",
  },
  selectLabel: {
    type: String,
    default: "",
  },
  taggable: {
    type: Boolean,
    default: true,
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

const loadTags = async () => {
  try {
    const res = await tagApi.getTags();
    tagOptions.value = res.data.map((tag) => tag.value);
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

onMounted(() => {
  loadTags();
});
</script>
