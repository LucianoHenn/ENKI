<template>
  <div class="layout-px-spacing dash_1">
    <div class="row layout-top-spacing">
      <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-summary-1">
          <div class="widget-heading">
            <h5>Summary</h5>
          </div>
          <div class="widget-content">
            <div class="order-summary d-flex justify-content-around">
              <div class="summary-list summary-income">
                <div class="summery-info">
                  <div class="w-icon">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-shopping-bag"
                    >
                      <path
                        d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"
                      ></path>
                      <line x1="3" y1="6" x2="21" y2="6"></line>
                      <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                  </div>
                  <div class="w-summary-details">
                    <div class="w-summary-info">
                      <h6>
                        Spent

                        <span class="summary-count" v-if="!summaryData">
                          <span
                            class="spinner-border spinner-border-reverse align-self-center loader-sm text-secondary"
                          >
                            Loading...
                          </span>
                        </span>
                        <span class="summary-count" v-else>
                          € {{ summaryData.totalSpent }}
                        </span>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="summary-list summary-profit">
                <div class="summery-info">
                  <div class="w-icon">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-mouse-pointer"
                    >
                      <path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z" />
                      <path d="M13 13l6 6" />
                    </svg>
                  </div>
                  <div class="w-summary-details">
                    <div class="w-summary-info">
                      <h6>
                        CPC
                        <span class="summary-count" v-if="!summaryData">
                          <span
                            class="spinner-border spinner-border-reverse align-self-center loader-sm text-info"
                          >
                            Loading...
                          </span>
                        </span>
                        <span class="summary-count" v-else
                          >€ {{ summaryData?.avgCPC.toFixed(3) }}</span
                        >
                      </h6>
                      <p class="summary-average">avg.</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="summary-list summary-expenses">
                <div class="summery-info">
                  <div class="w-icon">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-external-link"
                    >
                      <path
                        d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"
                      />
                      <polyline points="15 3 21 3 21 9" />
                      <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                  </div>
                  <div class="w-summary-details">
                    <div class="w-summary-info">
                      <h6>
                        Conversions
                        <span class="summary-count" v-if="!summaryData">
                          <span
                            class="spinner-border spinner-border-reverse align-self-center loader-sm text-warning"
                          >
                            Loading...
                          </span>
                        </span>
                        <span v-else class="summary-count">{{
                          summaryData?.totalConversions
                        }}</span>
                      </h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="summary-list summary-revenue">
                <div class="summery-info">
                  <div class="w-icon">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-dollar-sign"
                    >
                      <line x1="12" y1="1" x2="12" y2="23" />
                      <path
                        d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"
                      />
                    </svg>
                  </div>
                  <div class="w-summary-details">
                    <div class="w-summary-info">
                      <h6>
                        CTR
                        <span class="summary-count" v-if="!summaryData">
                          <span
                            class="spinner-border spinner-border-reverse align-self-center loader-sm text-success"
                          >
                            Loading...
                          </span>
                        </span>
                        <span v-else class="summary-count"
                          >{{ summaryData?.vCTR.toFixed(1) }}%</span
                        >
                      </h6>
                      <p class="summary-average">avg.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-statistics">
          <div class="widget-content">
            <div class="row">
              <div class="col-6">
                <div class="w-detail">
                  <p class="w-title">Clicks</p>
                  <p class="w-stats" v-if="!summaryData">
                    <span
                      class="spinner-border spinner-border-reverse align-self-center loader-sm text-danger"
                    >
                      Loading...
                    </span>
                  </p>
                  <p class="w-stats" v-else>
                    {{ summaryData?.totalClicks.toLocaleString() }}
                  </p>
                </div>
                <apexchart
                  v-if="total_visit_options"
                  height="58"
                  type="line"
                  :options="total_visit_options"
                  :series="total_visit_series"
                ></apexchart>
              </div>
              <div class="col-6">
                <div class="w-detail">
                  <p class="w-title">Impressions</p>
                  <p class="w-stats" v-if="!summaryData">
                    <span
                      class="spinner-border spinner-border-reverse align-self-center loader-sm text-danger"
                    >
                      Loading...
                    </span>
                  </p>
                  <p class="w-stats" v-else>
                    {{ summaryData?.totalImpressions.toLocaleString() }}
                  </p>
                </div>
                <apexchart
                  v-if="paid_visit_options"
                  height="58"
                  type="line"
                  :options="paid_visit_options"
                  :series="paid_visit_series"
                ></apexchart>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="statbox panel box box-shadow">
          <div class="panel-heading">
            <div class="row">
              <div class="d-flex justify-content-between">
                <h4>Clicks vs Spent</h4>
                <div class="dropdown btn-group" d>
                  <a
                    href="javascript:;"
                    class="btn dropdown-toggle btn-icon-only"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-more-horizontal"
                    >
                      <circle cx="12" cy="12" r="1"></circle>
                      <circle cx="19" cy="12" r="1"></circle>
                      <circle cx="5" cy="12" r="1"></circle></svg
                  ></a>
                  <ul
                    class="dropdown-menu dropdown-menu-end"
                    aria-labelledby="ddlRevenue"
                    style=""
                  >
                    <li>
                      <a
                        @click="getWeeklyData"
                        href="javascript:;"
                        class="dropdown-item"
                        >Weekly</a
                      >
                    </li>
                    <li>
                      <a
                        @click="setMonthlyData"
                        href="javascript:;"
                        class="dropdown-item"
                        >Monthly</a
                      >
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="panel-body">
            <div
              v-if="isLoading"
              class="d-flex justify-content-center align-items-center"
              style="min-height: 45vh"
            >
              <div class="spinner-grow text-info align-self-center loader-lg">
                Loading...
              </div>
            </div>
            <apexchart
              v-else
              height="350"
              type="area"
              :options="options_line_graph"
              :series="series_line_graph"
            ></apexchart>
          </div>
        </div>
      </div>
      <div class="col-xl-4 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-sales-category">
          <div class="widget-heading">
            <h5>Campaigns by Device</h5>
          </div>
          <div class="widget-content">
            <apexchart
              v-if="options_7"
              height="460"
              type="donut"
              :options="options_7"
              :series="series_7"
            ></apexchart>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.widget.widget-summary-1 .widget-content .summary-list:not(:last-child) {
  margin-bottom: 0px !important;
}
.widget.widget-summary-1 .widget-content .summary-list.summary-revenue {
  background: rgb(221 239 238);
}
.widget.widget-summary-1
  .widget-content
  .summary-list.summary-revenue
  .summery-info
  .summary-average {
  color: #009688;
}
.widget.widget-summary-1
  .widget-content
  .summary-list.summary-revenue
  .summery-info
  .w-icon
  svg {
  color: #009688;
  fill: rgba(199, 230, 228, 0.62);
}
.summary-list {
  width: 18vh;
}
</style>

