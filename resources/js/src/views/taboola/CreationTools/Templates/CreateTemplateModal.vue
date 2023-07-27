<template>
  <div
      ref="modalRef"
      id="templateModal"
      class="modal fade"
      aria-labelledby="templateModalLabel"
      aria-hidden="true"
  >
    <div class="modal-dialog modal-md modal-dialog-centered">
      <form @submit="onSubmit">
        <Field name="id" type="hidden" v-model="id" />
        <div class="modal-content mailbox-popup" style="height: 85vh">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ (id ? "Update" : "Create") + " Template" }}
            </h5>
            <button
                type="button"
                data-dismiss="modal"
                data-bs-dismiss="modal"
                aria-label="Close"
                class="btn-close"
            ></button>
          </div>
          <div class="modal-body modal-fullscreen">
            <div class="add-contact-box">
              <div class="panel-body vertical-pill tabs">
                <div class="row mb-4 mt-3">
                  <div class="col-sm-3 col-12">
                    <div
                        class="nav flex-column nav-pills mb-sm-0 mb-3"
                        id="v-pills-tab"
                        role="tablist"
                        aria-orientation="vertical"
                    >
                      <a
                          class="nav-link active mb-2"
                          id="v-pills-template-tab"
                          data-bs-toggle="pill"
                          href="#v-pills-template"
                          role="tab"
                          aria-controls="v-pills-template"
                          aria-selected="true"
                      >Template Settings</a
                      >
                      <a
                          class="nav-link mb-2"
                          id="v-pills-campaign-tab"
                          data-bs-toggle="pill"
                          href="#v-pills-campaign"
                          role="tab"
                          aria-controls="v-pills-campaign"
                          aria-selected="false"
                      >Campaign Setup</a
                      >
                      <a
                          class="nav-link mb-2"
                          id="v-pills-targeting-tab"
                          data-bs-toggle="pill"
                          href="#v-pills-targeting"
                          role="tab"
                          aria-controls="v-pills-targeting"
                          aria-selected="false"
                      >Campaign Targeting</a
                      >
                      <a
                          class="nav-link"
                          id="v-pills-budget-tab"
                          data-bs-toggle="pill"
                          href="#v-pills-budget"
                          role="tab"
                          aria-controls="v-pills-budget"
                          aria-selected="false"
                      >Budget</a
                      >
                    </div>
                  </div>

                  <div class="col-sm-9 col-12">
                    <div class="tab-content" id="v-pills-tabContent">
                      <div
                          class="tab-pane fade show active"
                          id="v-pills-template"
                          role="tabpanel"
                          aria-labelledby="v-pills-template-tab"
                      >
                        <form>
                          <div class="mb-4">
                            <label class="col-form-label" for="template_name"
                            >Description</label
                            >
                            <div class="row">
                              <div class="col-12">
                                <input
                                    type="text"
                                    id="template_name"
                                    class="form-control mb-4"
                                    placeholder="Description"
                                    v-model="description"
                                />
                              </div>

                              <div class="col-12">
                                <div class="form-group mb-4">
                                  <label>Language</label>

                                  <languages-select v-model="language" />
                                </div>
                              </div>

                              <div class="col-12">
                                <div class="form-group mb-4">
                                  <label>Category</label>

                                  <categories-select
                                      v-model="category"
                                      :closeOnSelect="true"
                                      :multiple="false"
                                  />
                                </div>
                              </div>

                              <div class="col-12">
                                <div class="form-group mb-4">
                                  <label>Countries</label>

                                  <countries-select
                                      v-model="countries"
                                      :closeOnSelect="false"
                                      :multiple="true"
                                  />
                                </div>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
                      <div
                          class="tab-pane fade"
                          id="v-pills-campaign"
                          role="tabpanel"
                          aria-labelledby="v-pills-campaign-tab"
                      >
                        <form>
                          <div class="mb-4">
                            <label class="col-form-label" for="name_suffix"
                            >Name suffix</label
                            >
                            <div>
                              <input
                                  type="text"
                                  id="name_suffix"
                                  class="form-control"
                                  placeholder="Name Suffix"
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
                                  type="text"
                                  id="brand_name"
                                  class="form-control"
                                  placeholder="Brand Name"
                                  v-model="template.campaign_settings.brand_name"
                              />
                            </div>
                          </div>
                          <div class="mb-4">
                            <label
                                class="col-form-label"
                                for="marketing_objective"
                            >Marketing Objective</label
                            >
                            <div class="d-flex gap-3 mt-1">
                              <div
                                  class="card"
                                  :class="{
                                  selected:
                                    template.campaign_settings
                                      .marketing_objective ===
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
                                        v-model="
                                        template.campaign_settings
                                          .marketing_objective
                                      "
                                    />
                                    <div
                                        class="iconRadioButton__icon-container"
                                    >
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
                                    template.campaign_settings
                                      .marketing_objective ===
                                    'ONLINE_PURCHASES',
                                }"
                              >
                                <div class="card-body">
                                  <label class="card-label">
                                    <input
                                        v-model="
                                        template.campaign_settings
                                          .marketing_objective
                                      "
                                        class="baseRadio__radio-input"
                                        type="radio"
                                        name="marketing-objective"
                                        value="ONLINE_PURCHASES"
                                    />
                                    <div
                                        class="iconRadioButton__icon-container"
                                    >
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
                                    template.campaign_settings
                                      .marketing_objective ===
                                    'DRIVE_WEBSITE_TRAFFIC',
                                }"
                              >
                                <div class="card-body">
                                  <label class="card-label">
                                    <input
                                        v-model="
                                        template.campaign_settings
                                          .marketing_objective
                                      "
                                        class="baseRadio__radio-input"
                                        type="radio"
                                        name="marketing-objective"
                                        value="DRIVE_WEBSITE_TRAFFIC"
                                    />
                                    <div
                                        class="iconRadioButton__icon-container"
                                    >
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
                                    template.campaign_settings
                                      .marketing_objective ===
                                    'BRAND_AWARENESS',
                                }"
                              >
                                <div class="card-body">
                                  <label class="card-label">
                                    <input
                                        v-model="
                                        template.campaign_settings
                                          .marketing_objective
                                      "
                                        class="baseRadio__radio-input"
                                        type="radio"
                                        name="marketing-objective"
                                        value="BRAND_AWARENESS"
                                    />
                                    <div
                                        class="iconRadioButton__icon-container"
                                    >
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
                                    template.campaign_settings
                                      .marketing_objective ===
                                    'MOBILE_APP_INSTALL',
                                }"
                              >
                                <div class="card-body">
                                  <label class="card-label">
                                    <input
                                        v-model="
                                        template.campaign_settings
                                          .marketing_objective
                                      "
                                        class="baseRadio__radio-input"
                                        type="radio"
                                        name="marketing-objective"
                                        value="MOBILE_APP_INSTALL"
                                    />
                                    <div
                                        class="iconRadioButton__icon-container"
                                    >
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
                          </div>
                          <div class="mb-4">
                            <label
                                class="col-form-label"
                                for="marketing_objective"
                            >Conversion Event</label
                            >
                            <div>
                              <select
                                  class="form-select"
                                  v-model="
                                  template.campaign_settings.conversion_event
                                "
                              >
                                <option selected disabled value="">
                                  Choose One
                                </option>
                                <option
                                    v-for="(cnv, index) in conversionEventOptions"
                                    :key="index"
                                    :value="cnv"
                                >
                                  {{ cnv.display_name }}
                                </option>
                              </select>
                            </div>
                          </div>
                        </form>
                      </div>
                      <div
                          class="tab-pane fade"
                          id="v-pills-targeting"
                          role="tabpanel"
                          aria-labelledby="v-pills-targeting-tab"
                      >
                        <div class="mb-4">
                          <div class="d-flex">
                            <label
                                class="col-form-label"
                                for="targeting_countries"
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
                                selected:
                                  template.targeting.platform_targeting.desktop,
                              }"
                            >
                              <div class="card-body">
                                <label class="card-label">
                                  <input
                                      class="baseRadio__radio-input"
                                      type="checkbox"
                                      name="desktop_targeting"
                                      value="DESK"
                                      v-model="
                                      template.targeting.platform_targeting
                                        .desktop
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
                                selected:
                                  template.targeting.platform_targeting.mobile,
                              }"
                            >
                              <div class="card-body">
                                <label class="card-label">
                                  <input
                                      v-model="
                                      template.targeting.platform_targeting
                                        .mobile
                                    "
                                      class="baseRadio__radio-input"
                                      type="checkbox"
                                      name="mobile_targeting"
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
                                selected:
                                  template.targeting.platform_targeting.tablet,
                              }"
                            >
                              <div class="card-body">
                                <label class="card-label">
                                  <input
                                      v-model="
                                      template.targeting.platform_targeting
                                        .tablet
                                    "
                                      class="baseRadio__radio-input"
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
                        </div>
                        <div class="mb-4">
                          <div class="d-flex">
                            <label class="col-form-label"
                            >Operating System({{
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
                            >Browsers({{
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
                      </div>
                      <div
                          class="tab-pane fade"
                          id="v-pills-budget"
                          role="tabpanel"
                          aria-labelledby="v-pills-budget-tab"
                      >
                        <form>
                          <div class="mb-4">
                            <label class="col-form-label" for="cpc"
                            >CPC (€ EUR)</label
                            >
                            <div>
                              <input
                                  type="text"
                                  id="cpc"
                                  class="form-control"
                                  placeholder="Cost Per Click"
                                  v-model="template.budget.cpc"
                              />
                              <div class="invalid-feedback">
                                Enter a Bid Amount
                              </div>
                            </div>
                          </div>
                          <div class="mb-4">
                            <label class="col-form-label"
                            >Spending Limit Model</label
                            >
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
                              v-if="
                              template.budget.spending_limit_model !== 'NONE'
                            "
                          >
                            <label class="col-form-label"
                            >Spending Limit (€ EUR)</label
                            >
                            <div>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="Spending Limit"
                                  v-model="template.budget.spending_limit"
                              />
                              <div class="invalid-feedback">Enter a Limit</div>
                            </div>
                          </div>
                          <div class="mb-4">
                            <label class="col-form-label"
                            >Daily Budget (€ EUR)</label
                            >
                            <div>
                              <input
                                  type="text"
                                  class="form-control"
                                  placeholder="Daily Budget"
                                  v-model="template.budget.daily_budget"
                              />
                              <div class="invalid-feedback">Enter a Budget</div>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
                type="button"
                class="btn btn-default"
                :class="{ disabled: store.getters.isLoading }"
                data-dismiss="modal"
                data-bs-dismiss="modal"
            >
              Discard
            </button>
            <button
                type="button"
                class="btn btn-primary"
                :class="{ disabled: store.getters.isLoading }"
                @click.prevent="createTemplate"
            >
              {{ id ? "Update Template" : "Add New Template" }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.vertical-pill .nav-pills .nav-link.active,
.vertical-pill .nav-pills .show > .nav-link {
  background-color: #4361ee !important;
  border-color: #4361ee;
  color: #fff;
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
import { ref, toRef, onMounted } from "vue";
import { showMessage } from "@/utils/toast";
import useBsModal from "@/composables/useBsModal";
import optionsApi from "@/services/api/options";
import SubmitButton from "@/components/form/SubmitButton.vue";
import OptionsSelect from "@/components/form/taboola/OptionsSelect.vue";
import CountriesSelect from "@/components/form/CountriesSelect.vue";
import templatesApi from "@/services/api/taboola/templates";
import CategoriesSelect from "@/components/form/CategoriesSelect.vue";
import LanguagesSelect from "@/components/form/LanguagesSelect.vue";
import { Field } from "vee-validate";
import { initTooltip } from "@/utils/tooltip";

const category = ref(null);
const language = ref(null);
const countries = ref([]);
const emit = defineEmits(["closeModal", "refreshList"]);
const store = useStore();

const props = defineProps({ isShow: Boolean });

const { modalRef, closeModal } = useBsModal(toRef(props, "isShow"), emit);

const id = ref(null);
const description = ref("");
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
const marketingOptions = ref([]);
const conversionEventOptions = ref([]);

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

const createTemplate = async () => {
  if (description.value.trim() === "") {
    showMessage("Description can not be empty", "error");
    return;
  }
  const payload = {
    id: id.value,
    template: template.value,
    description: description.value,
    language: language.value,
    category: category.value,
    countries: countries.value,
  };
  let response;
  if (id.value) response = await templatesApi.updateTemplate(payload);
  else response = await templatesApi.createTemplate(payload);
  if (response.success) {
    showMessage(
        "Template " + (id.value ? "updated" : "created") + " successfully"
    );
    emit("refreshList");
  } else {
    showMessage(
        "Could not" +
        (id.value ? "update" : "create") +
        " template, please try again later",
        "error"
    );
  }
  closeModal();
};

const setData = (data) => {
  console.log({ data });
  id.value = data.id;
  description.value = data.description;
  template.value = data.template;
  language.value = data.language;
  category.value = data.category;
  countries.value = data.countries;
};

const emptyData = () => {
  id.value = null;
  description.value = "";
  category.value = null;
  language.value = null;
  countries.value = [];
  template.value = {
    campaign_settings: {
      name_suffix: "",
      marketing_objective: "",
      brand_name: "",
      conversion_event: "",
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
      spending_limit_model: "ENTIRE",
      daily_budget: "",
    },
  };
};

onMounted(() => {
  loadSettings();
  initTooltip();
});

defineExpose({ setData, emptyData });
</script>
