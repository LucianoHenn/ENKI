<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-lg-12">
        <div class="col-sm-12 layout-spacing">
          <div class="panel br-6 p-0">
            <partnership-list
              ref="partnershipListComponent"
              @edit-partnership="editPartnership"
              @add-partnership="addPartnership"
            />

            <create-or-edit-partnership-modal
              ref="createOrEditPartnershipModalComponent"
              :is-show="showPartnershipModal"
              @close-modal="showPartnershipModal = false"
              @partnership-created="updatePartnershipList"
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
import PartnershipList from "./PartnershipList.vue";
import CreateOrEditPartnershipModal from "./CreateOrEditPartnershipModal.vue";

const showPartnershipModal = ref(false);
const createOrEditPartnershipModalComponent = ref(null);
const partnershipListComponent = ref(null);

useMeta({ title: "Taboola - Creation Tools - Partnerships" });

const editPartnership = (option) => {
  showPartnershipModal.value = true;
  createOrEditPartnershipModalComponent.value.setPartnershipData(option);
};

const addPartnership = () => {
  showPartnershipModal.value = true;
  createOrEditPartnershipModalComponent.value.setPartnershipData();
};

const updatePartnershipList = () => {
  partnershipListComponent.value.loadItems();
};
</script>
