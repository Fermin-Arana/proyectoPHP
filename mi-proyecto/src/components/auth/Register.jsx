import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx'; 
import '../../assets/styles/Register.css'; 

const Register = () => {
  const [nombre, setNombre] = useState('');
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const { register } = useAuth(); 

const handleSubmit = async (e) => {
  e.preventDefault();
  setError('');

  try {
    await register(nombre, usuario, password);
    navigate('/login'); 
  } catch (err) {
    setError(err.message); 
  }
};

  return (
    <div className="register-container">
      <div className="register-card">
        <h2 className = "nombre-registro">Registro</h2>
        {error && <p className="error-message">{error}</p>}
        
        <form onSubmit={handleSubmit} className="register-form">
          <input
            type="text"
            placeholder="Nombre completo"
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            required
            className="form-input"
          />
          
          <input
            type="text"
            placeholder="Nombre de usuario"
            value={usuario}
            onChange={(e) => setUsuario(e.target.value)}
            required
            className="form-input"
          />
          
          <input
            type="password"
            placeholder="Contraseña"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            className="form-input"
          />
          
          <button type="submit" className="submit-button">Registrarse</button>
        </form>
      </div>
    </div>
  );
};

export default Register;