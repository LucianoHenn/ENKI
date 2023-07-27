<template>
  <multiselect
    v-model="roles"
    :options="rolesOptions"
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
import { ref, onMounted } from 'vue';
import { showMessage } from '@/utils/toast';
import Multiselect from '@suadelabs/vue3-multiselect';
import '@suadelabs/vue3-multiselect/dist/vue3-multiselect.css';
import roleApi from '@/services/api/roles';

const roles = ref([]);
const rolesOptions = ref([]);

const props = defineProps({
  class: String,
  roles: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: 'name',
  },
  trackBy: {
    type: String,
    default: 'value',
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
    default: 'Choose...',
  },
  selectLabel: {
    type: String,
    default: '',
  },
  selectedLabel: {
    type: String,
    default: '',
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
    default: '',
  },
});

const loadRoles = async () => {
  try {
    const res = await roleApi.getRoles();
    rolesOptions.value = res.data.map(role => {
      return {
        value: role.name,
        name: role.display_name
      }
    });
  } catch (error) {
    showMessage(error.message, 'error');
    console.log(error);
  }
}

onMounted(() => {
  loadRoles();
});
</script>