<script setup>
import "@/assets/sass/widgets/widgets.scss";
import { computed, ref, onMounted } from "vue";
import highlight from "@/components/plugins/highlight.vue";
import taboolaCampaignsApi from "@/services/api/taboola/campaigns";
import apexchart from "vue3-apexcharts";
import { useStore } from "vuex";
import { useMeta } from "@/composables/use-meta";

useMeta({ title: "Apex Chart" });

const store = useStore();
const isLoading = ref(true);
const summaryData = ref();

//Simple Line
const series_7 = ref([85, 37, 15]);

//Line Graph
const line_graph_labels = ref([]);
const series_line_graph = ref([
  {
    name: "Clicks",
    data: [31, 40, 28, 51, 42, 109, 100],
  },
  { name: "Spent", data: [11, 32, 45, 32, 34, 52, 41] },
]);

const options_line_graph = computed(() => {
  const is_dark = store.state.is_dark_mode;
  return {
    chart: {
      fontFamily: "Nunito, sans-serif",
      zoom: { enabled: false },
      toolbar: { show: false },
    },
    dataLabels: { enabled: false },
    stroke: { show: true, curve: "smooth", width: 2, lineCap: "square" },
    dropShadow: { enabled: true, opacity: 0.2, blur: 10, left: -7, top: 22 },
    colors: is_dark ? ["#2196f3", "#e7515a"] : ["#1b55e2", "#e7515a"],
    labels: line_graph_labels.value,
    xaxis: {
      axisBorder: { show: false },
      axisTicks: { show: false },
      crosshairs: { show: true },
      labels: {
        offsetX: 0,
        offsetY: 5,
        style: {
          fontSize: "12px",
          fontFamily: "Nunito, sans-serif",
          cssClass: "apexcharts-xaxis-title",
        },
      },
    },
    yaxis: {
      tickAmount: 7,
      labels: {
        formatter: function (value) {
          return value / 1000 + "K";
        },
        offsetX: -10,
        offsetY: 0,
        style: {
          fontSize: "12px",
          fontFamily: "Nunito, sans-serif",
          cssClass: "apexcharts-yaxis-title",
        },
      },
    },
    grid: {
      borderColor: is_dark ? "#191e3a" : "#e0e6ed",
      strokeDashArray: 5,
      xaxis: { lines: { show: true } },
      yaxis: { lines: { show: false } },
      padding: { top: 0, right: 0, bottom: 0, left: 0 },
    },
    legend: {
      position: "top",
      horizontalAlign: "right",
      offsetY: 0,
      fontSize: "16px",
      fontFamily: "Nunito, sans-serif",
      markers: {
        width: 10,
        height: 10,
        strokeWidth: 0,
        strokeColor: "#fff",
        fillColors: undefined,
        radius: 12,
        onClick: undefined,
        offsetX: 0,
        offsetY: 0,
      },
      itemMargin: { horizontal: 20, vertical: 5 },
    },
    tooltip: { theme: "dark", marker: { show: true }, x: { show: false } },
    fill: {
      type: "gradient",
      gradient: {
        type: "vertical",
        shadeIntensity: 1,
        inverseColors: !1,
        opacityFrom: is_dark ? 0.19 : 0.28,
        opacityTo: 0.05,
        stops: is_dark ? [100, 100] : [45, 100],
      },
    },
  };
});

