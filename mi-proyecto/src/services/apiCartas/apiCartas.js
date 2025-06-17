import api from '../api.js'

export const getCartas = async () => {
  const response = await api.get('/cartas');
  return response.data; 
};