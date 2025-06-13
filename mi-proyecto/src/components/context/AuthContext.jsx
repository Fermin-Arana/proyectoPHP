import { createContext, useState, useContext } from "react";
import { register as registerService } from '../../services/apiAuth/apiRegister.js';
import { login as loginService } from '../../services/apiAuth/apiLogin.js';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem("token") || null);

const login = async (usuario, password) => {
  try {
    const result = await loginService(usuario, password);
    
    if (typeof result !== 'string' || !result) {
      throw new Error(result || 'Credenciales incorrectas');
    }

    // Guarda el token (asumiendo que el backend lo devuelve como string)
    localStorage.setItem('token', result);
    setToken(result);
    setUser({ 
      usuario, // Usamos el mismo nombre de usuario enviado
      nombre: usuario // Como fallback, ya que el backend no envía el nombre
    });

    return { token: result, usuario };
    
  } catch (error) {
    console.error('Error en login:', error);
    throw new Error(error.message || 'Error al iniciar sesión');
  }
};

const register = async (nombre, usuario, password) => {
  try {
    const token = await registerService(nombre, usuario, password);
    
    // Asume que el backend devuelve el token directamente en el mensaje
    localStorage.setItem('token', token);
    setToken(token);
    setUser({ nombre, usuario }); // Guarda los datos básicos
    
    return token; // Opcional: devuelve el token si lo necesitas
  } catch (error) {
    throw error; // Simplemente relanza el error
  }
};

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