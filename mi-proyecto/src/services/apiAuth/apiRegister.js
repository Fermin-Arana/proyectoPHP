const BASE_URL = 'http://localhost:8000/usuario/register'; // Asegúrate de que esta ruta coincida con tu backend

export const register = async (nombre, usuario, password) => {
  try {
    const response = await fetch(BASE_URL, {
      method: 'POST', // Método POST para registro
      headers: {
        'Content-Type': 'application/json',
        // No necesitas Authorization para registro, ya que el usuario aún no está autenticado
      },
      body: JSON.stringify({
        nombre,
        usuario, 
        password
      })
    });

    if (!response.ok) {
      const errorText = await response.text(); // 👈 te muestra el mensaje real
      console.error('respuesta del servidor',errorText);
      throw new Error('Error en el registro');
    }

    const data = await response.json();
    return data;
    
  } catch (error) {
    console.error('Error en register:', error);
    throw error; // Puedes manejar este error en tu componente
  }
};