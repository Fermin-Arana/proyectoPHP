import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';
import { crearPartida, jugarCarta } from '../../services/apiPartida/apiJugarPartida.js';
import '../../assets/styles/jugarPartida.css';

const JugarPartida = () => {
  const { id: mazoId } = useParams();
  const { token, loading } = useAuth();
  const navigate = useNavigate();

  const [partidaId, setPartidaId] = useState(null);
  const [cartasUsuario, setCartasUsuario] = useState([]);
  const [cartasServidor, setCartasServidor] = useState([]);
  const [cartasJugadas, setCartasJugadas] = useState([]);
  const [resultadoRonda, setResultadoRonda] = useState(null);
  const [partidaFinalizada, setPartidaFinalizada] = useState(null);
  const [error, setError] = useState('');

    useEffect(() => {
    if (!loading && token && !partidaId) {
        const iniciarPartida = async () => {
        try {
            const res = await crearPartida(token, mazoId);
            setPartidaId(res.id);
            setCartasUsuario(res.MAZO.map((c) => ({ ...c, estado: 'en_mano' })));
            setCartasServidor(new Array(5).fill(null));
        } catch (err) {
            if (err.message?.includes("partida en curso")) {
            setError("Ya tenés una partida activa. Recargá o continuá desde donde la dejaste.");
            } else {
            setError(err.message || "Error al iniciar la partida");
            }
        }
        };

        iniciarPartida();
    }
    }, [token, loading, mazoId, partidaId]);

  const handleJugada = async (carta) => {
    if (!partidaId || partidaFinalizada) return;

    try {
      const res = await jugarCarta(token, partidaId, carta.carta_id);
      console.log("Token enviado:", token);
      setCartasJugadas((prev) => [
        ...prev,
        {
          jugador: carta,
          servidor: res.carta_servidor,
          resultado: res.message
        }
      ]);
      setCartasUsuario((prev) => prev.filter((c) => c.carta_id !== carta.carta_id));
      setResultadoRonda(res.message);
      if (res.partida_finalizada) {
        setPartidaFinalizada(res.partida_finalizada);
      }
    } catch (err) {
      setError(err.message || 'No se pudo jugar la carta');
    }
  };

    const reiniciar = async () => {
    // Limpio todo para que el useEffect vuelva a ejecutar correctamente
    setPartidaId(null);
    setCartasUsuario([]);
    setCartasServidor([]);
    setCartasJugadas([]);
    setResultadoRonda(null);
    setPartidaFinalizada(null);
    setError('');
  };

  return (
    <div className="tablero-container">
      <h2>Partida en curso</h2>
      {error && <p className="error">{error}</p>}

      {/* Zona superior - Cartas ocultas del servidor */}
      <div className="zona-servidor">
        <h3>Cartas del Servidor</h3>
        <div className="cartas-servidor">
          {cartasServidor.map((_, idx) => (
            <div key={idx} className="carta-servidor">
              <span>Atributo</span>
            </div>
          ))}
        </div>
      </div>

      {/* Zona central - Resultado de la jugada actual */}
      <div className="zona-centro">
        {cartasJugadas.length > 0 && (
          <>
            <div>
              <h4>Tu carta</h4>
              <p>{cartasJugadas.at(-1).jugador.nombre}</p>
              <p>Ataque: {cartasJugadas.at(-1).jugador.ataque}</p>
            </div>
            <div className="bloque-servidor">
              <h4>Carta del servidor</h4>
              <p>{cartasJugadas.at(-1).servidor.nombre}</p>
              <p>Ataque: {cartasJugadas.at(-1).servidor.ataque}</p>
          </div>
            <p className="resultado-ronda">
              Resultado:{" "}
              {cartasJugadas.at(-1).resultado === "ganaste"
                ? "¡Ganaste esta ronda!"
                : cartasJugadas.at(-1).resultado === "empate"
                ? "Empate"
                : "Perdiste esta ronda"}
            </p>
          </>
        )}
      </div>

      {/* Zona inferior - Cartas del jugador */}
      <div className="zona-jugador">
        <h3>Tu mano</h3>
        <div className="cartas-jugador">
          {cartasUsuario.map((carta) => {
          return (
            <div
              key={carta.carta_id}
              className="carta"
              onDoubleClick={() => handleJugada(carta)}
            >
              <strong>{carta.nombre}</strong>
              <p>Ataque: {carta.ataque}</p>
              <p>Atributo: {carta.atributo_id}</p>
            </div>
          );
        })}
        </div>
      </div>

      {partidaFinalizada && (
        <div className="zona-final">
          <h3>{partidaFinalizada}</h3>
          <button onClick={reiniciar} className="submit-button">
            Jugar otra vez
          </button>
        </div>
      )}
    </div>
  );
};

export default JugarPartida;
