<template>
  <div
      ref="staticModalRef"
      id="adTaboolaCapaignGeneratorJobModal"
      class="modal fade"
      aria-labelledby="adTabolaCapaignGeneratorModalLabel"
      aria-hidden="true"
      data-keyboard="false"
      data-backdrop="static"
  >
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
      <div class="modal-content mailbox-popup">
        <div class="modal-header">
          <h5 class="modal-title">New Taboola Campaign Generation Request</h5>
          <button
              type="button"
              data-dismiss="modal"
              data-bs-dismiss="modal"
              aria-label="Close"
              class="btn-close"
              @click="closeModal"
          ></button>
        </div>

        <div class="modal-body pt-0">
          <div class="panel-body simple-tab tabs">
            <!-- Alert component -->
            <div
                :class="showAlertFlag ? '' : 'hide'"
                class="alert alert-icon-left alert-dismissible alert-light-danger mb-0 fade-out"
                role="alert"
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
                  class="feather feather-alert-circle"
              >
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12" y2="16"></line>
              </svg>
              <strong>Error!</strong>
              One or more fields in the form requires your attention before
              proceeding
              <button
                  type="button"
                  class="close"
                  @click="showAlertFlag = false"
              >
                ×
              </button>
            </div>

            <!-- tabs menu -->
            <ul
                class="nav nav-tabs mb-3 mt-3"
                id="fb-camp-create-job-tablist"
                role="tablist"
            >
              <li class="nav-item">
                <a
                    class="nav-link"
                    :class="{ active: activeTab == 1 }"
                    id="taboola-select-template-tab"
                    @click="changeStep(1)"
                >Main Setup
                  <svg
                      v-if="errors.user"
                      width="24"
                      height="24"
                      fill=""
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      role="img"
                      aria-label="alert"
                      class="validationGroup_error__Y+hgO"
                      style="color: #e23e57"
                  >
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M4 12a8 8 0 1116 0 8 8 0 01-16 0zm7 3v2h2v-2h-2zm-.292-7.125a2.999 2.999 0 00-.135 1.28l.317 2.851a1.117 1.117 0 002.22 0l.317-2.851a2.999 2.999 0 00-.135-1.28L13 7h-2l-.292.875z"
                    ></path></svg
                  ></a>
              </li>
              <li class="nav-item">
                <a
                    class="nav-link"
                    id="taboola-campaign-setting-tab"
                    :class="{ active: activeTab == 2 }"
                    @click="changeStep(2)"
                >Campaign Settings
                  <svg
                      v-if="
                      errors.brand_name ||
                      errors.marketing_objective ||
                      errors.conversion_event
                    "
                      width="24"
                      height="24"
                      fill=""
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      role="img"
                      aria-label="alert"
                      class="validationGroup_error__Y+hgO"
                      style="color: #e23e57"
                  >
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M4 12a8 8 0 1116 0 8 8 0 01-16 0zm7 3v2h2v-2h-2zm-.292-7.125a2.999 2.999 0 00-.135 1.28l.317 2.851a1.117 1.117 0 002.22 0l.317-2.851a2.999 2.999 0 00-.135-1.28L13 7h-2l-.292.875z"
                    ></path></svg
                  ></a>
              </li>
              <li class="nav-item">
                <a
                    class="nav-link"
                    id="taboola-targeting-tab"
                    :class="{ active: activeTab == 3 }"
                    @click="changeStep(3)"
                >Targeting
                  <svg
                      v-if="errors.platform_targeting"
                      width="24"
                      height="24"
                      fill=""
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      role="img"
                      aria-label="alert"
                      class="validationGroup_error__Y+hgO"
                      style="color: #e23e57"
                  >
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M4 12a8 8 0 1116 0 8 8 0 01-16 0zm7 3v2h2v-2h-2zm-.292-7.125a2.999 2.999 0 00-.135 1.28l.317 2.851a1.117 1.117 0 002.22 0l.317-2.851a2.999 2.999 0 00-.135-1.28L13 7h-2l-.292.875z"
                    ></path>
                  </svg>
                </a>
              </li>
              <li class="nav-item">
                <a
                    class="nav-link"
                    id="taboola-budget-tab"
                    :class="{ active: activeTab == 4 }"
                    @click="changeStep(4)"
                >Budget
                  <svg
                      v-if="
                      errors.cpc || errors.spending_limit || errors.daily_budget
                    "
                      width="24"
                      height="24"
                      fill=""
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      role="img"
                      aria-label="alert"
                      class="validationGroup_error__Y+hgO"
                      style="color: #e23e57"
                  >
                    <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M4 12a8 8 0 1116 0 8 8 0 01-16 0zm7 3v2h2v-2h-2zm-.292-7.125a2.999 2.999 0 00-.135 1.28l.317 2.851a1.117 1.117 0 002.22 0l.317-2.851a2.999 2.999 0 00-.135-1.28L13 7h-2l-.292.875z"
                    ></path></svg
                  ></a>
              </li>
              <li class="nav-item">
                <a
                    class="nav-link"
                    id="taboola-creatives-tab"
                    :class="{ active: activeTab == 5 }"
                    @click="changeStep(5)"
                >Creatives</a
                >
              </li>
            </ul>
            <!-- \tabs menu-->
            <!-- content -->
            <div class="tab-content" id="fb-camp-create-job-tabcontent">
              <!-- Select Template -->
              <div
                  class="tab-pane fade"
                  :class="{ show: activeTab == 1, active: activeTab == 1 }"
                  id="kwdsimgs"
                  role="tabpanel"
                  aria-labelledby="fb-camp-create-kwds-imgs-tab"
              >
                <div class="col-12 mb-4">
                  <div
                      class="alert alert-dismissible alert-arrow-left alert-icon-left alert-light-info mb-0 text-break"
                  >
                    <button
                        type="button"
                        class="close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    >
                      ×
                    </button>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                      <path
                          fill="currentColor"
                          fill-rule="nonzero"
                          d="M7.018 15.2c-1.623-.357-2.965-1.632-3.156-3.184-.266-2.17 1.406-4.084 3.679-4.3l4.546-.435 4.592-3.439c.836-.626 1.928-.159 2.053.874l1.319 10.858c.124 1.028-.804 1.748-1.77 1.353l-5.39-2.205-.614.075.286 2.33a2.464 2.464 0 0 1-1.96 2.7l-.282.054a2.509 2.509 0 0 1-2.988-2.16L7.018 15.2zm9.877-9.02l-4.06 3.039-5.104.487c-1.188.113-2.008 1.052-1.883 2.066.105.855 1.212 1.65 2.343 1.512l4.974-.61 4.755 1.944-1.025-8.439zM9.317 17.471a.51.51 0 0 0 .623.446l.281-.055a.465.465 0 0 0 .357-.493l-.262-2.134-1.259.155.26 2.081z"
                      ></path></svg
                    >With the "Template Select" you can choose an already
                    existing template to pre-fill all the Campaign, Targeting
                    and Budget Fields.
                  </div>
                </div>
                <div class="row">
                  <div class="col-4">
                    <div class="form-group mb-4">
                      <label>Language</label>

                      <languages-select v-model="language" />
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group mb-4">
                      <label>Category</label>

                      <categories-select
                          v-model="category"
                          :closeOnSelect="true"
                          :multiple="false"
                      />
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group mb-4">
                      <label>Country</label>

                      <countries-select
                          v-model="country"
                          :closeOnSelect="true"
                          :multiple="false"
                      />
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="col-form-label" for="template"
                    >Select Template</label
                    >
                    <multiselect
                        track-by="name"
                        label="name"
                        v-model="selectedTemplate"
                        :options="templateOptions"
                        :searchable="true"
                    ></multiselect>
                  </div>
                </div>

                <div class="my-3">
                  <label class="col-form-label">Request Description</label>
                  <div>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Description"
                        v-model="description"
                    />
                  </div>
                </div>

                <div class="my-3">
                  <label class="col-form-label">User</label>
                  <user-select
                      v-model="user"
                      :multiple="false"
                      :closeOnSelect="true"
                      :class="errors.user ? 'is-invalid' : ''"
                  />
                  <div class="invalid-feedback">Select a User</div>
                </div>

                <div class="d-flex mt-5 justify-content-end">
                  <div class="btn btn-primary" @click="changeStep(2)">Next</div>
                </div>
              </div>
              <!-- /elect Template-->

              <!-- Campaign Setting -->
              <div
                  class="tab-pane"
                  id="sites"
                  :class="{ show: activeTab == 2, active: activeTab == 2 }"
                  role="tabpanel"
                  aria-labelledby="fb-camp-create-sites-tab"
              >
                <form>
                  <div class="mb-4">
                    <label class="col-form-label" for="name_suffix"
                    >Campaign Name Suffix</label
                    >
                    <div>
                      <input
                          type="text"
                          id="name_suffix"
                          class="form-control"
                          placeholder="Campaign Name Suffix"
                          v-model="template.campaign_settings.name_suffix"
                      />
                    </div>
                  </div>
                  <div class="mb-4">
                    <label class="col-form-label" for="brand_name"
                    >Brand Name</label
                    >
                    <div>
                      <input
                          @keydown="errors.brand_name = false"
                          type="text"
                          id="brand_name"
                          class="form-control"
                          placeholder="Brand Name"
                          v-model="template.campaign_settings.brand_name"
                          :class="{
                          'is-invalid':
                            template.campaign_settings.brand_name.length ===
                              0 && errors.brand_name,
                        }"
                      />
                      <div class="invalid-feedback">Enter a Brand Name</div>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label class="col-form-label" for="marketing_objective"
                    >Marketing Objective</label
                    >
                    <div class="d-flex gap-3 mt-1">
                      <div
                          class="card"
                          :class="{
                          selected:
                            template.campaign_settings.marketing_objective ===
                            'LEADS_GENERATION',
                        }"
                      >
                        <div class="card-body">
                          <label class="card-label">
                            <input
                                class="baseRadio__radio-input"
                                type="radio"
                                name="marketing-objective"
                                value="LEADS_GENERATION"
                                @change="errors.marketing_objective = false"
                                v-model="
                                template.campaign_settings.marketing_objective
                              "
                            />
                            <div class="iconRadioButton__icon-container">
                              <svg
                                  width="32"
                                  height="32"
                                  viewBox="0 0 32 32"
                                  fill="currentColor"
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="marketingObjective_enabled-icon"
                              >
                                <path
                                    d="M5.31105 11.4293L5.82372 10.5707L14.2252 15.5873C15.0116 16.0569 15.9918 16.0588 16.7801 15.5923L25.2671 10.5697L25.7764 11.4303L17.2894 16.4529C16.1858 17.106 14.8135 17.1033 13.7125 16.4459L5.31105 11.4293Z"
                                    fill="currentColor"
                                ></path>
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M23 10H8C6.89543 10 6 10.8954 6 12V20C6 21.1046 6.89543 22 8 22H23C24.1046 22 25 21.1046 25 20V12C25 10.8954 24.1046 10 23 10ZM8 9C6.34315 9 5 10.3431 5 12V20C5 21.6569 6.34315 23 8 23H23C24.6569 23 26 21.6569 26 20V12C26 10.3431 24.6569 9 23 9H8Z"
                                    fill="currentColor"
                                ></path>
                              </svg>
                              <span class="marketingObjective_title">
                                <span>LEAD GENERATION</span>
                              </span>
                            </div>
                          </label>
                        </div>
                      </div>
                      <div
                          class="card"
                          :class="{
                          selected:
                            template.campaign_settings.marketing_objective ===
                            'ONLINE_PURCHASES',
                        }"
                      >
                        <div class="card-body">
                          <label class="card-label">
                            <input
                                v-model="
                                template.campaign_settings.marketing_objective
                              "
                                class="baseRadio__radio-input"
                                type="radio"
                                name="marketing-objective"
                                @change="errors.marketing_objective = false"
                                value="ONLINE_PURCHASES"
                            />
                            <div class="iconRadioButton__icon-container">
                              <svg
                                  width="32"
                                  height="32"
                                  viewBox="0 0 32 32"
                                  fill="currentColor"
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="marketingObjective_enabled-icon__IR6dm"
                              >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M6.5 8H8.13912L10.1443 18.6176C10.3228 19.7644 10.9174 20.6929 11.7465 21.2849C11.165 21.6347 10.7759 22.2719 10.7759 23C10.7759 24.1046 11.6714 25 12.7759 25C13.8805 25 14.7759 24.1046 14.7759 23C14.7759 22.6357 14.6785 22.2942 14.5084 22H21.0435C20.8733 22.2942 20.7759 22.6357 20.7759 23C20.7759 24.1046 21.6714 25 22.7759 25C23.8805 25 24.7759 24.1046 24.7759 23C24.7759 22.6069 24.6625 22.2404 24.4667 21.9312C24.6143 21.8443 24.7134 21.6837 24.7134 21.5C24.7134 21.2239 24.4896 21 24.2134 21H14.0101C12.8843 21 11.8784 20.3814 11.3929 19.3313L20.6186 18.6332C23.266 18.568 25.2708 16.8919 25.2708 14.2651L25.385 10.5164C25.3943 10.234 25.1679 10 24.8853 10H9.5371L9.04907 7.41125L8.9749 7H6.5C6.22386 7 6 7.22386 6 7.5C6 7.77614 6.22386 8 6.5 8ZM22.7759 22C22.2759 22 22.0259 22 21.9009 22.125C21.7759 22.25 21.7759 22.5 21.7759 23C21.7759 23.5 21.7759 23.75 21.9009 23.875C22.0259 24 22.2759 24 22.7759 24C23.2759 24 23.5259 24 23.6509 23.875C23.7759 23.75 23.7759 23.5 23.7759 23C23.7759 22.5 23.7759 22.25 23.6509 22.125C23.5259 22 23.2759 22 22.7759 22ZM9.72562 11L11.1112 18.3497L20.5685 17.6348C22.7381 17.5808 24.2708 16.2995 24.2711 14.2487L24.3686 11H9.72562ZM13.6509 23.875C13.5259 24 13.2759 24 12.7759 24C12.2759 24 12.0259 24 11.9009 23.875C11.7759 23.75 11.7759 23.5 11.7759 23C11.7759 22.5 11.7759 22.25 11.9009 22.125C12.0259 22 12.2759 22 12.7759 22C13.2759 22 13.5259 22 13.6509 22.125C13.7759 22.25 13.7759 22.5 13.7759 23C13.7759 23.5 13.7759 23.75 13.6509 23.875Z"
                                    fill="currentColor"
                                ></path>
                              </svg>
                              <span class="marketingObjective_title">
                                <span>ONLINE PURCHASES</span>
                              </span>
                            </div>
                          </label>
                        </div>
                      </div>
                      <div
                          class="card"
                          :class="{
                          selected:
                            template.campaign_settings.marketing_objective ===
                            'DRIVE_WEBSITE_TRAFFIC',
                        }"
                      >
                        <div class="card-body">
                          <label class="card-label">
                            <input
                                v-model="
                                template.campaign_settings.marketing_objective
                              "
                                class="baseRadio__radio-input"
                                @change="errors.marketing_objective = false"
                                type="radio"
                                name="marketing-objective"
                                value="DRIVE_WEBSITE_TRAFFIC"
                            />
                            <div class="iconRadioButton__icon-container">
                              <svg
                                  width="32"
                                  height="32"
                                  viewBox="0 0 32 32"
                                  fill="currentColor"
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="marketingObjective_enabled-icon__IR6dm"
                              >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M6.00027 15.8669C6.00122 15.8737 6.00552 15.9034 6.02501 15.9616C6.04855 16.0319 6.08746 16.1238 6.14532 16.2367C6.26099 16.4625 6.43872 16.7459 6.67707 17.0685C7.153 17.7127 7.84919 18.4839 8.71397 19.225C10.4526 20.715 12.8071 22.0283 15.366 22.0283C17.9217 22.0283 20.3431 20.6617 22.1555 19.1372C23.0556 18.3799 23.7875 17.5985 24.2906 16.9606C24.5425 16.641 24.7316 16.3642 24.8548 16.1502C24.9166 16.0428 24.9574 15.9589 24.9814 15.8986C24.9863 15.8863 24.99 15.8761 24.9929 15.8678C24.9903 15.8607 24.9871 15.8521 24.983 15.842C24.9598 15.785 24.9198 15.7051 24.8586 15.6023C24.7365 15.3973 24.5485 15.1326 24.2971 14.827C23.7953 14.2171 23.0644 13.4723 22.1647 12.7513C20.3548 11.3008 17.9301 10 15.366 10C12.7983 10 10.4407 11.2476 8.70465 12.6636C7.84042 13.3685 7.14547 14.1031 6.67101 14.719C6.43337 15.0275 6.25682 15.2987 6.1423 15.5152C6.08504 15.6234 6.04687 15.7113 6.02395 15.7782C6.00539 15.8324 6.00121 15.8603 6.00027 15.8669ZM8.07259 11.8887C9.90658 10.3928 12.482 9 15.366 9C18.2534 9 20.8958 10.4529 22.79 11.9709C23.7424 12.7341 24.5228 13.5273 25.0693 14.1917C25.3421 14.5232 25.5624 14.8298 25.7177 15.0905C25.7952 15.2205 25.8611 15.347 25.9092 15.4651C25.9532 15.5731 26 15.7163 26 15.8667C26 16.0158 25.9538 16.1593 25.9108 16.2677C25.8635 16.3868 25.7984 16.5156 25.7215 16.6491C25.5673 16.9169 25.348 17.2345 25.0758 17.5797C24.5305 18.2713 23.7511 19.1016 22.7992 19.9024C20.9074 21.4938 18.2619 23.0283 15.366 23.0283C12.4732 23.0283 9.89466 21.5537 8.06326 19.9843C7.14301 19.1957 6.39489 18.3694 5.87277 17.6628C5.61209 17.3099 5.40237 16.9797 5.25533 16.6927C5.1197 16.4279 5 16.1303 5 15.8667C5 15.6005 5.12151 15.3063 5.25835 15.0476C5.40654 14.7675 5.61745 14.448 5.87883 14.1087C6.40242 13.429 7.15178 12.6398 8.07259 11.8887ZM11.5789 15.7261C11.5789 13.6598 13.2956 12.0073 15.3849 12.0073C17.4741 12.0073 19.1908 13.6598 19.1908 15.7261C19.1908 17.7924 17.4741 19.4448 15.3849 19.4448C13.2956 19.4448 11.5789 17.7924 11.5789 15.7261ZM15.3849 13.0073C13.8225 13.0073 12.5789 14.2371 12.5789 15.7261C12.5789 17.2151 13.8225 18.4448 15.3849 18.4448C16.9472 18.4448 18.1908 17.2151 18.1908 15.7261C18.1908 14.2371 16.9472 13.0073 15.3849 13.0073Z"
                                    fill="currentColor"
                                ></path>
                              </svg>
                              <span class="marketingObjective_title">
                                <span>WEBSITE ENGAGEMENT</span>
                              </span>
                            </div>
                          </label>
                        </div>
                      </div>
                      <div
                          class="card"
                          :class="{
                          selected:
                            template.campaign_settings.marketing_objective ===
                            'BRAND_AWARENESS',
                        }"
                      >
                        <div class="card-body">
                          <label class="card-label">
                            <input
                                v-model="
                                template.campaign_settings.marketing_objective
                              "
                                class="baseRadio__radio-input"
                                type="radio"
                                name="marketing-objective"
                                @change="errors.marketing_objective = false"
                                value="BRAND_AWARENESS"
                            />
                            <div class="iconRadioButton__icon-container">
                              <svg
                                  width="32"
                                  height="32"
                                  viewBox="0 0 32 32"
                                  fill="currentColor"
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="marketingObjective_enabled-icon__IR6dm"
                              >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M18.0027 10.5665H10C7.79086 10.5665 6 12.3574 6 14.5665C6 16.7757 7.79086 18.5665 10 18.5665H12.0027V23.0573C12.0027 24.438 13.122 25.5573 14.5027 25.5573C15.8834 25.5573 17.0027 24.438 17.0027 23.0573V18.5665H18.0274L24.6516 22.0506C24.7638 22.0927 24.8828 22.1143 25.0027 22.1143C25.555 22.1143 26.0027 21.6666 26.0027 21.1143V8.00027C26.0027 7.88036 25.9811 7.76142 25.939 7.64915C25.7451 7.13203 25.1687 6.87002 24.6516 7.06394L18.0027 10.5573V10.5665ZM19.0027 11.2503L25.0027 8.00027V21.1143L19 17.9797L19.0027 11.2503ZM18 11.5665L18.0029 11.5576L19.0027 11.2503L18 11.5665ZM13 17.5665H10C8.34315 17.5665 7 16.2234 7 14.5665C7 12.9097 8.34315 11.5665 10 11.5665H18V17.5665H16.0027V23.0573C16.0027 23.8857 15.3311 24.5573 14.5027 24.5573C13.6743 24.5573 13.0027 23.8857 13.0027 23.0573V18.5573H16V17.5573H13V17.5665ZM13 17.5665V18.5573H13.0027V17.5665H13Z"
                                    fill="currentColor"
                                ></path>
                              </svg>
                              <span class="marketingObjective_title">
                                <span>BRAND AWARENESS</span>
                              </span>
                            </div>
                          </label>
                        </div>
                      </div>
                      <div
                          class="card"
                          :class="{
                          selected:
                            template.campaign_settings.marketing_objective ===
                            'MOBILE_APP_INSTALL',
                        }"
                      >
                        <div class="card-body">
                          <label class="card-label">
                            <input
                                v-model="
                                template.campaign_settings.marketing_objective
                              "
                                class="baseRadio__radio-input"
                                type="radio"
                                name="marketing-objective"
                                value="MOBILE_APP_INSTALL"
                                @change="errors.marketing_objective = false"
                            />
                            <div class="iconRadioButton__icon-container">
                              <svg
                                  width="32"
                                  height="32"
                                  viewBox="0 0 32 32"
                                  fill="currentColor"
                                  xmlns="http://www.w3.org/2000/svg"
                                  class="marketingObjective_enabled-icon__IR6dm"
                              >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M10 9V8C10 6.896 10.896 6 12 6H20C21.104 6 22 6.896 22 8V9H10ZM10 23H22V10H10V23ZM22 25C22 26.104 21.104 27 20 27H12C10.896 27 10 26.104 10 25V24H22V25ZM20 5H12C10.343 5 9 6.343 9 8V25C9 26.657 10.343 28 12 28H20C21.657 28 23 26.657 23 25V8C23 6.343 21.657 5 20 5ZM14.5 25.5C14.5 25.7761 14.7239 26 15 26H17C17.2761 26 17.5 25.7761 17.5 25.5C17.5 25.2239 17.2761 25 17 25H15C14.7239 25 14.5 25.2239 14.5 25.5ZM14.5 7.5C14.5 7.77614 14.7239 8 15 8H17C17.2761 8 17.5 7.77614 17.5 7.5C17.5 7.22386 17.2761 7 17 7H15C14.7239 7 14.5 7.22386 14.5 7.5Z"
                                    fill="currentColor"
                                ></path>
                              </svg>
                              <span class="marketingObjective_title">
                                <span>APP PROMOTION</span>
                              </span>
                            </div>
                          </label>
                        </div>
                      </div>
                    </div>
                    <div
                        class="mt-2 invalid-feedback"
                        :style="{
                        display: errors.marketing_objective ? 'block' : '',
                      }"
                    >
                      Select a Marketing Objective
                    </div>
                  </div>
                  <div class="mb-4">
                    <label class="col-form-label">Conversion Event</label>
                    <div>
                      <select
                          class="form-select"
                          v-model="template.campaign_settings.conversion_event"
                          @change="errors.conversion_event = false"
                          :class="{
                          'is-invalid':
                            !template.campaign_settings.conversion_event &&
                            errors.conversion_event,
                        }"
                      >
                        <option
                            v-for="(cnv, index) in conversionEventOptions"
                            :key="index"
                            :value="cnv"
                        >
                          {{ cnv.display_name }}
                        </option>
                      </select>
                      <div class="invalid-feedback">
                        Choose a Conversion Event
                      </div>
                    </div>
                  </div>
                </form>
                <div class="row">
                  <div
                      class="col-12 d-flex justify-content-between mt-5 align-items-center"
                  >
                    <div class="btn btn-warning" @click="changeStep(1)">
                      Prev
                    </div>
                    <div class="btn btn-primary" @click="changeStep(3)">
                      Next
                    </div>
                  </div>
                </div>
              </div>
              <!-- \Campaign Setting -->

              <!-- Targeting -->
              <div
                  class="tab-pane fade"
                  :class="{ show: activeTab == 3, active: activeTab == 3 }"
                  id="targeting"
                  role="tabpanel"
                  aria-labelledby="fb-camp-create-targeting-tab"
              >
                <div class="mb-4">
                  <div class="d-flex">
                    <label class="col-form-label" for="targeting_countries"
                    >Country ({{
                        template.targeting.excludes.country_targeting
                            ? "Exclude"
                            : "Include"
                      }})</label
                    >
                    <label
                        class="switch s-primary mb-0"
                        style="transform: translate(5px, 8px)"
                    ><input
                        type="checkbox"
                        v-model="
                          template.targeting.excludes.country_targeting
                        " /><span class="slider round"></span
                    ></label>
                  </div>
                  <div>
                    <countries-select
                        v-model="template.targeting.country_targeting"
                        :closeOnSelect="false"
                        :multiple="true"
                    />
                  </div>
                </div>
                <div class="mb-4">
                  <div class="d-flex">
                    <label class="col-form-label" data-v-76cda22f=""
                    >Platform ({{
                        template.targeting.excludes.platform_targeting
                            ? "Exclude"
                            : "Include"
                      }})</label
                    >
                    <label
                        data-bs-toggle="tooltip"
                        title="Only include option is available"
                        class="switch s-primary mb-0"
                        style="transform: translate(5px, 8px)"
                    ><input
                        type="checkbox"
                        disabled
                        v-model="
                          template.targeting.excludes.platform_targeting
                        " /><span class="slider round"></span
                    ></label>
                  </div>
                  <div class="d-flex gap-3 mt-1">
                    <div
                        class="card"
                        :class="{
                        selected: template.targeting.platform_targeting.desktop,
                      }"
                    >
                      <div class="card-body">
                        <label class="card-label">
                          <input
                              class="baseRadio__radio-input"
                              type="checkbox"
                              name="desktop_targeting"
                              value="DESK"
                              @change="errors.platform_targeting = false"
                              v-model="
                              template.targeting.platform_targeting.desktop
                            "
                          />
                          <div class="iconRadioButton__icon-container">
                            <svg
                                width="32"
                                height="33"
                                viewBox="0 0 32 33"
                                fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                  d="M14 24.5H18V25.5H14V24.5Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  d="M11 26.5H21V27.5H11V26.5Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  fill-rule="evenodd"
                                  clip-rule="evenodd"
                                  d="M25 9.5H7C5.89543 9.5 5 10.3954 5 11.5V20.5C5 21.6046 5.89543 22.5 7 22.5H25C26.1046 22.5 27 21.6046 27 20.5V11.5C27 10.3954 26.1046 9.5 25 9.5ZM7 8.5C5.34315 8.5 4 9.84315 4 11.5V20.5C4 22.1569 5.34315 23.5 7 23.5H25C26.6569 23.5 28 22.1569 28 20.5V11.5C28 9.84315 26.6569 8.5 25 8.5H7Z"
                                  fill="#212832"
                              ></path>
                            </svg>
                            <span class="marketingObjective_title">
                              <span class="mb-1">Desktop</span>
                            </span>
                          </div>
                        </label>
                      </div>
                    </div>
                    <div
                        class="card"
                        :class="{
                        selected: template.targeting.platform_targeting.mobile,
                      }"
                    >
                      <div class="card-body">
                        <label class="card-label">
                          <input
                              v-model="
                              template.targeting.platform_targeting.mobile
                            "
                              class="baseRadio__radio-input"
                              type="checkbox"
                              name="mobile_targeting"
                              @change="errors.platform_targeting = false"
                              value="PHON"
                          />
                          <div class="iconRadioButton__icon-container">
                            <svg
                                width="32"
                                height="33"
                                viewBox="0 0 32 33"
                                fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                  d="M17 23.5C17 24.0523 16.5523 24.5 16 24.5C15.4477 24.5 15 24.0523 15 23.5C15 22.9477 15.4477 22.5 16 22.5C16.5523 22.5 17 22.9477 17 23.5Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  d="M14 8C13.7239 8 13.5 8.22386 13.5 8.5C13.5 8.77614 13.7239 9 14 9H18C18.2761 9 18.5 8.77614 18.5 8.5C18.5 8.22386 18.2761 8 18 8H14Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  fill-rule="evenodd"
                                  clip-rule="evenodd"
                                  d="M12 5.5C10.3431 5.5 9 6.84315 9 8.5V24.5C9 26.1569 10.3431 27.5 12 27.5H20C21.6569 27.5 23 26.1569 23 24.5V8.5C23 6.84315 21.6569 5.5 20 5.5H12ZM20 6.5H12C10.8954 6.5 10 7.39543 10 8.5V24.5C10 25.6046 10.8954 26.5 12 26.5H20C21.1046 26.5 22 25.6046 22 24.5V8.5C22 7.39543 21.1046 6.5 20 6.5Z"
                                  fill="#212832"
                              ></path>
                            </svg>
                            <span class="marketingObjective_title">
                              <span class="mb-1">Mobile</span>
                            </span>
                          </div>
                        </label>
                      </div>
                    </div>
                    <div
                        class="card"
                        :class="{
                        selected: template.targeting.platform_targeting.tablet,
                      }"
                    >
                      <div class="card-body">
                        <label class="card-label">
                          <input
                              v-model="
                              template.targeting.platform_targeting.tablet
                            "
                              class="baseRadio__radio-input"
                              @change="errors.platform_targeting = false"
                              type="checkbox"
                              name="tablet_targeting"
                              value="TBLT"
                          />
                          <div class="iconRadioButton__icon-container">
                            <svg
                                width="32"
                                height="33"
                                viewBox="0 0 32 33"
                                fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                  d="M17 24.5C17 25.0523 16.5523 25.5 16 25.5C15.4477 25.5 15 25.0523 15 24.5C15 23.9477 15.4477 23.5 16 23.5C16.5523 23.5 17 23.9477 17 24.5Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  fill-rule="evenodd"
                                  clip-rule="evenodd"
                                  d="M16 25.5C16.5523 25.5 17 25.0523 17 24.5C17 23.9477 16.5523 23.5 16 23.5C15.4477 23.5 15 23.9477 15 24.5C15 25.0523 15.4477 25.5 16 25.5Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  d="M8 21.5H24V22.5H8V21.5Z"
                                  fill="#212832"
                              ></path>
                              <path
                                  fill-rule="evenodd"
                                  clip-rule="evenodd"
                                  d="M22 6.5H10C8.89543 6.5 8 7.39543 8 8.5V24.5C8 25.6046 8.89543 26.5 10 26.5H22C23.1046 26.5 24 25.6046 24 24.5V8.5C24 7.39543 23.1046 6.5 22 6.5ZM10 5.5C8.34315 5.5 7 6.84315 7 8.5V24.5C7 26.1569 8.34315 27.5 10 27.5H22C23.6569 27.5 25 26.1569 25 24.5V8.5C25 6.84315 23.6569 5.5 22 5.5H10Z"
                                  fill="#212832"
                              ></path>
                            </svg>
                            <span class="marketingObjective_title">
                              <span class="mb-1">Tablet</span>
                            </span>
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>
                  <div
                      class="mt-2 invalid-feedback"
                      :style="{
                      display: errors.platform_targeting ? 'block' : '',
                    }"
                  >
                    Select at least 1 platform
                  </div>
                </div>
                <div class="mb-4">
                  <div class="d-flex">
                    <label class="col-form-label"
                    >Operating System ({{
                        template.targeting.excludes.os_targeting
                            ? "Exclude"
                            : "Include"
                      }})</label
                    >
                    <label
                        class="switch s-primary mb-0"
                        style="transform: translate(5px, 8px)"
                    ><input
                        type="checkbox"
                        v-model="
                          template.targeting.excludes.os_targeting
                        " /><span class="slider round"></span
                    ></label>
                  </div>
                  <div>
                    <options-select
                        v-model="template.targeting.os_targeting"
                        optionName="taboola_os_targeting"
                        :multiple="true"
                    />
                  </div>
                </div>
                <div class="mb-4">
                  <div class="d-flex">
                    <label class="col-form-label"
                    >Browsers ({{
                        template.targeting.excludes.browser_targeting
                            ? "Exclude"
                            : "Include"
                      }})</label
                    >
                    <label
                        class="switch s-primary mb-0"
                        style="transform: translate(5px, 8px)"
                    ><input
                        type="checkbox"
                        v-model="
                          template.targeting.excludes.browser_targeting
                        " /><span class="slider round"></span
                    ></label>
                  </div>
                  <div>
                    <options-select
                        v-model="template.targeting.browser_targeting"
                        optionName="taboola_browser_targeting"
                        :multiple="true"
                    />
                  </div>
                </div>
                <div class="row">
                  <div
                      class="col-12 d-flex justify-content-between mt-5 align-items-center"
                  >
                    <div class="btn btn-warning" @click="changeStep(2)">
                      Prev
                    </div>
                    <div class="btn btn-primary" @click="changeStep(4)">
                      Next
                    </div>
                  </div>
                </div>
              </div>
              <!-- /Targeting -->

              <!-- Budget -->
              <div
                  class="tab-pane fade"
                  :class="{ show: activeTab == 4, active: activeTab == 4 }"
                  id="targeting"
                  role="tabpanel"
                  aria-labelledby="fb-camp-create-targeting-tab"
              >
                <form>
                  <div class="mb-4">
                    <label class="col-form-label" for="cpc">CPC (€ EUR)</label>
                    <div>
                      <input
                          type="text"
                          id="cpc"
                          class="form-control"
                          placeholder="Cost Per Click"
                          v-model="template.budget.cpc"
                          @keydown="errors.cpc = false"
                          :class="{
                          'is-invalid': errors.cpc,
                        }"
                      />
                      <div class="invalid-feedback">Enter a Bid Amount</div>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label class="col-form-label">Spending Limit Model</label>
                    <select
                        class="form-select"
                        v-model="template.budget.spending_limit_model"
                    >
                      <option value="NONE">None</option>
                      <option value="MONTHLY">Monthly Limit</option>
                      <option value="ENTIRE">Lifetime Limit</option>
                    </select>
                  </div>
                  <div
                      class="mb-4"
                      v-if="template.budget.spending_limit_model !== 'NONE'"
                  >
                    <label class="col-form-label">Spending Limit (€ EUR)</label>
                    <div>
                      <input
                          type="text"
                          class="form-control"
                          placeholder="Spending Limit"
                          v-model="template.budget.spending_limit"
                          @keydown="errors.spending_limit = false"
                          :class="{
                          'is-invalid': errors.spending_limit,
                        }"
                      />
                      <div class="invalid-feedback">Enter a Limit</div>
                    </div>
                  </div>
                  <div class="mb-4">
                    <label class="col-form-label">Daily Budget (€ EUR)</label>
                    <div>
                      <input
                          type="text"
                          class="form-control"
                          placeholder="Daily Budget"
                          v-model="template.budget.daily_budget"
                          @keydown="errors.daily_budget = false"
                          :class="{
                          'is-invalid': errors.daily_budget,
                        }"
                      />
                      <div class="invalid-feedback">Enter a Budget</div>
                    </div>
                  </div>
                </form>

                <div class="row">
                  <div
                      class="col-12 d-flex justify-content-between mt-5 align-items-center"
                  >
                    <div class="btn btn-warning" @click="changeStep(3)">
                      Prev
                    </div>
                    <div class="btn btn-primary" @click="changeStep(5)">
                      Next
                    </div>
                  </div>
                </div>
              </div>
              <!-- \Budget -->

              <!-- Creatives -->
              <div
                  class="tab-pane fade"
                  :class="{ show: activeTab == 5, active: activeTab == 5 }"
                  id="targeting"
                  role="tabpanel"
                  aria-labelledby="fb-camp-create-targeting-tab"
              >
                <div class="accordion" id="accordionExample">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                      <button
                          class="accordion-button collapsed"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#collapseTwo"
                          aria-expanded="false"
                          aria-controls="collapseTwo"
                      >
                        Content
                      </button>
                    </h2>
                    <div
                        id="collapseTwo"
                        class="accordion-collapse collapse"
                        aria-labelledby="headingTwo"
                        data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <div class="mb-3">
                          <label for="textarea" class="col-form-label"
                          >Headlines</label
                          >
                          <div>
                            <textarea
                                v-model="headlines"
                                class="form-control"
                                style="height: 95px"
                            ></textarea>
                            <div class="mt-1">
                              <small id="emailHelp1" class="block text-muted"
                              >Headlines should be separated by new line. You
                                can use the following placeholders for the
                                headline [keyword] and [brand] .
                              </small>
                            </div>
                          </div>
                        </div>
                        <div class="row mb-4">
                          <div class="col-6">
                            <label class="col-form-label"
                            >Description (Optional)</label
                            >
                            <div>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="Description"
                                  v-model="template.description"
                              />
                            </div>
                          </div>
                          <div class="col-6">
                            <label class="col-form-label"
                            >CTA Button (Optional)</label
                            >
                            <div>
                              <options-select
                                  v-model="template.cta"
                                  :closeOnSelect="true"
                                  optionName="taboola_cta_types"
                                  :multiple="false"
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                      <button
                          class="accordion-button collapsed"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#collapseThree"
                          aria-expanded="false"
                          aria-controls="collapseThree"
                      >
                        Image & Keywords
                      </button>
                    </h2>
                    <div
                        id="collapseThree"
                        class="accordion-collapse collapse"
                        aria-labelledby="headingThree"
                        data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <image-keywords
                            :showSubmitButton="false"
                            ref="imageKeywordsComponent"
                            :maxSelectableKeywords="50"
                            @submitKeywordsWithImages="addKeywordsSelected"
                            :submitButtonText="'Submit'"
                        />
                      </div>
                    </div>
                  </div>
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button
                          class="accordion-button collapsed"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#collapseOne"
                          aria-expanded="true"
                          aria-controls="collapseOne"
                          @click="imageKeywordsComponent.submitSelectedKeywords()"
                      >
                        Adaccount & Domains
                      </button>
                    </h2>
                    <div
                        id="collapseOne"
                        class="accordion-collapse collapse"
                        aria-labelledby="headingOne"
                        data-bs-parent="#accordionExample"
                    >
                      <div class="accordion-body">
                        <div class="col-md-12">
                          <div class="mb-4">
                            <div class="d-flex">
                              <label class="col-form-label"> Account</label>
                            </div>
                            <div>
                              <options-select
                                  v-model="ad_account"
                                  :closeOnSelect="true"
                                  optionName="taboola_adaccounts"
                                  :multiple="false"
                              />
                            </div>
                          </div>
                          <div class="form-group mb-4">
                            <!-- <label>Image Source</label> -->

                            <div
                                role="radiogroup"
                                tabindex="-1"
                                class="bv-no-focus-ring"
                            >
                              <div
                                  class="radio-classic radio-primary custom-control d-inline-flex custom-radio me-3"
                              >
                                <input
                                    type="radio"
                                    class="custom-control-input"
                                    value="1"
                                    id="rdo1"
                                    name="rdoinline"
                                    v-model="domainRadio"
                                />
                                <label class="custom-control-label" for="rdo1"
                                >Select Domain</label
                                >
                              </div>
                              <div
                                  class="radio-classic radio-primary custom-control d-inline-flex custom-radio me-3"
                              >
                                <input
                                    type="radio"
                                    class="custom-control-input mb-2"
                                    value="2"
                                    id="rdo2"
                                    name="rdoinline"
                                    v-model="domainRadio"
                                />
                                <label class="custom-control-label" for="rdo2"
                                >Insert Parking Domain</label
                                >
                              </div>
                            </div>
                            <div v-if="domainRadio == 1">
                              <domains-select
                                  v-model="domain"
                                  :multiple="false"
                                  :closeOnSelect="true"
                              />
                              <button
                                  type="button"
                                  class="btn btn-outline-primary btn-sm my-2 me-2"
                                  :class="{ disabled: !domain || !ad_account }"
                                  @click="handleSelect"
                              >
                                Select Account & Domain
                              </button>
                              <div class="table-responsive mt-2">
                                <table
                                    role="table"
                                    aria-busy="false"
                                    aria-colcount="5"
                                    class="table table-bordered"
                                >
                                  <thead role="rowgroup">
                                  <tr role="row">
                                    <th
                                        role="columnheader"
                                        scope="col"
                                        aria-colindex="1"
                                    >
                                      <div>Ad Account</div>
                                    </th>
                                    <th
                                        role="columnheader"
                                        scope="col"
                                        aria-colindex="2"
                                    >
                                      <div>Domain</div>
                                    </th>
                                    <th
                                        role="columnheader"
                                        scope="col"
                                        aria-colindex="5"
                                        aria-label="Action"
                                        class="text-center"
                                    >
                                      <div></div>
                                    </th>
                                  </tr>
                                  </thead>
                                  <tbody role="rowgroup">
                                  <tr
                                      role="row"
                                      v-for="(
                                        row, index
                                      ) in account_and_domain_table"
                                      :key="index"
                                  >
                                    <td aria-colindex="1" role="cell">
                                      {{ row.account.name }}
                                    </td>
                                    <td aria-colindex="3" role="cell">
                                      {{ row.domain.name }}
                                    </td>
                                    <td
                                        aria-colindex="5"
                                        role="cell"
                                        class="text-center"
                                    >
                                      <svg
                                          @click="
                                            account_and_domain_table.splice(
                                              index,
                                              1
                                            )
                                          "
                                          xmlns="http://www.w3.org/2000/svg"
                                          width="24"
                                          height="24"
                                          viewBox="0 0 24 24"
                                          fill="none"
                                          stroke="currentColor"
                                          stroke-width="2"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"
                                          class="feather feather-x t-icon t-hover-icon"
                                      >
                                        <line
                                            x1="18"
                                            y1="6"
                                            x2="6"
                                            y2="18"
                                        ></line>
                                        <line
                                            x1="6"
                                            y1="6"
                                            x2="18"
                                            y2="18"
                                        ></line>
                                      </svg>
                                    </td>
                                  </tr>
                                  </tbody>
                                </table>
                              </div>
                            </div>
                            <form class="mt-3" v-else>
                              <div
                                  class="row mb-2"
                                  v-for="(keyword, index) in selectedKeywords"
                                  :key="index"
                              >
                                <label
                                    class="col-sm-2 col-form-label col-form-label-sm"
                                    for="colFormLabelSm"
                                >{{ keyword.keyword }}</label
                                >
                                <div class="col-sm-10">
                                  <input
                                      type="text"
                                      class="form-control"
                                      placeholder="Domain URL"
                                      v-model="parking_domains[keyword.keyword]"
                                  />
                                </div>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div
                      class="col-12 d-flex justify-content-between mt-5 align-items-center"
                  >
                    <div class="btn btn-warning" @click="changeStep(4)">
                      Prev
                    </div>
                    <div class="btn btn-primary" @click="submitForm">
                      Create Campaigns
                    </div>
                  </div>
                </div>
              </div>
              <!-- \Creatives -->
            </div>
            <!-- /content -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.simple-tab .nav-tabs li a {
  color: rgb(59, 63, 92);
  font-size: 16px;
}
.tooltip-inner {
  max-width: 500px;
}
.switch .slider:before {
  background-color: #41b883;
}
.switch input:checked + .slider:before {
  background-color: #fff;
}
.switch.s-primary input:checked + .slider {
  background-color: #e7515a;
}
.card {
  width: 118px;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
  padding: 0;
}
.card-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0px;
}
.card-label {
  width: 100%;
  padding: 10px;
  margin-bottom: 0;
}
.fade-out {
  opacity: 1;
  transition: opacity 0.5s ease-out;
}

