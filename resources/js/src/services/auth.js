import axios from "@/axios"
import authApi from "@/services/api/auth"
import store from "@/store"

export const ifAuthenticated = (to, from, next) => {
  const token = localStorage.getItem('token')
  if (token) {
    handleGetMe()
    next()
    return
  }
  next({ name: "login" })
}

export const ifNotAuthenticated = (to, from, next) => {
  const token = localStorage.getItem('token')
  if (!token) {
    next()
    return
  }
  next({ name: "dashboard" })
}

export const ifAdminAuthenticated = (to, from, next) => {
  if (store?.state?.auth?.roles?.includes('admin')) {
    handleGetMe()
    next()
    return
  }
  next({ name: "404" })
}

export const handleLogin = (token, user) => {
  if (token.jwt) {
    localStorage.setItem('token', token.jwt)
    axios.defaults.headers["Authorization"] = "Bearer " + token.jwt
  }
  if (user) {
    // @todo store user info into store
  }
}

export const handleLogout = () => {
  localStorage.removeItem('token')
  delete axios.defaults.headers["Authorization"]
  window.location.href = '/login'
}

export const handleGetMe = () => {
  authApi.getMe()
    .then(res => {
      if (res.data) {
        store.dispatch("auth/updateUserInfo", res.data)
      }
    })
    .catch(() => {
      handleLogout()
    })
}
