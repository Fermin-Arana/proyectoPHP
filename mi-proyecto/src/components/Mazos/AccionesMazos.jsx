import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { getCartas, getCartasMazo, editarMazo, borrarMazo } from '../../services/apiMazos/apiMazo.js';
import { useParams, Link, useNavigate } from 'react-router-dom';
import '../../assets/styles/AccionesMazos.css';

const AccionesMazos = () => {
  const { id } = useParams(); // id del mazo desde la URL
  const { token } = useAuth();
  const [cartasMazo, setCartasMazo] = useState([]);
  const [todasCartas, setTodasCartas] = useState([]);
  const [nombreMazo, setNombreMazo] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const [mostrarConfirmacion, setMostrarConfirmacion] = useState(false);

  // Cargar datos del mazo y cartas
  useEffect(() => {
    const fetchData = async () => {  
      setLoading(true);
      try {
        // Obtener cartas del mazo
        const cartasMazoRes = await getCartasMazo(token, Number(id));
        setCartasMazo(cartasMazoRes.data || []);

        // Obtener todas las cartas (para mostrar nombres)
        const todasRes = await getCartas();
        setTodasCartas(todasRes.cartas || todasRes || []);
      } catch (err) {
        setError('Error al cargar datos');
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [id, token]);

  // Guardar solo el nombre del mazo
  const guardarCambios = async () => {
    setError('');
    try {
      await editarMazo(token, nombreMazo, id);
      navigate('/mazos'); // Redirige a la lista de mazos
    } catch (err) {
      setError('Error al guardar cambios');
    }
  };

  // Borrar mazo con confirmación visual
  const handleBorrarMazo = async () => {
    setError('');
    try {
      await borrarMazo(token, id);
      setMostrarConfirmacion(false);
      navigate('/mazos');
    } catch (err) {
      setError('Error al borrar el mazo');
      setMostrarConfirmacion(false);
    }
  };

  return (
    <div className="acciones-mazos-container">
      <div className="acciones-mazos-card">
        <h2>Editar Mazo</h2>
        {loading && <p>Cargando...</p>}
        {error && <p style={{ color: 'red' }}>{error}</p>}

        <label>
          Nombre del mazo:
          <input
            type="text"
            value={nombreMazo}
            onChange={(e) => setNombreMazo(e.target.value)}
          />
        </label>
        <div style={{ display: 'flex', gap: '10px', justifyContent: 'center', marginBottom: '18px' }}>
          <button onClick={guardarCambios} disabled={!nombreMazo.trim()}>
            Guardar nombre
          </button>
          <button
            onClick={() => setMostrarConfirmacion(true)}
            style={{ background: '#e53935' }}
          >
            Borrar mazo
          </button>
        </div>

        {mostrarConfirmacion && (
          <div className="modal-confirmacion">
            <div className="modal-contenido">
              <p>¿Seguro que quieres borrar este mazo? Esta acción no se puede deshacer.</p>
              <button
                onClick={handleBorrarMazo}
                style={{ background: '#e53935', marginRight: '10px' }}
              >
                Sí, borrar
              </button>
              <button onClick={() => setMostrarConfirmacion(false)}>
                Cancelar
              </button>
            </div>
          </div>
        )}

        <h3>Cartas en el mazo:</h3>
        <ul>
          {cartasMazo.map((carta) => {
            
            
            const cartaId = Number(carta.carta_id ?? carta.id);
            const cartaObj = todasCartas.find((c) => Number(c.id) === cartaId);
            return (
              <li key={cartaId}>
                {cartaObj ? cartaObj.nombre : `Carta ${cartaId}`}
              </li>
            );
          })}
        </ul>

        <br />
        <Link to="/mazos">Volver a mis mazos</Link>
      </div>
    </div>
  );
};

export default AccionesMazos;