.fade-out.hide {
  opacity: 0;
}

.iconRadioButton__icon-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.marketingObjective_title {
  margin-top: 0.5rem;
  text-align: center;
  font-size: 12px;
}

.selected {
  border-color: #0076ff;
}
</style>
<script setup>
import { useStore } from "vuex";
import { ref, toRef, onMounted, computed, watch } from "vue";
import { Form, Field, ErrorMessage } from "vee-validate";
import { showMessage, askForConfirmation } from "@/utils/toast";
import "@/assets/sass/components/tabs-accordian/custom-tabs.scss";
import jobberApi from "@/services/api/jobber";
import { useCreateJobber } from "@/composables/use-jobber";
import useBsModal from "@/composables/useBsStaticModal";
import SubmitButton from "@/components/form/SubmitButton.vue";
import OptionsSelect from "@/components/form/taboola/OptionsSelect.vue";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import templatesApi from "@/services/api/taboola/templates";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import ImageKeywords from "@/components/imagekeywords/ImageKeywords.vue";
import DomainsSelect from "@/components/form/taboola/DomainsSelect.vue";
import Multiselect from "@suadelabs/vue3-multiselect";
import UserSelect from "@/components/form/facebook/UserSelect.vue";
import "@suadelabs/vue3-multiselect/dist/vue3-multiselect.css";
import optionsApi from "@/services/api/options";
import keywordApi from "@/services/api/database/keywords";
import { initTooltip } from "@/utils/tooltip";

