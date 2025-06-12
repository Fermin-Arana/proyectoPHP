import { useAuth } from "../context/AuthContext.jsx";

const Logout = () => {
  const { Logout, user } = useAuth();

  if (!user) return null;

  return (
    <div>
      <p>Hola, {user.usuario}!</p>
      <button onClick={Logout}>Cerrar sesión</button>
    </div>
  );
};

export default Logout;