<template>
    <!--  BEGIN SIDEBAR  -->
    <div class="sidebar-wrapper sidebar-theme">
        <nav ref="menu" id="sidebar">
            <div class="shadow-bottom"></div>
            <perfect-scrollbar
                class="list-unstyled menu-categories"
                tag="ul"
                :options="{ wheelSpeed: 0.5, swipeEasing: !0, minScrollbarLength: 40, maxScrollbarLength: 300, suppressScrollX: true }"
              >
                <template v-for="(menu, index) in menuItems">
                  <li v-if="showMenu(menu)" class="menu" :key="index">
                      <a
                          v-if="(typeof(menu.children) !== 'undefined')"
                          class="dropdown-toggle"
                          data-bs-toggle="collapse"
                          :data-bs-target="`#${menu.name}`"
                          :aria-controls="menu.name"
                          :aria-expanded="menu.expand"
                      >
                          <div class="">
                              <i :data-feather="menu.icon"/>
                              <span>{{ menu.title }}</span>
                          </div>
                          <div><i data-feather="chevron-down"/></div>
                      </a>

                      <ul
                          :id="menu.name"
                          class="collapse submenu list-unstyled"
                          data-bs-parent="#sidebar"
                          v-if="(typeof(menu.children) !== 'undefined')"
                      >
                          <li v-for="(submenu, subIndex) in menu.children" :key="subIndex">
                            <a
                              v-if="(typeof(submenu.children) !== 'undefined')"
                              class="dropdown-toggle"
                              data-bs-toggle="collapse"
                              :data-bs-target="`#${submenu.name}`"
                              :aria-controls="submenu.name"
                              :aria-expanded="submenu.expand"
                          >
                              <div class="">
                                  <span>{{ submenu.title }}</span>
                              </div>
                              <div><i data-feather="chevron-down"/></div>
                            </a>

                            <ul
                                :id="submenu.name"
                                class="collapse submenu subofsub list-unstyled"
                                :data-bs-parent="`#${submenu.name}`"
                                v-if="(typeof(submenu.children) !== 'undefined')"
                            >
                                <li v-for="(subOfMenu, subIndex) in submenu.children" :key="subIndex">
                                    <router-link :to="subOfMenu.to" @click="toggleMobileMenu">{{ subOfMenu.title }}</router-link>
                                </li>
                            </ul>

                            <router-link v-else :to="submenu.to" @click="toggleMobileMenu">{{ submenu.title }}</router-link>
                          </li>
                      </ul>

                      <router-link
                          v-else
                          :to="menu.to"
                          class="dropdown-toggle"
                          @click="toggleMobileMenu"
                      >
                          <div class="">
                              <i :data-feather="menu.icon" />
                              <span>{{ menu.title }}</span>
                          </div>
                      </router-link>
                  </li>
                </template>
            </perfect-scrollbar>
        </nav>
    </div>
    <!--  END SIDEBAR  -->
</template>

<style scope lang="scss">
  #sidebar ul.menu-categories ul.submenu > li a {
    margin-left: 8px;
  }

  #sidebar ul.menu-categories li.menu > .dropdown-toggle svg {
    margin-right: 10px;
  }

  #sidebar ul.menu-categories li.menu > .dropdown-toggle {
    padding: 10px 5px;
  }

  ul.subofsub{
    margin-left: 22px;
  }

  #sidebar a.dropdown-toggle {
    cursor: pointer;
  }
</style>

<script setup>
    import { onMounted, ref } from 'vue';
    import { useStore } from 'vuex';
    import feather from 'feather-icons';
    import menuItems from '../../navs';

    const store = useStore();

    const menu_collapse = ref('dashboard');

    onMounted(() => {
        const selector = document.querySelector('#sidebar a[href="' + window.location.pathname + '"]');
        if (selector) {
            const ul = selector.closest('ul.collapse');
            if (ul) {
                let ele = ul.closest('li.menu').querySelectorAll('.dropdown-toggle');
                if (ele) {
                    ele = ele[0];
                    setTimeout(() => {
                        ele.click();
                    });
                }
            } else {
                selector.click();
            }
        }
        feather.replace();
    });

    const showMenu = (menu) => {
      const user = store?.state?.auth;
      if (menu?.roles?.length) {
        if (user?.roles?.length) {
          let result = false;
          user.roles.forEach((role) => {
            if (menu.roles.includes(role)) {
              result = true;
            }
          });
          return result;
        } else {
          return false;
        }
      }
      return true;
    }

    const toggleMobileMenu = () => {
      if (window.innerWidth < 991) {
          store.commit('toggleSideBar', !store.state.is_show_sidebar);
      }
    };
</script>
