import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx'; 
import '../../assets/styles/Register.css'; 

const Register = () => {
  const [nombre, setNombre] = useState('');
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [errores, setErrores] = useState([]);
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();
  const { register } = useAuth(); 

  const validar = () => {
    const erroresDetectados = [];

    if (!/^[a-zA-Z0-9]{6,20}$/.test(usuario)) {
      erroresDetectados.push("El usuario debe tener entre 6 y 20 caracteres alfanuméricos.");
    }

    if (!nombre.trim()) {
      erroresDetectados.push("El nombre no puede estar vacío.");
    } else if (nombre.length > 30) {
      erroresDetectados.push("El nombre no puede tener más de 30 caracteres.");
    }

    const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!regexPassword.test(password)) {
      erroresDetectados.push("La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas, números y símbolos.");
    }

    return erroresDetectados;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrores([]);
    setSuccess('');

    const erroresValidados = validar();
    if (erroresValidados.length > 0) {
      setErrores(erroresValidados);
      return;
    }

    try {
      await register(nombre, usuario, password);
      setSuccess("¡Registro exitoso! Serás redirigido al login...");
      setTimeout(() => {
        navigate('/login');
      }, 2500);
    } catch (err) {
      const mensajeDelBackend = err.response?.data?.message;
      setErrores([mensajeDelBackend || err.message || "Error al registrar el usuario."]);
    }
  };

  return (
    <div className="register-container">
      <div className="register-card">
        <h2 className="nombre-registro">Registro</h2>

        {errores.length > 0 && (
          <div className="error-message">
            <ul>
              {errores.map((error, idx) => (
                <li key={idx}>{error}</li>
              ))}
            </ul>
          </div>
        )}

        {success && (
          <div className="success-message">
            <p>{success}</p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="register-form">
          <input
            type="text"
            placeholder="Nombre completo"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            className="form-input"
          />

          <input
            type="text"
            placeholder="Nombre de usuario"
            value={usuario}
            onChange={(e) => setUsuario(e.target.value)}
            className="form-input"
          />

          <input
            type="password"
            placeholder="Contraseña"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="form-input"
          />

          <button type="submit" className="submit-button">Registrarse</button>
        </form>
      </div>
    </div>
  );
};

export default Register;