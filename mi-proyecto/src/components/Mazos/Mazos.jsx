import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { getMazos, createMazo } from '../../services/apiMazos/apiMazo.js';

const Mazo = () => {
  const [nombreMazo, setNombreMazo] = useState('');
  const [mazos, setMazos] = useState([]);
  const [error, setError] = useState('');
  const { user, token } = useAuth(); // Asegúrate de que useAuth devuelva { user, token }

useEffect(() => {
  const loadMazos = async () => {
    try {
      if (!token || !user?.id) {
        console.log("Esperando token o user.id...");
        return; 
      }
      
      const mazosData = await getMazos(token, user.id);
      setMazos(mazosData);
    } catch (err) {
      setError("Error al cargar mazos: " + err.message);
      console.error("Error en loadMazos:", err);
    }
  };
  loadMazos();
}, [token, user?.id]); // Dependencia específica en user.id

  // 2. Crear un nuevo mazo
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    try {
      if (!token || !user?.id) {
        throw new Error("No hay token o usuario ID");
      }
      
      await createMazo(token, { 
        nombre: nombreMazo, 
        usuario_id: user.id 
      });

      const updatedMazos = await getMazos(token, user.id);
      setMazos(updatedMazos);
      setNombreMazo('');
    } catch (err) {
      setError("Error al crear mazo: " + err.message);
      console.error("Error en handleSubmit:", err); // Debug
    }
  };

  return (
    <div className="mazo-container">
      <div className="mazo-card">
        <h2 className="tituloss">Mis Mazos</h2>
        {error && <p className="error-message">{error}</p>}

        {/* Lista de mazos existentes */}
        {mazos.length > 0 ? (
          <ul className="mazo-list">
            {mazos.map((mazo) => (
              <li key={mazo.id} className="mazo-item">
                {mazo.nombre}
              </li>
            ))}
          </ul>
        ) : (
          <p>No hay mazos creados aún.</p>
        )}

        {/* Formulario para crear nuevo mazo */}
        <form onSubmit={handleSubmit} className="mazo-form">
          <input
            type="text"
            placeholder="Nombre del mazo"
            value={nombreMazo}
            onChange={(e) => setNombreMazo(e.target.value)}
            required
            className="form-input"
          />
          <button type="submit" className="submit-button">Crear Mazo</button>
        </form>
      </div>
    </div>
  );
};

export default Mazo;