//Donut
const options_7 = computed(() => {
  const is_dark = store.state.is_dark_mode;
  const option = {
    chart: {},
    dataLabels: { enabled: false },
    expandOnClick: is_dark ? false : true,
    stroke: { show: true, width: 25, colors: is_dark ? "#0e1726" : "#fff" },
    colors: is_dark
      ? ["#5c1ac3", "#e2a03f", "#e7515a", "#e2a03f"]
      : ["#e2a03f", "#5c1ac3", "#e7515a"],
    legend: {
      position: "bottom",
      horizontalAlign: "center",
      fontSize: "14px",
      markers: { width: 10, height: 10 },
      height: 50,
      offsetY: 20,
      itemMargin: { horizontal: 8, vertical: 0 },
    },
    plotOptions: {
      pie: {
        donut: {
          size: "65%",
          background: "transparent",
          labels: {
            show: true,
            name: {
              show: true,
              fontSize: "29px",
              fontFamily: "Nunito, sans-serif",
              offsetY: -10,
            },
            value: {
              show: true,
              fontSize: "26px",
              fontFamily: "Nunito, sans-serif",
              color: is_dark ? "#bfc9d4" : undefined,
              offsetY: 16,
              formatter: function (val) {
                return val;
              },
            },
            total: {
              show: true,
              label: "Total",
              color: "#888ea8",
              fontSize: "29px",
              formatter: function (w) {
                return w.globals.seriesTotals.reduce(function (a, b) {
                  return a + b;
                }, 0);
              },
            },
          },
        },
      },
    },
    labels: ["Mobile", "Desktop", "Tablet"],
  };

  if (is_dark) {
    option["states"] = {
      hover: { filter: { type: "none" } },
      active: { filter: { type: "none" } },
    };
  }

  return option;
});

