import { useState } from "react";

export default function EditarMazo({ mazo, token, onClose, onUpdate }) {
  const [nuevoNombre, setNuevoNombre] = useState(mazo.nombre);

  const guardarCambios = async () => {
    try {
      const response = await fetch(`http://tu-backend.com/mazos/${mazo.id}`, {
        method: "PUT",
        headers: {
          "Authorization": `Bearer ${token}`,
          "Content-Type": "application/json"
        },
        body: JSON.stringify({ nombre: nuevoNombre })
      });
      const data = await response.json();
      if (response.ok) {
        onUpdate(); // Recargar la lista
        onClose();
      } else {
        alert(data.message || "Error al editar el mazo");
      }
    } catch (error) {
      console.error("Error:", error);
    }
  };

  return (
    <div className="modal">
      <div className="modal-content">
        <h3>Editar Mazo</h3>
        <input
          type="text"
          value={nuevoNombre}
          onChange={(e) => setNuevoNombre(e.target.value)}
        />
        <button onClick={guardarCambios}>Guardar</button>
        <button onClick={onClose}>Cancelar</button>
      </div>
    </div>
  );
}