const errors = ref({});
const domainRadio = ref(1);
const activeTab = ref(1);
const selectedKeywords = ref([]);
const isSubmitting = ref(false);
const imageKeywordsComponent = ref(0);
const store = useStore();
const emit = defineEmits(["closeStaticModal", "JobberCreated", "saveTemplate"]);
const props = defineProps({ isShow: Boolean });
const { staticModalRef, closeStaticModal } = useBsModal(
    toRef(props, "isShow"),
    emit
);
const showAlertFlag = ref(false);
const user = ref(null);
const description = ref("");
const account_and_domain_table = ref([]);
const headlines = ref("");
const domain = ref(null);
const parking_domains = ref({});
const ad_account = ref({
  name: "BidBerry srl - SY1 - Local Services - SC",
  value: "bidberrysrl-sy1-localservices-sc",
});
const template = ref({
  cta: {
    name: "NONE",
    value: "None",
  },
  description: "",
  campaign_settings: {
    name_suffix: "",
    marketing_objective: "",
    brand_name: "",
    conversion_event: {
      id: 676386,
      status: "ACTIVE",
      display_name: "tonic_conv",
      include_in_total_conversions: true,
    },
  },
  targeting: {
    country_targeting: [],
    platform_targeting: {
      desktop: false,
      mobile: false,
      tablet: false,
    },
    browser_targeting: [],
    os_targeting: [],
    excludes: {
      country_targeting: false,
      platform_targeting: false,
      browser_targeting: false,
      os_targeting: false,
    },
  },
  budget: {
    cpc: "",
    spending_limit: "",
    spending_limit_model: "NONE",
    daily_budget: "",
  },
});
const selectedTemplate = ref();
const marketingOptions = ref([]);
const conversionEventOptions = ref([]);
const templateOptions = ref([]);
const category = ref(null);
const language = ref(null);
const country = ref([]);

