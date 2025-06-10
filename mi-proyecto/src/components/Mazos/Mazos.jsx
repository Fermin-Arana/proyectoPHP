import { useState, useEffect } from "react";
import AccionesMazos from "./AccionesMazos";

export default function MisMazos({ usuarioId, token }) {
  const [mazos, setMazos] = useState([]);
  const [loading, setLoading] = useState(true);

  const cargarMazos = async () => {
    try {
      const response = await fetch(`http://tu-backend.com/usuarios/${usuarioId}/mazos`, {
        headers: { "Authorization": `Bearer ${token}` }
      });
      const { data } = await response.json();
      setMazos(data || []);
    } catch (error) {
      console.error("Error al cargar mazos:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { cargarMazos(); }, [usuarioId, token]);

  if (loading) return <div>Cargando mazos...</div>;

  return (
    <div>
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