import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx'; // ✔️

const Register = () => {
  const [nombre, setNombre] = useState('');
  const [usuario, setUsuario] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const { register } = useAuth(); 

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const result = await register(nombre, usuario, password);
      
      if (result.token) { 
        localStorage.setItem('token', result.token);
        navigate('/'); 
      } else if (result.message) {
        setError(result.message); 
      }
    } catch (err) {
      setError('Error al registrar. Intenta nuevamente.');
      console.error(err);
    }
  };

  return (
    <div className="register-form">
      <h2>Registro</h2>
      {error && <p className="error">{error}</p>}
      
      <form onSubmit={handleSubmit}>
        <input
          type="text"
          placeholder="Nombre completo"
          value={nombre}
          onChange={(e) => setNombre(e.target.value)}
          required
        />
        
        <input
          type="text"
          placeholder="Nombre de usuario"
          value={usuario}
          onChange={(e) => setUsuario(e.target.value)}
          required
        />
        
        <input
          type="password"
          placeholder="Contraseña"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        
        <button type="submit">Registrarse</button>
      </form>
    </div>
  );
};

export default Register;