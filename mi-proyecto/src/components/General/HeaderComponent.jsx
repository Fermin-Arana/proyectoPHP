import '../../assets/styles/HeaderComponent.css';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Link } from 'react-router-dom';
import iconoPagina from '../../assets/images/iconoPagina.svg';

const HeaderComponent = () => {
  const { isAuthenticated, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/login', { replace: true });
  };

  return (
    <div className="header">
      <Link to="/" className="logo-container">
        <img src={iconoPagina} alt="Logo" className="logo" />
        <span className="title">Pokepage</span>
      </Link>

      {!isAuthenticated ? (
        <div className="header__buttons">
          <button onClick={() => navigate('/register')}>Registro de usuario</button>
          <button onClick={() => navigate('/login')}>Login</button>
        </div>
      ) : (
        <div className="header__user-buttons">
          <button onClick={() => navigate('/mazos')}>Mis mazos</button>
          <button onClick={() => navigate('/editar-usuario')}>Editar usuario</button>
          <button onClick={handleLogout}>Logout</button>
        </div>
      )}
    </div>
  );
};

export default HeaderComponent;