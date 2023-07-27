<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-lg-12">
        <div class="col-sm-12 layout-spacing">
          <div class="panel br-6 p-0">
            <template-list
                ref="templateListComponent"
                @edit-template="editTemplate"
                @add-template="addTemplate"
            />
            <create-template-modal
                ref="templateCreateModal"
                :is-show="showTemplateModal"
                @close-modal="showTemplateModal = false"
                @refresh-list="updateTemplateList"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useMeta } from "@/composables/use-meta";
import TemplateList from "./TemplateList.vue";
import CreateTemplateModal from "./CreateTemplateModal.vue";
const templateListComponent = ref(null);
const templateCreateModal = ref(null);
const showTemplateModal = ref(false);
useMeta({ title: "Taboola - Creation Tools - Templates" });

const editTemplate = (data) => {
  templateCreateModal.value.setData(data);
  showTemplateModal.value = true;
};

const addTemplate = () => {
  templateCreateModal.value.emptyData();
  showTemplateModal.value = true;
};

const updateTemplateList = () => {
  templateListComponent.value.loadItems();
};
</script>
