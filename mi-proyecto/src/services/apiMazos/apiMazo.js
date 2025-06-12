
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
