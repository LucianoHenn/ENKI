import { createStore } from 'vuex';
import plugins from './plugin';
import { state, getters, mutations, actions } from './global';

import auth from './modules/auth'

export default new createStore({
    state,
    getters,
    mutations,
    actions,
    plugins,
    modules: {
        auth
    }
});
