
import { useEffect } from 'react';
import { getMazos } from '../../services/apiMazos/apiMazo.js';

export default function TestConnection() {
  useEffect(() => {
    const testBackend = async () => {
      try {
        const response = await getMazos('a', 2);
        console.log("Respuesta del backend:", response);
      } catch (error) {
        console.error("Error al conectar con el backend:", error);
      }
    };
    testBackend();
  }, []);

  return <h1>Verifica la consola del navegador</h1>;
}