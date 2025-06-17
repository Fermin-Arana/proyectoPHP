// NavBarComponent.jsx
import { Link } from 'react-router-dom'; 
import '../../assets/styles/NavBarComponent.css';

const NavBarComponent = () => {
  return (
    <nav className="navbar">
      <Link to="/mazos">Mis Mazos</Link> 
      <Link to="/jugar-partida">Jugar partida</Link>
      <Link to="/login">Iniciar sesión</Link>
      <Link to="/register">Registrarse</Link>
      <button onClick={cerrarSesion}>Cerrar sesión</button>
    </nav>
  );
};

const cerrarSesion = () => {
  localStorage.removeItem('token'); 
  window.location.href = '/';
};

export default NavBarComponent;