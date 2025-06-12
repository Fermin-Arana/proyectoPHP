import { useState } from "react";
import { useAuth } from "../context/AuthContext.jsx";

const Login = () => {
  const [usuario, setUsuario] = useState("");
  const [password, setPassword] = useState("");
  const { Login } = useAuth();

  const handleSubmit = async (e) => {
    e.preventDefault();
    const result = await Login(usuario, password);
    if (result.error) {
      alert(result.error);
    } else {
      alert("¡Bienvenido!");
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        placeholder="Usuario"
        value={usuario}
        onChange={(e) => setUsuario(e.target.value)}
      />
      <input
        type="password"
        placeholder="Contraseña"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
      />
      <button type="submit">Iniciar sesión</button>
    </form>
  );
};

export default Login;