watch(selectedTemplate, (val) => {
  if (val) template.value = val.value;
  errors.value = {};
});

watch(user, (val) => {
  if (val) errors.value.user = false;
});

watch(
    () => [country.value, language.value, category.value],
    () => {
      loadItems({
        columnFilters: {
          category_id: category.value,
          country_ids: [country.value],
          language_id: language.value,
        },
      });
    }
);

const changeStep = (value) => {
  activeTab.value = value;
};

// here we call the submit button from inside the Image & Keyword Component
// const getKeywordsAndSubmitForm = () => {
//   imageKeywordsComponent.value.submitSelectedKeywords();
//   submitForm();
// };

const addKeywordsSelected = async (keywords) => {
  try {
    const res = await keywordApi.createKeywordsInCampaignGenerator(keywords);
    selectedKeywords.value = res.data;
  } catch (e) {
    showMessage(e.message, "error");
    return;
  }
};

const resetFormData = () => {
  activeTab.value = 1;
  selectedKeywords.value = [];
  isSubmitting.value = false;
};
const closeModal = () => {
  emit("closeModal");
};

const submitForm = async () => {
  imageKeywordsComponent.value.submitSelectedKeywords();
  if (!validateForm()) {
    showAlertFlag.value = true;
    setTimeout(() => {
      showAlertFlag.value = false;
    }, 5000);
    return;
  }

  if (headlines.value.trim() === "") {
    showMessage("At least one Headline is required", "error");
    return;
  }

  let ad_accounts;
  const uses_parking_domain = domainRadio.value == 2;
  // if uses Pargking Domain
  if (uses_parking_domain) {
    ad_accounts = [{ account: ad_account.value }];
  } else ad_accounts = account_and_domain_table.value;

  if (ad_accounts.length === 0) {
    showMessage("At least one account is required", "error");
    return;
  }

  const confirmation = await askForConfirmation(
      "Do you want to store all the current template data into a new one? ",
      "Save new template",
      "Yes, create new template",
      "No, continue without saving",
      "",
      "question"
  );
  if (confirmation.isConfirmed) {
    emit("saveTemplate", template.value);
    return;
  }

  submitFormDirectly();
};

