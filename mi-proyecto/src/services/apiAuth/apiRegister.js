import api from '../api.js';

export const register = async (nombre, usuario, password) => {
  try {
    const response = await api.post('/usuario/registro', { 
      nombre, 
      usuario, 
      password 
    });
    
    // Verifica si la respuesta es exitosa (status 200)
    if (response.data.status !== 200) {
      throw new Error(response.data.message || 'Registro fallido');
    }
    
    // Devuelve un objeto con la estructura esperada por tu contexto
    return {
      message: {
        token: null, // Tu backend actual no devuelve token
        id: null,    // Tu backend actual no devuelve ID
        usuario: usuario,
        nombre: nombre
      },
      status: response.data.status
    };
    
  } catch (error) {
    throw new Error(error.response?.data?.message || error.message);
  }
};