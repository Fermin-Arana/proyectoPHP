import api from '../api.js';

export const editarUsuario = async (id, nombre, password) => {
  try {
    const token = localStorage.getItem('token'); // Token automático

    const response = await api.put(`/usuarios/${id}`, {
      nombre,
      password,
    }, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    return response.data;
  } catch (error) {
    throw error;
  }
};