import api from '../api.js'; 

export const login = async (usuario, password) => {
  try {
    const response = await api.post('/usuario/login', { 
      usuario, 
      password 
    });
    return response.data; // Solo devuelve el mensaje directo del backend
  } catch (error) {
    throw error; // El interceptor de axios ya maneja el error
  }
};