// src/services/apiMazos/apiMazo.js
import api from '../api'; 

export const getMazos = async (token, usuarioId) => {
  try {
    const response = await api.get(`/usuarios/${usuarioId}/mazos`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
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

export const createMazo = async (token, data) => {
  try {
    const response = await api.post('/mazos', data, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    if (error.response) {
      throw {
        status: error.response.status,
        message: error.response.data?.message || 'Error al crear mazo'
      };
    }
    throw error;
  }
};

export const getCartasMazo = async (token, mazoId) => {
  try {
    const response = await api.get(`/mazos/${mazoId}/cartas`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    if (error.response) {
      throw {
        status: error.response.status,
        message: error.response.data?.message || 'Error al obtener cartas del mazo'
      };
    }
    throw error;
  }
}

export const editarMazo = async (token,nombre, id_mazo) => {
  try {
    const response = await api.put(`/mazos/${id_mazo}`, { nombre }, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    if (error.response) {
      throw {
        status: error.response.status,
        message: error.response.data?.message || 'Error al editar mazo'
      };
    }
    throw error;
  }
};

export const borrarMazo = async (token, mazoId) => {
  try {
    const response = await api.delete(`/mazos/${mazoId}`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.data;
  } catch (error) {
    throw error;
  }
};


