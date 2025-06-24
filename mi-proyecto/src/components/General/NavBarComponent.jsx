import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import '../../assets/styles/NavBarComponent.css';
import iconoPagina from '../../assets/images/iconoPagina.svg';

const NavBarComponent = () => {
  const { loading, isAuthenticated, user, logout } = useAuth();
  const navigate = useNavigate();

  if (loading) return null;

  const handleLogout = () => {
    logout();
    navigate('/login', { replace: true });
  };

  return (
    <>
      {/* Header blanco con logo y botones de login/registro */}
      <header className="header">
      <div className="logo-container">
        <img src={iconoPagina} alt="Pokepage" className="logo" />
        <span className="logo-text">Pokepage</span>
      </div>

      {isAuthenticated ? (
        <div className="header__user">
          <button onClick={() => navigate('/mazos')}>Mis mazos</button>
          <button onClick={() => navigate('/editar-usuario')}>Editar usuario</button>
          <button onClick={handleLogout}>Logout</button>
        </div>
      ) : (
        <div className="header__buttons">
          <button onClick={() => navigate('/register')}>Registro de usuario</button>
          <button onClick={() => navigate('/login')}>Login</button>
        </div>
      )}
    </header>

    {isAuthenticated && (
    <section className="saludo-area">
      <span className="navbar__greeting">Hola {user?.usuario}!!</span>
    </section>
  )}

    </>
  );
};

export default NavBarComponent;
