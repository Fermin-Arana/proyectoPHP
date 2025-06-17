// src/services/apiMazos/apiMazo.js
import api from '../api'; // Importa tu instancia de Axios

export const getMazos = async (token, usuarioId) => {
  console.log("HOLA");
  try {
    console.log("URL:", `/usuarios/${usuarioId}/mazos`);
  console.log("Token:", token);
  console.log("Usuario ID:", usuarioId);
    const response = await api.get(`/usuarios/${usuarioId}/mazos`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    console.log("ta bien")
    return {
      status: response.status,
      data: response.data?.message || [] 
    };
  } catch (error) {
    console.log("ta mal");
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