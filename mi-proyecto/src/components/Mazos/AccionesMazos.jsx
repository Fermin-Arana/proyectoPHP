import { useState } from "react";
import EditarMazo from "./EditarMazo";

export default function AccionesMazo({ mazo, token, onUpdate }) {
  const [showEditModal, setShowEditModal] = useState(false);

  const borrarMazo = async () => {
    if (!window.confirm(`¿Borrar el mazo "${mazo.nombre}"?`)) return;
    
    try {
      const response = await fetch(`http://tu-backend.com/mazos/${mazo.id}`, {
        method: "DELETE",
        headers: { "Authorization": `Bearer ${token}` }
      });
      if (response.ok) onUpdate();
    } catch (error) {
      console.error("Error al borrar:", error);
    }
  };

  return (
    <div className="acciones-mazo" style={{ border: "1px solid #ccc", padding: "10px", margin: "10px" }}>
      <h3>{mazo.nombre}</h3>
      <p><strong>Cartas:</strong> {mazo.cartas.length}/5</p>
      
      <div className="cartas-list">
        {mazo.cartas.map((carta) => (
          <div key={carta.id} style={{ margin: "5px 0" }}>
            <span>{carta.nombre} (Ataque: {carta.ataque}, {carta.atributo})</span>
          </div>
        ))}
      </div>

      <button onClick={() => setShowEditModal(true)}>Editar Nombre</button>
      <button onClick={borrarMazo} style={{ backgroundColor: "#ff4444", color: "white" }}>Borrar</button>

      {showEditModal && (
        <EditarMazoModal
          mazo={mazo}
          token={token}
          onClose={() => setShowEditModal(false)}
          onUpdate={onUpdate}
        />
      )}
    </div>
  );
}