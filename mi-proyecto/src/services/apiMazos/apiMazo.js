// src/services/apiMazos/apiMazo.js
import api from '../api'; 

export const getMazos = async (token, usuarioId) => {
  try {
    const response = await api.get(`/usuarios/${usuarioId}/mazos`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    if (error.response) {
      throw {
        status: error.response.status,
        message: error.response.data?.message || 'Error del servidor'
      };
    } else {
      throw {
        status: 500,
        message: 'Error de conexión'
      };
    }
  }
};

export const createMazo = async (token, data) => {
  try {
    const response = await api.post('/mazos', data, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    if (error.response) {
      throw {
        status: error.response.status,
        message: error.response.data?.message || 'Error al crear mazo'
      };
    }
    throw error;
  }
};