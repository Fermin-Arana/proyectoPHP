import { createContext, useState, useContext, useEffect } from "react";
import { register as registerService } from '../../services/apiAuth/apiRegister.js';
import { login as loginService } from '../../services/apiAuth/apiLogin.js';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

 useEffect(() => {
    const verifyStoredToken = async () => {
      const storedToken = localStorage.getItem("token");

      if (storedToken) {
        try {
          // ✅ Verificar el token con el backend
          const response = await verifyToken(storedToken);

          if (response.valid) {
            setToken(storedToken);
            setUser({
              id: response.user.id,
              usuario: response.user.usuario,
              nombre: response.user.nombre
            });
          } else {
            // Token inválido, limpiar
            localStorage.removeItem('token');
          }
        } catch (error) {
          console.error('Token inválido:', error);
          localStorage.removeItem('token');
        }
      }

      setLoading(false);
    };

    verifyStoredToken();
  }, []);
const login = async (usuario, password) => {
  try {
    const response = await loginService(usuario, password);
    const { token, id, usuario: nombre } = response.message;


    
    const user = {
      id,
      nombre
    };

    setToken(token);
    setUser(user);

    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    return response;

  } catch (err) {
    console.error('Error en login:', err);
    throw err;
  }
};


  const register = async (nombre, usuario, password) => {
    try {
      const response = await registerService(nombre, usuario, password);

      if (response.status !== 200) {
        throw new Error(response.message || 'Registro fallido');
      }

      return { success: true, message: response.message };
    } catch (error) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      setToken(null);
      setUser(null);
      throw error;
    }
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setToken(null);
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{
      user,
      token,
      loading,
      login,
      register,
      logout,
      isAuthenticated: !!token
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe usarse dentro de un AuthProvider');
  }
  return context;
};
