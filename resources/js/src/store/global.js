import i18n from "@/i18n";

export const state = {
    layout: 'app',
    is_show_sidebar: true,
    is_show_search: false,
    is_dark_mode: false,
    dark_mode: 'light',
    locale: null,
    menu_style: 'vertical',
    layout_style: 'full',
    countryList: [
        { code: 'en', name: 'English' },
    ],
    isLoading: false,
}

export const getters = {
    layout: state => state.layout,
    is_show_sidebar: state => state.is_show_sidebar,
    is_show_search: state => state.is_show_search,
    is_dark_mode: state => state.is_dark_mode,
    dark_mode: state => state.dark_mode,
    locale: state => state.locale,
    menu_style: state => state.menu_style,
    layout_style: state => state.layout_style,
    countryList: state => state.countryList,
    isLoading: state => state.isLoading,
}

export const mutations = {
    setLayout(state, payload) {
        state.layout = payload;
    },
    toggleSideBar(state, value) {
        state.is_show_sidebar = value;
    },
    toggleSearch(state, value) {
        state.is_show_search = value;
    },
    toggleLocale(state, value) {
        value = value || 'en';
        i18n.global.locale = value;
        localStorage.setItem('i18n_locale', value);
        state.locale = value;
    },
    toggleDarkMode(state, value) {
        //light|dark|system
        value = value || 'light';
        localStorage.setItem('dark_mode', value);
        state.dark_mode = value;
        if (value == 'light') {
            state.is_dark_mode = false;
        } else if (value == 'dark') {
            state.is_dark_mode = true;
        } else if (value == 'system') {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                state.is_dark_mode = true;
            } else {
                state.is_dark_mode = false;
            }
        }

        if (state.is_dark_mode) {
            document.querySelector('body').classList.add('dark');
        } else {
            document.querySelector('body').classList.remove('dark');
        }
    },
    toggleMenuStyle(state, value) {
        //vertical|horizontal
        value = value || 'vertical';
        localStorage.setItem('menu_style', value);
        state.menu_style = value;
    },
    toggleLayoutStyle(state, value) {
        //full|boxed
        value = value || 'full';
        localStorage.setItem('layout_style', value);
        state.layout_style = value;
    },
    setIsLoading(state, value) {
        state.isLoading = value;
    }
}

export const actions = {
    setLayout({ commit }, payload) {
        commit('setLayout', payload);
    },
    toggleSideBar({ commit }, payload) {
        commit('toggleSideBar', payload);
    },
    toggleSearch({ commit }, payload) {
        commit('toggleSearch', payload);
    },
    toggleLocale({ commit }, payload) {
        commit('toggleLocale', payload);
    },
    toggleDarkMode({ commit }, payload) {
        commit('toggleDarkMode', payload);
    },
    toggleMenuStyle({ commit }, payload) {
        commit('toggleMenuStyle', payload);
    },
    toggleLayoutStyle({ commit }, payload) {
        commit('toggleLayoutStyle', payload);
    },
    getErrors(key) {
        return this.error[key];
    },
    setIsLoading({ commit }, payload) {
        commit('setIsLoading', payload);
    }
}

export default {
    state,
    getters,
    mutations,
    actions
};
