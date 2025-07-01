import api from '../api'; // ajustá esta ruta si tu instancia axios vive en otro archivo

// Crear partida
export const crearPartida = async (token, mazo_id) => {
  try {
    const res = await api.post(
      '/partidas',
      { mazo_id },
      {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    );
    return res.data;
  } catch (error) {
    console.error('Error al crear partida:', error);
    throw error.response?.data || { message: 'Error al crear partida' };
  }
};

// Jugar carta
export const jugarCarta = async (token, partida_id, carta_id) => {
  try {
    const res = await api.post(
      '/jugadas',
      {
        partida_id,
        carta_id
      },
      {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }
    );
    return res.data;
  } catch (error) {
    console.error('Error al jugar carta:', error);
    throw error.response?.data || { message: 'Error al jugar carta' };
  }
};

export const reanudarPartida = async (token) => {
  try {
    const res = await api.get('/partida/reanudar', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    return res.data;
  } catch (error) {
    console.error('Error al reanudar partida:', error);
    throw error.response?.data || { message: 'Error al reanudar partida' };
  }
};