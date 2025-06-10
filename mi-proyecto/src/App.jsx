import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import NavBarComponent from './components/NavBarComponent';
import Home from './components/Home';
import Mazos from './components/Mazos';
import FooterComponent from './components/FooterComponent'
import TestConnection from './components/TestConnection';
import HeaderComponent from './components/HeaderComponent';

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