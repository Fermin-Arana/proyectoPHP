// src/services/api.js
const BASE_URL = 'http://localhost/proyectoPHP';

export const getMazos = async (token, usuarioId) => {
  const response = await fetch(`${BASE_URL}/usuarios/${usuarioId}/mazos`, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return await response.json();
};

export const createMazo = async (token, data) => {
  // data = { nombre: "Mazo1", cartas: [1, 2, 3, 4, 5] }
  const response = await fetch(`${BASE_URL}/mazos`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(data)
  });
  return await response.json();
};

// Similar para deleteMazo y updateMazo...