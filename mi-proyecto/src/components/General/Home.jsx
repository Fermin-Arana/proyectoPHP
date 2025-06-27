import { useState, useEffect } from 'react';
import { getEstadisticas } from '../../services/apiEstadisticas/apiEstadisticas.js';
import '../../assets/styles/HomeEstadisticas.css';

export default function Home() {
  const [estadisticas, setEstadisticas] = useState(null);
  const [orden, setOrden] = useState('desc');
  const [pagina, setPagina] = useState(1);
  const usuariosPorPagina = 5;

  useEffect(() => {
    const fetchEstadisticas = async () => {
      try {
        const data = await getEstadisticas();
        setEstadisticas(data);
      } catch (error) {
        console.error('Error fetching estadísticas:', error);
      }
    };
    fetchEstadisticas();
  }, []);

  if (!estadisticas) {
    return (
      <div className="home-flex-container">
        <div className="home-bienvenida">
          <h1>¡Bienvenido a la App!</h1>
          <p>Esta es la página principal.</p>
        </div>
        <div>
          <h2>Estadísticas generales</h2>
          <p>Cargando estadísticas...</p>
        </div>
      </div>
    );
  }

  const usuarios = Object.entries(estadisticas.message).map(([userId, partidas]) => {
    const ganadas = partidas.filter(p => p.el_usuario === "gano").length;
    const total = partidas.length;
    const promedio = total > 0 ? ((ganadas / total) * 100) : 0;
    return { userId, partidas, ganadas, total, promedio };
  });

  usuarios.sort((a, b) => orden === 'desc' ? b.promedio - a.promedio : a.promedio - b.promedio);

  const totalPaginas = Math.ceil(usuarios.length / usuariosPorPagina);
  const usuariosPagina = usuarios.slice((pagina - 1) * usuariosPorPagina, pagina * usuariosPorPagina);

  return (
    <div className="home-flex-container">
      <div style={{ flex: 2 }}>
        <div className="estadisticas-header">
          <h2>Estadísticas generales</h2>
          <div className="paginado-estadisticas">
            <button
              onClick={() => setPagina(pagina - 1)}
              disabled={pagina === 1}
            >
              Anterior
            </button>
            <span>Página {pagina} de {totalPaginas}</span>
            <button
              onClick={() => setPagina(pagina + 1)}
              disabled={pagina === totalPaginas}
            >
              Siguiente
            </button>
          </div>
        </div>

        <div style={{ marginBottom: 16 }}>
          <button
            className={orden === 'desc' ? 'orden-activo' : ''}
            onClick={() => setOrden('desc')}
          >
            Mejor promedio primero
          </button>
          <button
            className={orden === 'asc' ? 'orden-activo' : ''}
            onClick={() => setOrden('asc')}
            style={{ marginLeft: 8 }}
          >
            Peor promedio primero
          </button>
        </div>

        <div className="estadisticas-lista">
          {usuariosPagina.map((usuario, idx) => (
            <div
              key={usuario.userId}
              className={`estadisticas-usuario${idx === 0 && pagina === 1 ? ' destacado' : ''}`}
            >
              <strong>Usuario ID: {usuario.userId}</strong>
              <div>
                <span>
                  Partidas ganadas: {usuario.ganadas} / {usuario.total}
                  {usuario.total > 0 && (
                    <> ({usuario.promedio.toFixed(2)}% de victorias)</>
                  )}
                </span>
              </div>
              <ul>
                {usuario.partidas.length === 0 && <li>No hay partidas</li>}
                {usuario.partidas.map((partida, idx2) => (
                  <li key={idx2}>
                    Estado: {partida.estado} | El usuario: {partida.el_usuario}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>
      <div className="home-bienvenida" style={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center' }}>
        <h1>¡Bienvenido a la App!</h1>
        <p>Esta es la página principal.</p>
      </div>
    </div>
  );
}

