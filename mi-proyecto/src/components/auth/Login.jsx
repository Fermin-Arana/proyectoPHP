import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx'; 
import '../../assets/styles/Register.css'; 

const Login = () => {
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const { login } = useAuth();
  const handleSubmit = async (e) => {
  e.preventDefault();
  setError('');
  try {
    const result = await login(usuario, password);
    // Redirige solo si no hubo error
    if (result?.token) {
      navigate('/'); // Cambio a ruta raíz
    }
  } catch (err) {
    // Muestra el mensaje de error limpio
    setError(err.toString().replace('Error: ', ''));
  }
};
  return (
    <div className="login-container">
      <div className="login-card">
        <h2>Iniciar sesion</h2>
        {error && <p className="error-message">{error}</p>}
        
        <form onSubmit={handleSubmit} className="login-form">     
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
          
          <button type="submit" className="submit-button">Iniciar sesion</button>
        </form>
      </div>
    </div>
  );
};

export default Login;