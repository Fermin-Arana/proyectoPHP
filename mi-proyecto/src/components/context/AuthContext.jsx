import { createContext, useState, useContext } from "react";
import { register as registerService } from '../../services/apiAuth/apiRegister.js';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem("token") || null);

  // Función para login (guarda token en localStorage)
  const login = async (usuario, password) => {
    try {
      const response = await fetch("http://localhost/proyectoPHP/usuario/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ usuario, password }),
      });
      const data = await response.json();
      if (response.ok) {
        localStorage.setItem("token", data.token); // Asume que el backend devuelve un token
        setToken(data.token);
        setUser({ usuario }); // Guarda datos básicos del usuario
      }
      return data;
    } catch (error) {
      console.error("Error en login:", error);
      return { error: "Error al conectar con el servidor" };
    }
  };

// Dentro de tu AuthProvider
const register = async (nombre, usuario, password) => {
  const result = await registerService(nombre, usuario, password);
  if (result.token) {
    localStorage.setItem('token', result.token);
    setUser({ usuario }); // Actualiza el estado global
  }
  return result;
};

// Luego lo provees en el value del Provider

  // Función para logout
  const logout = () => {
    localStorage.removeItem("token");
    setToken(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, token, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);