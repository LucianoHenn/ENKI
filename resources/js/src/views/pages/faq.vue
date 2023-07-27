<template>
  <div class="layout-px-spacing">
    <teleport to="#breadcrumb">
      <ul class="navbar-nav flex-row">
        <li>
          <div class="page-header">
            <nav class="breadcrumb-one" aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="javascript:;">Pages</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                  <span>FAQ</span>
                </li>
              </ol>
            </nav>
          </div>
        </li>
      </ul>
    </teleport>

    <div class="fq-header-wrapper">
      <div class="container">
        <div class="row">
          <div class="col-md-6 align-self-center order-md-0 order-1">
            <h1 class="">FAQ</h1>
            <p class="">
              Get Instant Answer From Popular Question Asked By Other Users
            </p>
            <button class="btn btn-default">Start Learning</button>
          </div>
          <div class="col-md-6 order-md-0 order-0">
            <div class="banner-img">
              <img
                  src="https://demo.cork-vue3.sbthemes.com/img/faq.69be1f77.svg"
                  class="d-block"
                  alt="header-image"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="faq container">
      <div class="faq-layouting layout-spacing">
        <div class="fq-comman-question-wrapper">
          <div class="row">
            <div class="col-md-12">
              <h3>Installation Based Problems</h3>
              <div class="row">
                <div class="col-md-6">
                  <ul class="">
                    <li class="list-unstyled">
                      <div class="icon-svg">
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
                            class="feather feather-check"
                        >
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </div>
                      How to Install CORK
                    </li>
                    <li class="list-unstyled">
                      <div class="icon-svg">
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
                            class="feather feather-check"
                        >
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </div>
                      Gulp not running
                    </li>
                    <li class="list-unstyled">
                      <div class="icon-svg">
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
                            class="feather feather-check"
                        >
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </div>
                      Browser Sync not working
                    </li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <ul class="">
                    <li class="list-unstyled">
                      <div class="icon-svg">
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
                            class="feather feather-check"
                        >
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </div>
                      File Structure
                    </li>
                    <li class="list-unstyled">
                      <div class="icon-svg">
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
                            class="feather feather-check"
                        >
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </div>
                      Demo Detail
                    </li>
                    <li class="list-unstyled">
                      <div class="icon-svg">
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
                            class="feather feather-check"
                        >
                          <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                      </div>
                      Build website and webapps
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="fq-tab-section">
          <div class="row">
            <div class="col-md-12 mb-5 mt-5">
              <h2>Some common questions</h2>

              <div class="accordion" id="faq">
                <div class="card">
                  <div class="card-header" role="tab">
                    <div
                        class="mb-0 collapsed"
                        role="menu"
                        data-bs-toggle="collapse"
                        data-bs-target="#fqcollapseOne"
                        aria-expanded="false"
                        aria-controls="fqcollapseOne"
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
                          class="feather feather-code"
                      >
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                      </svg>
                      <span class="faq-q-title">What are the templates?</span>
                      <div class="like-faq">
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
                            class="feather feather-thumbs-up"
                        >
                          <path
                              d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                          ></path>
                        </svg>
                        <span class="faq-like-count">65</span>
                      </div>
                    </div>
                  </div>
                  <div
                      id="fqcollapseOne"
                      class="collapse"
                      aria-labelledby="fqcollapseOne"
                      data-bs-parent="#faq"
                  >
                    <div class="card-body">
                      <p>
                        Templates are just a set of configs that you can save in
                        order to use later so you don´t have to start from
                        scratch every time you want to create a new campaign.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header" role="tab">
                    <div
                        class="mb-0 collapsed"
                        role="menu"
                        data-bs-toggle="collapse"
                        data-bs-target="#fqcollapseTwo"
                        aria-expanded="true"
                        aria-controls="fqcollapseTwo"
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
                          class="feather feather-code"
                      >
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                      </svg>
                      <span class="faq-q-title">
                        Where can I download a report?</span
                      >
                      <div class="like-faq">
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
                            class="feather feather-thumbs-up"
                        >
                          <path
                              d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                          ></path>
                        </svg>
                        <span class="faq-like-count">20</span>
                      </div>
                    </div>
                  </div>
                  <div
                      id="fqcollapseTwo"
                      class="collapse"
                      aria-labelledby="fqcollapseTwo"
                      data-bs-parent="#faq"
                  >
                    <div class="card-body">
                      <p>
                        You have to go under Taboola/PerformanceTools/Reports.
                        Once you are on that tab, you can create a new request
                        with the button on the top corner. Once the requests is
                        created, you have to wait 10 seconds and then you will
                        be able to download the report by clicking on the
                        Download File button.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header" role="tab">
                    <div
                        class="mb-0 collapsed"
                        role="menu"
                        data-bs-toggle="collapse"
                        data-bs-target="#fqcollapseThree"
                        aria-expanded="false"
                        aria-controls="fqcollapseThree"
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
                          class="feather feather-code"
                      >
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                      </svg>
                      <span class="faq-q-title">
                        Are all the fields on the campaign generator
                        required?</span
                      >
                      <div class="like-faq">
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
                            class="feather feather-thumbs-up"
                        >
                          <path
                              d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                          ></path>
                        </svg>
                        <span class="faq-like-count">33</span>
                      </div>
                    </div>
                  </div>
                  <div
                      id="fqcollapseThree"
                      class="collapse"
                      aria-labelledby="fqcollapseThree"
                      data-bs-parent="#faq"
                  >
                    <div class="card-body">
                      <p>
                        No, all the fields are not required. Nevertheless We
                        recommend that try you complete every field. When
                        submiting the system checks automatically for the
                        required fields and will point them out if needed.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header" role="tab">
                    <div
                        class="mb-0 collapsed"
                        role="menu"
                        data-bs-toggle="collapse"
                        data-bs-target="#fqcollapseFour"
                        aria-expanded="false"
                        aria-controls="fqcollapseFour"
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
                          class="feather feather-code"
                      >
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                      </svg>
                      <span class="faq-q-title"
                      >How do I know how many ads and campaigns will be
                        created?</span
                      >
                      <div class="like-faq">
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
                            class="feather feather-thumbs-up"
                        >
                          <path
                              d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                          ></path>
                        </svg>
                        <span class="faq-like-count">14</span>
                      </div>
                    </div>
                  </div>
                  <div
                      id="fqcollapseFour"
                      class="collapse"
                      aria-labelledby="fqcollapseFour"
                      data-bs-parent="#faq"
                  >
                    <div class="card-body">
                      <p>
                        The system will create the same amount of campaigns as
                        keywords. Regarding the amount of ads, the system will
                        create the result of making [number of headlines] *
                        [number of images] that contains each keyword.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header" role="tab">
                    <div
                        class="mb-0 collapsed"
                        role="menu"
                        data-bs-toggle="collapse"
                        data-bs-target="#fqcollapseFive"
                        aria-expanded="false"
                        aria-controls="fqcollapseFive"
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
                          class="feather feather-code"
                      >
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                      </svg>
                      <span class="faq-q-title">
                        What is the conversion event?</span
                      >
                      <div class="like-faq">
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
                            class="feather feather-thumbs-up"
                        >
                          <path
                              d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                          ></path>
                        </svg>
                        <span class="faq-like-count">09</span>
                      </div>
                    </div>
                  </div>
                  <div
                      id="fqcollapseFive"
                      class="collapse"
                      aria-labelledby="fqcollapseFive"
                      data-bs-parent="#faq"
                  >
                    <div class="card-body">
                      <p>
                        When an advertiser sets up a campaign on Taboola, they
                        define their conversion event, which represents the
                        desired action they want users to take after clicking on
                        their ads. For instance, if an e-commerce website wants
                        to measure how many users complete a purchase after
                        clicking on their Taboola ad, they would set the
                        conversion event as the "purchase" action.
                      </p>
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header" role="tab">
                    <div
                        class="mb-0 collapsed"
                        role="menu"
                        data-bs-toggle="collapse"
                        data-bs-target="#fqcollapseSix"
                        aria-expanded="false"
                        aria-controls="fqcollapseSix"
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
                          class="feather feather-code"
                      >
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                      </svg>
                      <span class="faq-q-title"
                      >Can I choose more than one account in the campaign
                        generator?</span
                      >
                      <div class="like-faq">
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
                            class="feather feather-thumbs-up"
                        >
                          <path
                              d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"
                          ></path>
                        </svg>
                        <span class="faq-like-count">58</span>
                      </div>
                    </div>
                  </div>
                  <div
                      id="fqcollapseSix"
                      class="collapse"
                      aria-labelledby="fqcollapseSix"
                      data-bs-parent="#faq"
                  >
                    <div class="card-body">
                      <p>
                        Yes! You just have to click the "Select Doiman" radio
                        button under the Adaccounts & Domains accordion. Once
                        you´ve choosen the account and the domain, you just have
                        to click the "Select Account & Domain" button. The
                        combination will be added to the table. Then you can add
                        or remove them from the table.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import "@/assets/sass/pages/faq/faq.scss";

import { useMeta } from "@/composables/use-meta";
useMeta({ title: "FAQ" });
</script>
