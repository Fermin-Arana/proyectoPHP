import { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import '../../assets/styles/NavBarComponent.css';

const NavBarComponent = () => {
  const { isAuthenticated, user } = useAuth();
  const location = useLocation();
  const [mostrarSaludo, setMostrarSaludo] = useState(false);

  useEffect(() => {
    if (isAuthenticated) {
      setMostrarSaludo(true);
    }
  }, [isAuthenticated]);

  useEffect(() => {
    if (location.pathname !== '/home') {
      setMostrarSaludo(false);
    }
  }, [location]);

  return (
    <>
      {isAuthenticated && mostrarSaludo && (
        <section className="saludo-area">
          <span className="navbar__greeting">Hola {user?.usuario}!!!</span>
        </section>
      )}
    </>
  );
};

export default NavBarComponent;