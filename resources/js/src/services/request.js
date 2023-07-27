import axios from "@/axios"

const request = function (method, path, params, config = {}) {
  return new Promise(function (resolve, reject) {
    try {
      if (method === "get") {
        axios
          .get(path, { params, ...config })
          .then(response => {
            resolve(response.data)
          })
          .catch(e => {
            reject(e)
          })
      } else if (method === "post") {
        axios
          .post(path, params, config)
          .then(response => {
            resolve(response.data)
          })
          .catch(e => {
            reject(e)
          })
      } else if (method === "put") {
        axios
          .put(path, params, config)
          .then(response => {
            resolve(response.data)
          })
          .catch(e => {
            reject(e)
          })
      } else if (method === "delete") {
        axios
          .delete(path, params, config)
          .then(response => {
            resolve(response.data)
          })
          .catch(e => {
            reject(e)
          })
      } else if (method === "patch") {
        axios
          .patch(path, params, config)
          .then(response => {
            resolve(response.data)
          })
          .catch(e => {
            reject(e)
          })
      } else {
        console.log("Exception: unexpected request method - ", method)
      }
    } catch (e) {
      console.log(
        "api failed (method: " + method + ", path: " + path + ", params: ",
params,
        ")",
        e
      )
      reject(e)
    }
  })
}

export default request