const loadSettings = async () => {
  try {
    let res = await optionsApi.getOption("taboola_marketing_objective");
    marketingOptions.value = res.data.value;
    res = await optionsApi.getOption("taboola_conversion_events");
    conversionEventOptions.value = res.data.value
        .filter((obj) => obj.status.toUpperCase() === "ACTIVE")
        .map((obj) => {
          const { id, display_name, status, include_in_total_conversions } = obj;
          const newObj = {
            id,
            display_name,
            status,
            include_in_total_conversions,
          };
          return newObj;
        });
  } catch (error) {
    showMessage(error.message, "error");
  }
};

const loadItems = async (params = {}) => {
  try {
    const res = await templatesApi.getTemplates(params);
    templateOptions.value = [
      {
        value: template.value,
        name: "Empty template",
      },
    ].concat(
        res.data.map((t) => {
          return {
            value: t.template,
            name: t.description,
          };
        })
    );
    selectedTemplate.value = templateOptions.value[0];
  } catch (error) {
    showMessage(error.message, "error");
    console.log(error);
  }
};

const handleSelect = () => {
  if (
      account_and_domain_table.value.some(
          (item) =>
              item.account === ad_account.value && item.domain === domain.value
      )
  ) {
    showMessage(
        "That combination of account and domain is already on the table!",
        "error"
    );
    return;
  }
  account_and_domain_table.value.push({
    account: ad_account.value,
    domain: domain.value,
  });
  console.log("account_and_domain_table", account_and_domain_table);
};

