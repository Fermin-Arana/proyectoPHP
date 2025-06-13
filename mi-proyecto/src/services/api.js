// src/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000', // Ajusta esto si tu backend usa otro puerto
  headers: {
    'Content-Type': 'application/json',
  },
});

export default api;