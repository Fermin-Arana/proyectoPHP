import { useState } from 'react'
import logogo from './assets/Images/iconoPagina.svg'
import HeaderComponent from './components/HeaderComponent'
import FooterComponent from './components/footercomponent'
import './App.css'
import NavBarComponent from './components/navbarcomponent'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div>
      <div className = "top-bar">
        <NavBarComponent />
        <HeaderComponent />
      </div>
      {/* Acá va el resto de tu app */}
      <FooterComponent />
    </div>
  )
}

export default App
