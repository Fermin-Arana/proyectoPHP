import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import NavBarComponent from './components/General/NavBarComponent';
import Home from './components/General/Home';
import Mazos from './components/Mazos/Mazos';
import FooterComponent from './components/General/FooterComponent';
import TestConnection from './components/Test/testConnection';
import HeaderComponent from './components/General/HeaderComponent';
import { AuthProvider } from "./components/context/AuthContext.jsx";
import Register from "./components/auth/Register.jsx";
import Login from "./components/auth/Login.jsx";
import Logout from "./components/auth/Logout.jsx";

export default function App() {
  return (
    <Router>
      <AuthProvider>
        <HeaderComponent />
        <NavBarComponent />
        <Routes>
          <Route path="/" element={<Home />} /> 
          <Route path="/mazos" element={<Mazos />} />
          <Route path="/test-backend" element={<TestConnection />} />
          <Route path="/register" element={<Register />} />
          <Route path="/login" element={<Login />} />
          <Route path="/logout" element={<Logout />} />
        </Routes>
        <FooterComponent />
      </AuthProvider>
    </Router>
  );
}