import { useState } from 'react';
import { editarUsuario } from '../../services/apiAuth/apiEditarUsuario';
import { useAuth } from '../context/AuthContext';
import '../../assets/styles/EditUser.css'; 

const EditUser = () => {
  const { user } = useAuth();
  const [nombre, setNombre] = useState(user?.nombre || '');
  const [password, setPassword] = useState('');
  const [repeatPassword, setRepeatPassword] = useState('');
  const [errores, setErrores] = useState([]);
  const [mensaje, setMensaje] = useState('');

  const validar = () => {
    const erroresDetectados = [];

    if (!nombre.trim()) {
      erroresDetectados.push("El nombre no puede estar vacío.");
    } else if (nombre.length > 30) {
      erroresDetectados.push("El nombre no puede tener más de 30 caracteres.");
    }

    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!passwordRegex.test(password)) {
      erroresDetectados.push("La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.");
    }

    if (password !== repeatPassword) {
      erroresDetectados.push("Las contraseñas no coinciden.");
    }

    return erroresDetectados;
  };

  const handleSubmit = async (e) => {
    e.preventDefault(); //Previene que el formulario recargue la página. Asi se mantiene el react sin refrescos.
    const erroresValidados = validar();

    if (erroresValidados.length > 0) {
      setErrores(erroresValidados);
      setMensaje('');
      return;
    }

    try {
      const resultado = await editarUsuario(user.id, nombre, password);
      setMensaje(resultado.message);
      setErrores([]);
    } catch (error) {
      setErrores(["Ocurrió un error al actualizar el usuario."]);
      setMensaje('');
    }
  };

  return (
    <div className="editar-usuario">
      <h2>Editar Usuario</h2>

      <form onSubmit={handleSubmit}>
        <div>
          <label>Nombre de usuario:</label>
          <input
            type="text"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
          />
        </div>

        <div>
          <label>Contraseña:</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
          />
        </div>

        <div>
          <label>Repetir contraseña:</label>
          <input
            type="password"
            value={repeatPassword}
            onChange={(e) => setRepeatPassword(e.target.value)}
          />
        </div>

        <button type="submit">Guardar cambios</button>
      </form>

      {/* Mostrar errores */}
      {errores.length > 0 && (
        <div className="errores">
          <ul>
            {errores.map((error, idx) => (
              <li key={idx}>{error}</li>
            ))}
          </ul>
        </div>
      )}

      {/* Mensaje de éxito */}
      {mensaje && <p className="mensaje-exito">{mensaje}</p>}
    </div>
  );
};

export default EditUser;