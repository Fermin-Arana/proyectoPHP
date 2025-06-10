import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import NavBarComponent from './components/General/NavBarComponent';
import Home from './components/General/Home';
import Mazos from './components/Mazos/Mazos';
import FooterComponent from './components/General/FooterComponent'
import TestConnection from './components/Test/testConnection';
import HeaderComponent from './components/General/HeaderComponent';

export default function App() {
  return (
    <Router>
      <HeaderComponent />
      <NavBarComponent />
      <Routes>
        <Route path="/" element={<Home />} /> 
        <Route path="/mazos" element={<Mazos />} />
        <Route path="/test-backend" element={<TestConnection />} />
      </Routes>
      <FooterComponent />
    </Router>
  );
}