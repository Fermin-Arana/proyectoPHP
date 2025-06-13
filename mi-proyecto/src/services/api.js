// src/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000', // Ajusta esto si tu backend usa otro puerto
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor para respuestas (manejo centralizado de errores)
api.interceptors.response.use(
  (response) => response.data, // Devuelve solo los datos (sin el objeto "response" completo)
  (error) => {
    if (error.response) {
      // Error del servidor (ej. 400, 500)
      throw new Error(error.response.data.message || 'Error del servidor');
    } else {
      // Error de red (sin conexión, etc.)
      throw new Error('No se pudo conectar al servidor');
    }
  }
);

export default api;