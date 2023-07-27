<template>
  <div
    ref="staticModalRef"
    id="addFacebookCapaignGeneratorJobModal"
    class="modal fade"
    aria-labelledby="addFacebookCapaignGeneratorModalLabel"
    aria-hidden="true"
    data-keyboard="false"
    data-backdrop="static"
  >
    <div
      class="modal-dialog modal-fullscreen modal-dialog-centered"
      role="document"
    >
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logModalLabel">
            {{ className.replace("/", " ") }} Logs
          </h5>
          <button
            type="button"
            data-dismiss="modal"
            data-bs-dismiss="modal"
            aria-label="Close"
            class="btn-close"
            @click="closeModal"
          ></button>
        </div>
        <div class="modal-body" v-if="jobberIds.length > 0">
          <div class="panel-body underline-content tabs pt-0">
            <ul
              class="nav nav-tabs mb-3 d-flex"
              id="lineTab"
              role="tablist"
              v-if="jobberIds.length"
            >
              <li
                @click="setActive(id)"
                class="nav-item flex-fill"
                v-for="id in jobberIds"
                :key="id"
              >
                <a
                  :class="{ active: active == id }"
                  class="nav-link text-center"
                  id="underline-home-tab"
                  data-bs-toggle="tab"
                  href="#underline-home"
                  role="tab"
                  aria-controls="underline-home"
                  aria-selected="false"
                >
                  #{{ id }}
                </a>
              </li>
            </ul>
            <div class="row">
              <div class="col-md-4 d-flex">
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="search query"
                    v-model="query"
                  />
                  <span class="input-group-btn">
                    <button
                      @click="handleSearch"
                      class="btn btn-outline-danger"
                      type="button"
                    >
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
                        class="feather feather-search"
                      >
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
              <div class="col-md-4 offset-md-4">
                <div class="row">
                  <div class="col-sm-4 d-flex">
                    <div
                      class="checkbox-primary custom-control custom-checkbox"
                    >
                      <input
                        type="checkbox"
                        class="custom-control-input"
                        :value="wordWrap"
                        v-model="wordWrap"
                        id="chk1"
                      />
                      <label class="custom-control-label" for="chk1"
                        >Word Wrap</label
                      >
                    </div>
                  </div>
                  <label
                    class="
                      d-flex
                      align-items-center
                      col-sm-1 col-form-label col-form-label-sm
                      font-weight-bold
                    "
                    for="colFormLabelSm"
                    >Level:</label
                  >
                  <div class="col-sm-7">
                    <select
                      v-model="debugLevel"
                      class="form-select form-select-sm"
                    >
                      <option value="">choose a level</option>
                      <option value="debug">DEBUG</option>
                      <option value="info">INFO</option>
                      <option value="warning">WARNING</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div id="logData" class="mt-3">
              <div v-if="isLoading">
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
                  class="feather feather-loader spin me-2"
                >
                  <line x1="12" y1="2" x2="12" y2="6"></line>
                  <line x1="12" y1="18" x2="12" y2="22"></line>
                  <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                  <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                  <line x1="2" y1="12" x2="6" y2="12"></line>
                  <line x1="18" y1="12" x2="22" y2="12"></line>
                  <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                  <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                </svg>
                Loading
              </div>
              <div v-else>
                <div v-if="logs.length">
                  <div
                    v-for="str in logLines()"
                    :key="str"
                    :class="{ 'text-nowrap': !wordWrap }"
                    class="mb-1"
                  >
                    <span v-html="str"></span>
                    <br />
                  </div>
                </div>
              </div>
              <div v-if="isJobberNotFinished">
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
                  class="feather feather-loader spin me-2"
                >
                  <line x1="12" y1="2" x2="12" y2="6"></line>
                  <line x1="12" y1="18" x2="12" y2="22"></line>
                  <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                  <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                  <line x1="2" y1="12" x2="6" y2="12"></line>
                  <line x1="18" y1="12" x2="22" y2="12"></line>
                  <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                  <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                </svg>
                Fetching Logs...
              </div>
            </div>
          </div>
        </div>
        <div class="text-center mt-5" v-if="jobberIds.length === 0">
          <button type="button" class="btn btn-info btn-lg mb-3 me-3">
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
              class="feather feather-loader spin me-2"
            >
              <line x1="12" y1="2" x2="12" y2="6"></line>
              <line x1="12" y1="18" x2="12" y2="22"></line>
              <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
              <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
              <line x1="2" y1="12" x2="6" y2="12"></line>
              <line x1="18" y1="12" x2="22" y2="12"></line>
              <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
              <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
            </svg>
            Loading
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
#logData {
  overflow-y: scroll !important;
  background-color: black;
  background-image: radial-gradient(rgba(0, 150, 0, 0.75), black 120%);
  height: 70vh;
  margin: 0;
  overflow: scroll;
  padding: 1.3rem;
  color: white;
  font: 0.7rem Inconsolata, monospace;
  line-height: 1.3rem;
  text-shadow: 0 0 2px #c8c8c8;
}
#logData::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: repeating-linear-gradient(
    0deg,
    rgba(black, 0.15),
    rgba(black, 0.15) 1px,
    transparent 1px,
    transparent 2px
  );
  pointer-events: none;
}

