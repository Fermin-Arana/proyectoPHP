import api from '../api.js'

export const getCartas = async () => {
  const response = await api.get('/cartasdisponibles');
  return response.data; 
};