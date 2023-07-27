<template>
  <div class="layout-px-spacing">
    <div class="row layout-spacing layout-top-spacing">
      <div class="col-sm-12 layout-spacing">
        <div class="panel br-6 p-0">
          <historical-data-list
            ref="listComponent"
          />

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>


import { useMeta } from "@/composables/use-meta";
import HistoricalDataList from "./HistoricalDataList.vue";


useMeta({ title: "Historical Data List" });


</script>
