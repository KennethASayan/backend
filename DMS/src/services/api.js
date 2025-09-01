import axios from "axios";

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000',
  withCredentials: true, // Important for CSRF
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

export default apiClient;
