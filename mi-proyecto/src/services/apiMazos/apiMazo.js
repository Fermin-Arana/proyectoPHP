
import api from '../api.js'

export const login = async (usuario, password) => {
  const response = await api.post('/usuario/login', { usuario, password });
  return response.data; 
};

export const getMazos = async (usuarioId) => {
  try {
    const response = await api.get(`/usuarios/${usuarioId}/mazos`);
    return response.data;
  } catch (error) {
    console.error('Error fetching mazos:', error.response?.data || error.message);
    throw error; 
  }
};
export const createMazo = async (nombre, cartas) => {
  try {
    const response = await api.post('/mazos', {
      nombre,
      cartas
    });
    return response.data;
  } catch (error) {
    console.error('Error creating mazo:', error.response?.data || error.message);
    throw new Error(error.response?.data?.message || 'Error al crear mazo');
  }
};