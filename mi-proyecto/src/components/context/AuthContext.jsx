import { createContext, useState, useContext } from "react";
import { register as registerService } from '../../services/apiAuth/apiRegister.js';
import { login as loginService } from '../../services/apiAuth/apiLogin.js';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem("token") || null);

const login = async (usuario, password) => {
  try {
    const response = await loginService(usuario, password);

    if (response && response.token) {
      localStorage.setItem('token', response.token);
      setToken(response.token);
      setUser({ usuario }); 
      return response; 
    }

    throw new Error(response?.message || 'Credenciales incorrectas');
    
  } catch (error) {
    const errorMessage = error.response?.data?.message || 
                       error.message || 
                       'Error al iniciar sesión';
    console.error('Error de login:', error); 
    throw new Error(errorMessage); 
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