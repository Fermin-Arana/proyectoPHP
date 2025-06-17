import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { getMazos, createMazo } from '../../services/apiMazos/apiMazo.js';

const Mazo = () => {
  const [nombreMazo, setNombreMazo] = useState('');
  const [mazos, setMazos] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { user, token } = useAuth();

  useEffect(() => {
    const loadMazos = async () => {
      try {
        setLoading(true);
        setError('');
        
        if (!token || !user?.id) {
          console.log("Esperando token o user.id...");
          return;
        }
        
        const response = await getMazos(token, user.id);
        
        // Adaptación para la estructura de tu backend
        if (response.status === 200) {
          setMazos(response.data || []);
        } else {
          setError(response.message || "Error al cargar mazos");
        }
      } catch (err) {
        console.error("Error completo:", err);
        
        if (err.status === 401) {
          setError("Tu sesión ha expirado. Por favor inicia sesión nuevamente.");
          // Aquí podrías redirigir al login o limpiar el contexto
        } else if (err.status === 403) {
          setError("No tienes permisos para ver estos mazos");
        } else {
          setError(err.message || "Error de conexión con el servidor");
        }
      } finally {
        setLoading(false);
      }
    };
    
    loadMazos();
  }, [token, user?.id]);

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

      // Recargar la lista después de crear
      const updatedResponse = await getMazos(token, user.id);
      if (updatedResponse.status === 200) {
        setMazos(updatedResponse.data || []);
        setNombreMazo('');
      }
    } catch (err) {
      console.error("Error al crear mazo:", err);
      setError(err.message || "Error al crear el mazo");
    }
  };

  return (
    <div className="mazo-container">
      <div className="mazo-card">
        <h2 className="tituloss">Mis Mazos</h2>
        
        {loading && <p>Cargando mazos...</p>}
        {error && <p className="error-message">{error}</p>}

        {!loading && mazos.length > 0 ? (
          <ul className="mazo-list">
            {mazos.map((mazo) => (
              <li key={mazo.id} className="mazo-item">
                {mazo.nombre}
              </li>
            ))}
          </ul>
        ) : (
          !loading && <p>No hay mazos creados aún.</p>
        )}

        <form onSubmit={handleSubmit} className="mazo-form">
          <input
            type="text"
            placeholder="Nombre del mazo"
            value={nombreMazo}
            onChange={(e) => setNombreMazo(e.target.value)}
            required
            className="form-input"
          />
          <button type="submit" className="submit-button">
            Crear Mazo
          </button>
        </form>
      </div>
    </div>
  );
};

export default Mazo;