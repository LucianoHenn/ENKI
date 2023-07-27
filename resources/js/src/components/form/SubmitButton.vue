<template>
  <button :class="buttonClass" @click="clickButton" >
    <span v-if="isLoading" class="spinner-border text-white me-2 align-self-center loader-sm">{{textLoading}}</span>
    <span v-if="isLoading">{{textLoading}}</span>
    <span v-else>{{ buttonText }}</span>
  </button>
</template>

<script setup>
  import { useStore } from 'vuex';
  import { computed } from 'vue';
  const store = useStore();
  const emit = defineEmits(["click"]);

  const props = defineProps({
    class: {
      type: String,
      default: "btn btn-primary",
    },
    buttonText: {
      type: String,
      default: "Submit",
    },
    textLoading: {
      type: String,
      default: "Loading...",
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
  });

  const buttonClass = computed(() => {
    return props.isLoading ? "btn btn-primary disabled" : props.class;
  });

  const clickButton = () => {
    store.commit("setIsLoading", true);
    emit("click");
  };
</script>
