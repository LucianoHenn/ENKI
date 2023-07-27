<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-lg-12">
        <div class="col-sm-12 layout-spacing">
          <div class="panel br-6 p-0">
            <domain-list
                ref="domainListComponent"
                @edit-domain="editDomain"
                @add-domain="addDomain"
            />

            <create-or-edit-domain-modal
                ref="createOrEditModalComponent"
                :is-show="showCreateOrEditModal"
                @close-modal="showCreateOrEditModal = false"
                @domain-created="updateDomainList"
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
import DomainList from "./DomainList.vue";
import CreateOrEditDomainModal from "./CreateOrEditDomainModal.vue";

const showCreateOrEditModal = ref(false);
const createOrEditModalComponent = ref(null);
const domainListComponent = ref(null);

useMeta({ title: "Taboola - Creation Tools - Domains" });

const editDomain = (row) => {
  showCreateOrEditModal.value = true;
  createOrEditModalComponent.value.setDomainData(row);
};

const addDomain = () => {
  showCreateOrEditModal.value = true;
  createOrEditModalComponent.value.setDomainData();
};

const updateDomainList = () => {
  domainListComponent.value.loadItems();
};
</script>
