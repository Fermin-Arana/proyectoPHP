import api from '../api.js'

export const login = async (usuario, password) => {
  const response = await api.post('/usuario/login', { usuario, password });
  return response.data; 
};