//Statistics
const total_visit_series = ref([
  { data: [21, 9, 36, 12, 44, 25, 59, 41, 66, 25] },
]);
const total_visit_options = computed(() => {
  const is_dark = store.state.is_dark_mode;
  return {
    chart: {
      sparkline: { enabled: true },
      dropShadow: {
        enabled: true,
        top: 3,
        left: 1,
        blur: 3,
        color: "#009688",
        opacity: 0.7,
      },
    },
    stroke: { curve: "smooth", width: 2 },
    markers: { size: 0 },
    colors: ["#009688"],
    grid: { padding: { top: 0, bottom: 0, left: 0 } },
    tooltip: {
      theme: is_dark ? "dark" : "light",
      x: { show: false },
      y: {
        title: {
          formatter: function formatter() {
            return "";
          },
        },
      },
    },
    responsive: [
      {
        breakPoint: 576,
        options: {
          chart: { height: 95 },
          grid: { padding: { top: 45, bottom: 0, left: 0 } },
        },
      },
    ],
  };
});
const paid_visit_series = ref([
  { data: [22, 19, 30, 47, 32, 44, 34, 55, 41, 69] },
]);
const paid_visit_options = computed(() => {
  const is_dark = store.state.is_dark_mode;
  return {
    chart: {
      sparkline: { enabled: true },
      dropShadow: {
        enabled: true,
        top: 1,
        left: 1,
        blur: 2,
        color: "#e2a03f",
        opacity: 0.7,
      },
    },
    stroke: { curve: "smooth", width: 2 },
    markers: { size: 0 },
    colors: ["#e2a03f"],
    grid: { padding: { top: 0, bottom: 0, left: 0 } },
    tooltip: {
      theme: is_dark ? "dark" : "light",
      x: { show: false },
      y: {
        title: {
          formatter: function formatter() {
            return "";
          },
        },
      },
    },
    responsive: [
      {
        breakPoint: 576,
        options: {
          chart: { height: 95 },
          grid: { padding: { top: 35, bottom: 0, left: 0 } },
        },
      },
    ],
  };
});

const loadItems = async () => {
  isLoading.value = true;
  try {
    const res = await taboolaCampaignsApi.getCampaignsData();
    console.log("campaigns", res);
    summaryData.value = res.data;
    series_line_graph.value[0].data = summaryData.value.clicks.reverse();
    series_line_graph.value[1].data = summaryData.value.spends.reverse();
    line_graph_labels.value = summaryData.value.dates.reverse();
  } catch (error) {
    //showMessage(error.message, "error");
    console.log(error);
  }
  isLoading.value = false;
};

const setMonthlyData = async () => {
  isLoading.value = true;
  try {
    const res = await taboolaCampaignsApi.getCampaignsData();
    series_line_graph.value[0].data = res.data.clicks.reverse();
    series_line_graph.value[1].data = res.data.spends.reverse();
    line_graph_labels.value = res.data.dates.reverse();
  } catch (error) {
    //showMessage(error.message, "error");
    console.log(error);
  }
  isLoading.value = false;
};

const getWeeklyData = async () => {
  isLoading.value = true;
  try {
    const res = await taboolaCampaignsApi.getWeeklyCampaignsData();
    console.log("campaigns", res);

    series_line_graph.value[0].data = res.data.clicks.reverse();
    series_line_graph.value[1].data = res.data.spent.reverse();
    line_graph_labels.value = res.data.dates.reverse();
    //
  } catch (error) {
    //showMessage(error.message, "error");
    console.log(error);
  }
  isLoading.value = false;
};

onMounted(loadItems);
</script>
