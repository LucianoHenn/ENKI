import axios from "axios";

const token = localStorage.getItem('token');

let bearHeader = "";
if (token) {
  bearHeader = "Bearer " + token;
}

export default axios.create({
  baseURL: '/api',
  headers: {
    "Content-Type": "application/json",
    Authorization: bearHeader
  }
});
