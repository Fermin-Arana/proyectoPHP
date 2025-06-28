import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { getCartas, getMazos, createMazo } from '../../services/apiMazos/apiMazo.js';
import { Link } from 'react-router-dom';

const Mazo = () => {
  const [nombreMazo, setNombreMazo] = useState('');
  const [mazos, setMazos] = useState([]);
  const [cartas, setCartas] = useState([]);
  const [seleccionadas, setSeleccionadas] = useState(() => {
    const saved = localStorage.getItem('seleccionadas');
    return saved ? JSON.parse(saved) : [];
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [mostrarCartas, setMostrarCartas] = useState(false);
  const [filtroNombre, setFiltroNombre] = useState('');
  const [filtroAtributo, setFiltroAtributo] = useState('');

  const { user, token } = useAuth();

  // Cargar mazos
  useEffect(() => {
    const loadMazos = async () => {
      try {
        setLoading(true);
        setError('');
        if (!token || !user?.id) return;

        const response = await getMazos(token, user.id);

        if (response.status === 200) {
          setMazos(response.data || []);
        } else {
          setError(response.message || "Error al cargar mazos");
        }
      } catch (err) {
        console.error(err);
        setError(err.message || "Error al cargar mazos");
      } finally {
        setLoading(false);
      }
    };
    loadMazos();
  }, [token, user?.id]);

  // Cargar cartas cuando se muestran
  useEffect(() => {
    if (!mostrarCartas) return;

    const loadCartas = async () => {
      try {
        const response = await getCartas();
          console.log("Cartas recibidas:", response.cartas);
          setCartas(response.cartas || []);
        } catch (err) {
          console.error(err);
          setError("Error al cargar cartas");
        }
      };

      loadCartas();
    }, [mostrarCartas]);

  // Función para seleccionar/deseleccionar carta
  const toggleSeleccion = (idCarta) => {
    if (typeof idCarta !== 'number') {
      alert('Error: carta sin ID válido');
      return;
    }
    setSeleccionadas((prev) => {
      let nuevaSeleccion;
      if (prev.includes(idCarta)) {
        nuevaSeleccion = prev.filter(id => id !== idCarta);
      } else if (prev.length < 5) {
        nuevaSeleccion = [...prev, idCarta];
      } else {
        alert("Solo puedes seleccionar hasta 5 cartas");
        return prev;
      }
      localStorage.setItem('seleccionadas', JSON.stringify(nuevaSeleccion));
      return nuevaSeleccion;
    });
  };

  // Crear mazo con cartas seleccionadas
  const handleConfirmarMazo = async () => {
    if (seleccionadas.length !== 5) {
      setError("Debes seleccionar exactamente 5 cartas");
      return;
    }
    setError('');

    try {
      if (!token || !user?.id) throw new Error("No hay token o usuario ID");

      await createMazo(token, {
        nombre: nombreMazo,
        usuario_id: user.id,
        cartas: seleccionadas
      });

      // Recargar mazos
      const updatedResponse = await getMazos(token, user.id);
      if (updatedResponse.status === 200) {
        setMazos(updatedResponse.data || []);
        setNombreMazo('');
        setSeleccionadas([]);
        localStorage.removeItem('seleccionadas');
        setMostrarCartas(false);
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

        {/* Mostrar mazos creados */}
        {!loading && mazos.length > 0 ? (
          <ul className="mazo-list">
            {mazos.map((mazo) => (
          <li key={mazo.id} className="mazo-item">
            <Link
              to={`/mazos/${mazo.id}`}
              className="mazo-link"
            >
              {mazo.nombre}
            </Link>
          </li>
            ))}
          </ul>
        ) : (
          !loading && <p>No hay mazos creados aún.</p>
        )}

        {/* Input y botón Crear mazo */}
        <input
          type="text"
          placeholder="Nombre del mazo"
          value={nombreMazo}
          onChange={(e) => {
            if (e.target.value.length <= 20) {
              setNombreMazo(e.target.value);
            }
          }}
          className="form-input"
          disabled={mostrarCartas}
        />
        <p className="contador-caracteres">
          {nombreMazo.length}/20 caracteres
        </p>

        {mazos.length >= 3 && (
          <p className="error-message">
             Ya alcanzaste el máximo de 3 mazos permitidos.
          </p>
        )}
        <button
          onClick={() => {
            if (!nombreMazo.trim()) {
              setError("Debes ingresar un nombre para el mazo");
              return;
            }
            setError('');
            setMostrarCartas(true);
          }}
          disabled={mostrarCartas || mazos.length >= 3}
          className="submit-button"
        >
          Crear mazo
        </button>


        {/* Mostrar cartas para selección solo si mostrarCartas=true */}
        {mostrarCartas && (
          <>
            <div className="filtros-cartas">
              <input
                type="text"
                placeholder="Buscar por nombre"
                value={filtroNombre}
                onChange={(e) => setFiltroNombre(e.target.value)}
                className="form-input"
              />

              <select
                value={filtroAtributo}
                onChange={(e) => setFiltroAtributo(e.target.value)}
                className="form-input"
              >
                <option value="">Todos los atributos</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
              </select>

              <button onClick={async () => {
                try {
                  const res = await getCartas(filtroNombre, filtroAtributo);
                  setCartas(res.cartas || []);
                } catch {
                  setError("Error al aplicar filtros");
                }
              }} className="submit-button">
                Buscar
              </button>

              <button onClick={async () => {
                setFiltroNombre('');
                setFiltroAtributo('');
                const res = await getCartas();
                setCartas(res.cartas || []);
              }} className="submit-button">
                Limpiar
              </button>
            </div>
            <div className="cartas-lista" style={{ maxHeight: '300px', overflowY: 'auto', marginTop: '1rem' }}>
              {cartas.map((carta) => {
                const cartaId = carta.id;
                if (typeof cartaId !== 'number') {
                  console.warn('Carta sin ID:', carta);
                  return null;
                }
                const estaSeleccionada = seleccionadas.includes(cartaId);

                return (
                  <div key={cartaId} className="carta-item">
                    <div style={{ display: 'flex', flexDirection: 'column' }}>
                      <strong>{carta.nombre}</strong>
                      <span>Ataque: {carta.ataque}</span>
                      <span>Atributo: {carta.atributo_id}</span>
                    </div>
                    <input
                      type="checkbox"
                      checked={estaSeleccionada}
                      onChange={() => toggleSeleccion(cartaId)}
                    />
                  </div>
                );
              })}
            </div>

            <button
              onClick={handleConfirmarMazo}
              className="submit-button"
              style={{ marginTop: '1rem', marginBottom: '3rem' }} // Dejo espacio para el footer
            >
              Confirmar mazo
            </button>
          </>
        )}
      </div>
    </div>
  );
};

export default Mazo;
