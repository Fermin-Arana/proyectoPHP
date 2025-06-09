import "../assets/styles/HeaderComponent.css";
import logogo from "../assets/images/iconoPagina.svg";

const HeaderComponent = () => {
  return (
    <div className="header">
      <img src={logogo} alt="Logo" className="logo" />
      <span className="title">Milica erome</span>
    </div>
  );
};

export default HeaderComponent;