import api from '../api.js';

export const verifyToken = async (token) => {
  try {
    const response = await api.get('/usuario/token', {
      headers: {
        'Authorization': 'Bearer ${token}'
      }
    });
    return response.data;
  } catch (error) {
    throw error;
  }
};