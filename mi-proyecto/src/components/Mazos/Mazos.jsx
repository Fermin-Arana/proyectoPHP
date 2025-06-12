import { useState, useEffect } from "react";
import { getMazos } from "../../services/apiMazos/apiMazo.js"; 
import AccionesMazo from "./AccionesMazos.jsx";

export default function MisMazos({ usuarioId, token }) {
  const [mazos, setMazos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const cargarMazos = async () => {
    try {
      setLoading(true);
      const { data, status } = await getMazos(token, usuarioId); 
      if (status === 200) {
        setMazos(data || []);
      } else {
        setError("Error al cargar mazos");
      }
    } catch (err) {
      setError("Error de conexión");
      console.error("Error:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    cargarMazos();
  }, [usuarioId, token]);

  if (loading) return <div>Cargando mazos...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="mis-mazos">
      <h2>Mis Mazos</h2>
      {mazos.length === 0 ? (
        <p>No tienes mazos creados.</p>
      ) : (
        <div className="mazos-grid">
          {mazos.map((mazo) => (
            <AccionesMazo
              key={mazo.id}
              mazo={mazo}
              token={token}
              onUpdate={cargarMazos} 
            />
          ))}
        </div>
      )}
    </div>
  );
}