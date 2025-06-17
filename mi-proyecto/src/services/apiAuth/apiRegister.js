import api from '../api.js';

export const register = async (nombre, usuario, password) => {
  try {
    const response = await api.post('/usuario/registro', { 
      nombre, 
      usuario, 
      password 
    });
    return response.data; 
  } catch (error) {
    throw error; 
  }
};