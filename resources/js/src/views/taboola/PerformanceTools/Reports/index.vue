<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-lg-12">
        <div class="col-sm-12 layout-spacing">
          <div class="panel br-6 p-0">
            <jobber-list
              ref="JobberListComponent"
              :class-name="'Taboola\\PerformanceAnalyzerReport'"
              @add-jobber="showJobberModal = true"
              @show-logs="showLogs"
            />
            <jobber-create-modal
              ref="JobberCreateModalComponent"
              :is-show="showJobberModal"
              @close-modal="showJobberModal = false"
              @jobber-created="updateJobberList"
            />
            <jobber-logs-modal
              ref="JobberLogsModalComponent"
              :is-show="showLogsModal"
              @close-modal="showLogsModal = false"
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
import JobberList from "@/components/jobber/jobberList.vue";
import JobberLogsModal from "@/components/jobber/jobberLogsModal.vue";
import JobberCreateModal from "./jobberCreateModal.vue";

const JobberListComponent = ref(null);
const JobberCreateModalComponent = ref(null);
const showJobberModal = ref(false);
const showLogsModal = ref(false);
const JobberLogsModalComponent = ref(null);

useMeta({ title: "Taboola - Optimization Tools - Performance Analyzer" });

const updateJobberList = () => {
  JobberListComponent.value.loadItems();
  setTimeout(function () {
    JobberListComponent.value.loadItems();
  }, 15000);
};

const showLogs = (id) => {
  showLogsModal.value = true;
  JobberLogsModalComponent.value.getLogsByJobberId(id);
};
</script>