.tooltip-inner {
  max-width: 500px;
}
</style>
<script setup>
import { ref, toRef, watch } from "vue";
import useBsModal from "@/composables/useBsStaticModal";
import logsApi from "@/services/api/facebook/campaign-logs";
import { showMessage } from "@/utils/toast";

const emit = defineEmits(["closeStaticModal", "JobberCreated"]);
const props = defineProps({ isShow: Boolean });
const logs = ref([]);
const filteredLogs = ref([]);
const active = ref(null);
const jobberIds = ref([]);
const wordWrap = ref(true);
const query = ref(null);
const debugLevel = ref("");
const isLoading = ref(true);
const className = ref("");
const isJobberNotFinished = ref(false);
const timeoutId = ref(null);

watch(query, (val) => {
  if (val === "") {
    if (debugLevel.value)
      filteredLogs.value = logs.value.filter((x) =>
        x.level_name.toLowerCase().includes(debugLevel.value.toLowerCase())
      );
    else filteredLogs.value = logs.value;
  }
});

watch(active, (val) => {
  if (val) getLogs(val);
  query.value = null;
  debugLevel.value = "";
});

watch(debugLevel, (val) => {
  if (!val) {
    if (query.value)
      filteredLogs.value = logs.value.filter((x) =>
        x.message.toLowerCase().includes(query.value.toLowerCase())
      );
    else filteredLogs.value = logs.value;
  }
  if (query.value)
    filteredLogs.value = logs.value.filter(
      (x) =>
        x.level_name.toLowerCase().includes(val.toLowerCase()) &&
        x.message.toLowerCase().includes(query.value.toLowerCase())
    );
  else
    filteredLogs.value = logs.value.filter((x) =>
      x.level_name.toLowerCase().includes(val.toLowerCase())
    );
});

const setActive = (n) => {
  active.value = n;
};

const { staticModalRef, closeStaticModal } = useBsModal(
  toRef(props, "isShow"),
  emit
);
const closeModal = () => {
  clearTimeout(timeoutId.value);
  jobberIds.value = [];
  isJobberNotFinished.value = false;
  logs.value = [];
  filteredLogs.value = [];
  setActive(null);
  emit("closeModal");
};

const handleSearch = () => {
  if (debugLevel.value)
    filteredLogs.value = logs.value.filter(
      (x) =>
        x.message.toLowerCase().includes(query.value.toLowerCase()) &&
        x.level_name.toLowerCase().includes(debugLevel.value.toLowerCase())
    );
  else
    filteredLogs.value = logs.value.filter((x) =>
      x.message.toLowerCase().includes(query.value.toLowerCase())
    );
};

const renderMoreLogs = async (id) => {
  clearTimeout(timeoutId.value);
  timeoutId.value = setTimeout(async () => {
    const res = await logsApi.getLogs({ id });

    for (let i = filteredLogs.value.length; i < res.data.logs.length; i++) {
      filteredLogs.value.push(res.data.logs[i]);
    }
    logs.value = filteredLogs.value;

    if (res.data.status === "pending" || res.data.status === "running")
      renderMoreLogs(id);
    else isJobberNotFinished.value = false;
  }, 10000);
};

const getLogs = async (id, loadingValue = true) => {
  isLoading.value = loadingValue;
  try {
    const res = await logsApi.getLogs({ id });

    className.value = res.data.className;
    logs.value = res.data.logs;
    filteredLogs.value = logs.value;
    if (!jobberIds.value.length) jobberIds.value = res.data.jobsIds;
    if (res.data.status === "pending" || res.data.status === "running") {
      isJobberNotFinished.value = true;
      renderMoreLogs(id);
    }
    isLoading.value = false;
  } catch (error) {
    showMessage(`The logs of Jobber # ${id} are not available`, "error");
    logs.value = [];
    filteredLogs.value = [];
    isLoading.value = false;
  }
};

const logLines = () => {
  return filteredLogs.value.map((x) => {
    const badge =
      x.level_name === "INFO"
        ? "info"
        : x.level_name === "DEBUG"
        ? "warning"
        : "danger";
    return (
      x.datetime +
      " " +
      `<span style="width: 4rem" class="badge badge-${badge} me-1">${x.level_name}</span>` +
      " " +
      x.message
    );
  });
};

const getLogsByJobberId = (id) => {
  setActive(id);
};

defineExpose({ getLogsByJobberId });
</script>