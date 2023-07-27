export const ls = require("store");
export const STORAGE_KEY = "enki";

const localStoragePlugin = store => {
  store.subscribe((mutation, state) => {
    const syncedData = {
      auth: state.auth
    };

    ls.set(STORAGE_KEY, syncedData);
    if (mutation.type === "CLEAR_ALL_DATA") {
      ls.delete(STORAGE_KEY);
    }
  })
};

export default [localStoragePlugin];
