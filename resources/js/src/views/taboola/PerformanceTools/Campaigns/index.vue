<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-lg-12">
        <div class="col-sm-12 layout-spacing">
          <div class="panel br-6 p-0">
            <campaign-list ref="campaignListComponent" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useMeta } from "@/composables/use-meta";
import campaignList from "./CampaignList.vue";

useMeta({ title: "Taboola - Performance Tools - Campaigns" });
</script>
