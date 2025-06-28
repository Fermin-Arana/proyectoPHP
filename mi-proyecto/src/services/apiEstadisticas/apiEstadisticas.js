
import api from '../api'; 

export const getEstadisticas = async () => {
  try {
    const response = await api.get(`/estadisticas`);
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