// This method is created to submit the form if the person decides to create a new template
// then we have to wait for that first
const submitFormDirectly = async () => {
  let ad_accounts;
  const uses_parking_domain = domainRadio.value == 2;
  // if uses Pargking Domain
  if (uses_parking_domain) {
    ad_accounts = [{ account: ad_account.value }];
  } else ad_accounts = account_and_domain_table.value;

  if (!template.value.cta) {
    template.value.cta = {
      name: "NONE",
      value: "None",
    };
  }

  const payload = {
    args: {
      headlines: headlines.value.split("\n"),
      keywords: selectedKeywords.value,
      template: template.value,
      uses_parking_domain,
      parking_domains: uses_parking_domain ? parking_domains.value : null,
      ad_accounts,
      user: user.value,
    },
    class: "Taboola\\CreateCampaigns",
    description: description.value,
  };

  try {
    const response = await jobberApi.createJobber(payload);

    if (response.success) {
      showMessage("Jobber created successfully");
      emit("JobberCreated");
      closeModal();
      resetFormData();
    }
  } catch (e) {
    showMessage(e?.response?.data?.message, "error");
  }
};

const validateForm = () => {
  errors.value = {};
  if (!template.value.campaign_settings.conversion_event)
    errors.value.conversion_event = true;
  if (template.value.campaign_settings.brand_name == "")
    errors.value.brand_name = true;
  if (!template.value.campaign_settings.marketing_objective)
    errors.value.marketing_objective = true;
  const { desktop, mobile, tablet } =
      template.value.targeting.platform_targeting;
  if (!desktop && !mobile && !tablet) errors.value.platform_targeting = true;
  if (!template.value.budget.cpc) errors.value.cpc = true;
  if (
      template.value.budget.spending_limit_model !== "NONE" &&
      !template.value.budget.spending_limit
  )
    errors.value.spending_limit = true;
  if (!template.value.budget.daily_budget) errors.value.daily_budget = true;
  if (!user.value) errors.value.user = true;

  console.log("errors", errors.value);

  return Object.keys(errors.value).length === 0;
};

onMounted(() => {
  initTooltip();
  loadItems();
  loadSettings();
});

defineExpose({ submitFormDirectly });
</script>
