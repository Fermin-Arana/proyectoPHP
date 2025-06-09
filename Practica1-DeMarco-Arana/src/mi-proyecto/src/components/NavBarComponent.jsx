import React from 'react';
import "../assets/styles/NavBarComponent.css"; 


const NavBarComponent = () => {
  return (
    <nav className="navbar">
      <a href="Mazos">Mazos</a>
      <a href="Registrarse">Registrarse</a>
      <a href="Iniciar sesion">Iniciar sesión</a>
      <a href="Cerrar sesion">Cerrar sesión</a>
      <a href="Jugar partida">Jugar partida</a>
    </nav>
  );
};

export default NavBarComponent;