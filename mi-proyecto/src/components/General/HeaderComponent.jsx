import '../../assets/styles/HeaderComponent.css'
import logogo from "../../assets/images/iconoPagina.svg";
import { Link } from "react-router-dom"; 

const HeaderComponent = () => {
  return (
    <div className="header">
      <Link to="/">
        <img src={logogo} alt="Logo" className="logo" />
      </Link>
      <span className="title">Pokepage</span>
    </div>
  );
};

export default HeaderComponent;