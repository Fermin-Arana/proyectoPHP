import { useState } from 'react'
import logogo from './assets/images/iconoPagina.svg'
import HeaderComponent from './components/HeaderComponent'
import FooterComponent from './components/FooterComponent'
import NavBarComponent from './components/NavBarComponent'
import './App.css';


function App() {
  const [count, setCount] = useState(0)

  return (
    <div className ='header-container'>
      <HeaderComponent />
      <NavBarComponent />
      {/* Acá va el resto de tu app */}
      <FooterComponent />
    </div>
  )
}

export default App
